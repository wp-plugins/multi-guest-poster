<?php
if( !class_exists('abstract_article') ) {
	require_once( mpco_plugin_dir() . '/abstract_article.php' );
}

require_once 'phpQuery-onefile.php';

class articlebase extends abstract_article {
	public $domain;
	
	public function __construct( $keyword = '', $limit = 0) {
		parent::__construct($keyword, $limit);
		
		$this->domain = 'www.articlesbase.com';
	}
	
	public function get_article_text( &$article ) {
		
		$html = $this->url_get_contents( $article['url'] );

		libxml_use_internal_errors(true);
		$pq = phpQuery::newDocumentHTML($html);

		$pq->find('div.KonaBody script')->parent()->remove();
		$content = pq('div.KonaBody')->html();
		
		$this->fix_urls($content, 'http://' . $this->domain);
		$this->url_add_nofolow($content);
		
		$article['content'] = strip_tags( $content, '<br><p><div><b><u><i><strong><body>' );
		
		return true;
		
	}
	
	public function get_article_list() {
		$keyword = $this->get_keyword();
		
		$list = array();
		
		$page = 1;
		
		$cnt = 0;
		while (1) {
			
			$searchUrl		= "http://" . $this->domain . "/find-articles.php?q=%s";
			if($page > 1) $searchUrl .= '&page=' . $page;
			
			$linkRx			= "<h3><a title=\"(.*?)\" href=\"(.*?)\"";
			$linkRxSwitches	= "si";
			
			$url  = sprintf( $searchUrl, urlencode($keyword) );
			$html = $this->url_get_contents( $url, 'http://' . $this->domain );
			
			preg_match_all( "/{$linkRx}/{$linkRxSwitches}", $html, $matches );
			
			if(count($matches[1]) == 0) break;
			
		    foreach ( $matches[1] as  $i => $match ) {
				$list[] = array(
					'url'   => $matches[2][$i],
					'title' => $matches[1][$i]
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