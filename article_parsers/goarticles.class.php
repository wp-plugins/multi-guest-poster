<?php
if( !class_exists('abstract_article') ) {
	require_once( mpco_plugin_dir() . '/abstract_article.php' );
}

require_once 'phpQuery-onefile.php';

class goarticles extends abstract_article {
	
	public function get_article_text( &$article ) {
		
		$html = $this->url_get_contents( 'http://goarticles.com' . $article['url'] );

		libxml_use_internal_errors(true);
		$pq = phpQuery::newDocumentHTML($html);

		$content = pq('div.KonaBody')->html();
		
		$this->fix_urls($content, 'http://goarticles.com/');
		$this->url_add_nofolow($content);
		
		$article['content'] = strip_tags( $content, '<br><p><div><b><u><i><strong><body>' );
		
		return true;
		
	}
	
	public function get_article_list() {
		$keyword = $this->get_keyword();
		
		$list = array();
		
		$page = 0;
		
		$cnt = 0;
		while (1) {
			
			$searchUrl		= "http://goarticles.com/search/?type=title&q=%s&x=0&y=0";
			if($page > 0) $searchUrl .= '&limit=10&start=' . $page;
			
			$url  = sprintf( $searchUrl, urlencode($keyword) );
			$html = $this->url_get_contents( $url );
			//echo $html;
			
			libxml_use_internal_errors(true);
			$pq = phpQuery::newDocumentHTML($html);
			
			$flag = false;
			foreach(pq('a.article_title_link') as $link) {
				$flag = true;
				$url   = pq($link)->attr('href');
				$title = pq($link)->text();
				
				$list[] = array(
					'url'   => $url,
					'title' => $title,
				);
				$cnt++;
				if($cnt > $this->limit) break;
			}
			if(!$flag) {
				break;
			}
			
			if($cnt > $this->limit) break;
			$page += 10;
		}
		
		return $list;
		
	}
	
}

?>