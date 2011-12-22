<?php

if(!class_exists('article_parser')) {
class article_parser {
	
	private $keyword;
	private $source;
	private $source_title;
	private $limit;
	
	public function __construct( $keyword, $source, $source_title, $limit ) {
		
		$this->set_keyword( $keyword );
		$this->set_source( $source );
		$this->set_source_title( $source_title );
		$this->set_limit( $limit );
		
	}
	
	public function set_keyword( $keyword ) {
		$this->keyword = $keyword;
	}
	
	public function set_limit( $limit ) {
		$this->limit = $limit;
	}
	
	public function set_source( $source ) {
		$this->source = $source;
	}
	
	public function set_source_title( $source_title ) {
		$this->source_title = $source_title;
	}
	
	public function grab( &$rss, &$items ) {
		
		$items = array();
		
		$rss = new rss_wrap();
		
		$rss->set_title( $this->source_title . ': ' . $this->keyword );
		
		$class_name = '';
		switch ( $this->source ) {
			case 1:
				$class_name = 'ezine';
				break;
				
			case 2:
				$class_name = 'isnare';
				break;
				
			case 3:
				$class_name = 'articlebase';
				break;

			case 4:
				$class_name = 'artigonal';
				break;
				
			case 5:
				$class_name = 'articuloz';
				break;
			
			case 6:
				$class_name = 'articlonet';
				break;
				
			case 7:
				$class_name = 'goarticles';
				break;
				
			default:
				return false;
		}
		
		if( !class_exists( $this->source ) ) {
			if ( is_file( mpco_plugin_dir() . '/article_parsers/' . $class_name . '.class.php' ) ) {
				require_once( mpco_plugin_dir() . '/article_parsers/' . $class_name . '.class.php' );
			} else {
				return false;
			}
		}
		
		$parser = new $class_name( $this->keyword, $this->limit );
		
		$articles = $parser->get_items();
		
		$items = array();
		foreach ( $articles as $article ) {
			$item = new article_item();
			$item->set_link( $article['url'] );
			$item->set_title( $article['title'] );
			if($article['content']) {
				$item->set_content( $article['content'] );
			} else {
				$item->set_content( $article['title'] );
			}
			
			$items[] = $item;
		}
		
	}
	
}
}

if(!class_exists('article_item')) {
class article_item {
	
	var $title = '';
	var $link = '';
	var $content = '';
	var $description = '';
	var $copyright = '';
	
	public function get_link() { return $this->link; }
	public function set_link( $link ) { $this->link = $link; }
	
	public function get_title() { return $this->title; }
	public function set_title( $title ) { $this->title = $title; }
	
	public function get_content() { return $this->content; }
	public function set_content( $content ) { $this->content = $content; }
	
	public function get_description() { return $this->description; }
	public function set_description( $description ) { $this->description = $description; }
	
	public function get_copyright() { return $this->copyright; }
	public function set_copyright( $copyright ) { $this->copyright = $copyright; }
	
	public function get_enclosures() { return array(); }
	
	public function get_item_tags( $arg1, $arg2 ) { return null; }
	
	public function get_categories() { return null; }
	
}
}

if(!class_exists('rss_wrap')) {
class rss_wrap {
	
	var $title;
	
	public function get_title() { return $this->title; }
	public function set_title( $title ) { $this->title = $title; }
	
	public function get_permalink() {
		return '';
	}
	
	public function get_description() {
		return '';
	}
	
	public function get_favicon() {
		return '';
	}
	
	public function get_image_url() {
		return '';
	}
	
	public function get_image_link() {
		return '';
	}
	
	public function get_image_title() {
		return '';
	}
	
	public function subscribe_url() {
		return '';
	}	
}
}

?>