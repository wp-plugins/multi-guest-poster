<?php
if( !class_exists('abstract_article') ) {
	require_once( mpco_plugin_dir() . '/abstract_article.php' );
}

require_once(dirname(__FILE__) . '/phpQuery-onefile.php');

class ezine extends abstract_article {
	
	public function get_article_text( &$article ) {
		$html = $this->url_get_contents( $article['url'] );
		if(preg_match('/Ezine Publishers Get 25 Free Article Reprints/i', $html)) {
			sleep(5);
			return false;
		}

		libxml_use_internal_errors(true);
		$pq = phpQuery::newDocumentHTML($html);

		$content = pq('div#article-content')->html();
		$title   = pq('title')->text();
		
		$this->fix_urls($content, 'http://www.articlesbase.com');
		$this->url_add_nofolow($content);
		
		$article['content'] = strip_tags( $content, '<br><p><div><b><u><i><strong><body>' );
		$article['title']   = $title;
		
		return true;
		
//		$contentAnchorOpen	= "<!-- google_ad_section_start -->";
//		$contentAnchorClose	= "<!-- google_ad_section_end -->";
//		$titleAnchorOpen 	= "<title>";
//		$titleAnchorClose	= "<\/title>";
//		
//		$html = $this->url_get_contents( $article['url'] );
//		
//		if(preg_match('/Ezine Publishers Get 25 Free Article Reprints/i', $html)) {
//			sleep(5);
//			return false;
//		}
//		
//		$pattern = "/" . $contentAnchorOpen . "(.*?)"
//					   . $contentAnchorClose . "/ims";
//		preg_match( $pattern, $html, $matches );
//		$content = $matches[1];
//		
//		$pattern = "/" . $titleAnchorOpen . "(.*?)"
//					   . $titleAnchorClose . "/ims";
//		preg_match( $pattern, $html, $matches );
//		$title = $matches[1];
//		
//		$article['title']   = strip_tags( $title );
//		
//		$this->fix_urls($content, 'http://ezinearticles.com');
//		$this->url_add_nofolow($content);
//		
//		$article['content'] = strip_tags( $content, '<a><br><p><div><b><u><i><strong><body>' );
//		
//		return true;
	}
	
	public function get_article_list() {		
		
		$keyword = $this->get_keyword();
		
		$list = array();
		
		$page = 1;
		
		$cnt = 0;
		while (1) {
			$searchUrl		= "http://www.google.com/search?as_q=%s&hl=ru&newwindow=1&num=10&btnG=%%D0%%9F%%D0%%BE%%D0%%B8%%D1%%81%%D0%%BA+%%D0%%B2+Google&as_epq=&as_oq=&as_eq=&lr=&cr=&as_ft=i&as_filetype=&as_qdr=all&as_occt=any&as_dt=i&as_sitesearch=ezinearticles.com&as_rights=&safe=images";
			
			if($page > 1) $searchUrl .= '&start=' . $page * 10;
			
			$linkRx			= "<h3 class=\"r\"><a href=\"http:\/\/ezinearticles\.com\/(.*?)\"";
			$linkRxSwitches	= "si";
			
			$url  = sprintf( $searchUrl, urlencode( $keyword ) );
			$html = $this->url_get_contents( $url );
			
			preg_match_all( "/{$linkRx}/{$linkRxSwitches}", $html, $matches );
			
			if(count($matches[1]) == 0) break;
	
		    foreach ( $matches[1] as $match ) {
		    	if( preg_match( "/\?cat=/i", $match ) ) {
		    		continue;
		    	}
				$list[] = array(
					'url'   => 'http://ezinearticles.com/' . str_replace( '&amp;', '&', $match )
				);
				$cnt++;
				if($cnt > $this->limit) break;
			}
		    
			if($cnt > $this->limit) break;
			$page++;
		}

		return $list;
		
	}
	
}

?>