<?php

abstract class abstract_article {
	
	public $keyword;
	public $limit;
	public $cache_lifetime;
	
	public function __construct( $keyword = '', $limit = 0) {
		
		$this->keyword = $keyword;
		$this->limit   = $limit;
		$this->cache_lifetime = 3600 * 24;
		
	}
	
	public function get_items() {
		
		if( !$article_list = $this->get_article_list() ) {
			return array();
		}
		
		if( $this->limit > 0 ) {
			$article_list = array_slice( $article_list, 0, $this->limit );
		}
		
		
		foreach ( $article_list as $i => $article ) {
			
			if( $cached = $this->get_cached( $article['url'] ) ) {
				$article_list[$i] = unserialize( $cached );
				continue;
			}
			
			if(!$this->get_article_text( $article_list[$i] )) {
				unset($article_list[$i]);
			}
			
			sleep(rand(1, 3));
			$this->cache_it( $article_list[$i]['url'], serialize( $article_list[$i] ) );
			
		}
		
		return $article_list;
		
	}
	
	public function url_get_contents( $url, $referer = "", $method = "GET" ) {
		
		$curl_inst=true;
		if(!extension_loaded('curl') && (!function_exists('dl') || !dl('curl.so'))) {
			$curl_inst = false;
		}
	
		if($curl_inst) {
		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $url);
		    //curl_setopt($ch, CURLOPT_HEADER, 1);
		    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)';
		    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
		    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 360);
			curl_setopt($ch, CURLOPT_REFERER, $referer);
			
		    //curl_setopt($ch, CURLOPT_INTERFACE, $ip);
		    if ($method == 'POST') {
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
		    }
		    $data = curl_exec($ch);
		    //echo curl_error($ch);
		    curl_close($ch);
		    if ($data) {
				return $data;
		    }
		    return false;
		}
		
        $header = "User-Agent: " . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)');
        
        if($referer) {
            $header .=  "\r\nReferer: " . $referer;
        }
        
		$opts = array(
			'http' => array(
				'method' => "GET",
				'header' => $header,
			)
		);
		$context = stream_context_create($opts);
		
		$html = file_get_contents( $url, false, $context);
		
		return $html;
		
	}
	
	public function get_cached( $key ) {
		
		$path      = mpco_plugin_dir() . '/cache/articles';
		$file_name = $path . '/' . md5($key);
		
		if( file_exists( $file_name ) && ((time() - filemtime($file_name)) < $this->cache_lifetime) && ($content = file_get_contents( $file_name )) ) {
			return $content;
		}
		
		return null;
		
	}
	
	public function cache_it( $key, $content ) {
		
		$path      = mpco_plugin_dir() . '/cache/articles';
		
		if (!is_dir($path) && is_writable(mpco_plugin_dir() .'/cache')) {
			@mkdir($path, 0777, true);
			@chmod($path, 0777);
		}
		
		if( !is_dir($path) || !is_writable($path) ) return false;
				
		$file_name = $path . '/' . md5($key);
		
		file_put_contents( $file_name, $content );
		@chmod($file_name, 0777);
		
		return true;
		
	}
	
	public function get_keyword() {
		
		return $this->keyword;
		
	}
	
		
	public function fix_urls(&$content, $domain) {
		
		if( false === strpos($domain, '://')) {
			$domain .= 'http://';
		}
		
		$domain = trim($domain, '/');
		
		$linkRx			= "href=[\"'](.*?)[\"']";
		$linkRxSwitches	= "si";
		
		preg_match_all( "/{$linkRx}/{$linkRxSwitches}", $content, $matches );
		
		if(!$matches) return ;
		
		foreach ($matches[1] as $i => $match) {
			
			if( false !== strpos($match, '://') ) {
				continue;
			}
			
			$orig_url = $match;
			$orig_url = ltrim($orig_url, '/');
			$new_url  = $domain . '/' . $orig_url;
			
			$piece = str_replace($match, $new_url, $matches[0][$i]);
			
			$content = str_replace($matches[0][$i], $piece, $content);
			
		}
				
	}
	
	public function url_add_nofolow(&$content) {
		
		$linkRx         = "<a (.*?)>";
		$linkRxSwitches	= "si";
		preg_match_all("/{$linkRx}/{$linkRxSwitches}", $content, $matches);
		
		if(!$matches) return ;
		
		foreach ($matches[0] as $i => $match) {
			if(false !== strpos($match, 'nofollow')) continue;
			
			$new_a = str_replace($matches[1][$i], $matches[1][$i] . " rel='nofollow'", $match);
			
			$content = str_replace($match, $new_a, $content);
			
		}

	}
	
	abstract public function get_article_list();
	abstract public function get_article_text(&$article);
	
}


?>