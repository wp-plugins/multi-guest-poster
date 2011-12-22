<?php
if( !class_exists('abstract_article') ) {
	require_once( mpco_plugin_dir() . '/abstract_article.php' );
}
	
class isnare extends abstract_article {
	
	public function get_article_text( &$article ) {
		$url  = 'http://www.isnare.com/html.php?aid=' . $article['id'];
		$html = $this->url_get_contents( $url, $article['url'] );
		
		preg_match( "/HTML Version\<\/b\>\<\/span\>\<br\>\<br\>\s+\<textarea cols=\"75\" rows=\"20\">(.*)\<\/textarea\>/Usi", $html, $matches );
		
		$content = $matches[1];
		$content = preg_replace("/^<h1>(.*)<\/h1>/Usi", '', $content);
		
		$this->fix_urls($content, 'http://www.isnare.com');
		$this->url_add_nofolow($content);
		
		$article['content'] = $content;
		
		return true;
		
	}
	
	public function get_article_list() {
		
		$keyword = $this->get_keyword();
		
		$list = array();
		
		$page = 1;
		
		$cnt = 0;
		while (1) {
			$url = "http://www.isnare.com/search.php?q=" . urlencode( $keyword ) . "&t=a&g=t";
			if($page > 1) {
				$ref  = $url . '&i=2&p=' . $page - 1;
				$url .= '&i=2&p=' . $page;
			} else {
				$url .= '&submit=Search';
				$ref  = "http://www.isnare.com/";
			}
			
			$html = $this->url_get_contents( $url, $ref );
			
			//$html = file_get_contents('111');
						
			preg_match_all( "/<tr><td class='text' align='left'>(.*)<\/td><\/tr>/Usi", $html, $matches );
			
			if(count($matches[1]) == 0) break;

			foreach ( $matches[1] as $i => $match ) {
				preg_match( "/<a href=\"(.*)\" class='s'>(.*)<\/a>/Usi", $match, $matches2 );
				
				preg_match( "/aid=(\d+)\&/", $matches2[1], $matches3 );
				$list[] = array(
					'url'   => 'http://www.isnare.com'.$matches2[1],
					'id'    => $matches3[1],
					'title' => $matches2[2]
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