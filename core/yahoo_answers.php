<?php

class mpco_yahoo_answers {
		
	public $keyword;
	public $limit;
	public $cache_lifetime;
	
	private $app_id;
	private $region;
	private $yacat;
	private $get_comments;
	private $date_range;
	
	public function __construct( $keyword = '', $limit = 0, $app_id = 'WauPtk7V34H47Qw0sIYJBat5Qm67mhthPnj2jeDxicDpKLcRNcLQWMbtJXOGLA--', $region = 'us', $yacat = 0, $get_comments = 0, $date_range = 'all') {
		
		$this->keyword = $keyword;
		$this->limit   = $limit;
		
		$this->app_id  = $app_id;
		$this->region  = $region;
		$this->yacat   = $yacat;
		
		$this->date_range     = $date_range;
		$this->get_comments   = $get_comments;
		$this->cache_lifetime = 3600 * 1;
		
	}
	
	public function set_date_range($date_range) {
		$this->date_range = $date_range;
	}
	
	public function set_app_id($app_id) {
		$this->app_id = $app_id;
	}
	
	public function set_region($region) {
		$this->region = $region;
	}
	
	public function set_cat($yacat) {
		$this->yacat = $yacat;
	}
	
	public function set_comments($get_comments) {
		$this->get_comments = $get_comments;
	}
	
	function yahoo_answers_request($keyword, $limit, $start, $yapcat, $date_range) {	
	
		$appid   = $this->app_id;
		$region  = $this->region;
		
		$keyword = preg_replace('/[^\w\d\-+]/', '', str_replace(" ", "+", $keyword));
	
		$request = "http://answers.yahooapis.com/AnswersService/V1/questionSearch?appid=".$appid."&query=".$keyword."&region=".$region."&type=resolved&start=".$start;
	    if($limit) {
	    	$request .= '&results='.$limit;
	    }
	    if($yapcat != "") {
			$request .= '&category_id='.$yapcat;
		}
		
		if($date_range) {
			$request .= '&date_range='.$date_range;
		}
		/*if ( function_exists('curl_init') ) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $request);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			$response = curl_exec($ch);
			if (!$response) {
				$return["error"]["module"] = "Yahoo Answers";
				$return["error"]["reason"] = "cURL Error";
				$return["error"]["message"] = "cURL Error Number ".curl_errno($ch).": ".curl_error($ch);	
				return $return;
			}		
			curl_close($ch);
		} else { 				
			$response = @file_get_contents($request);
			if (!$response) {
				$return["error"]["module"] = "Yahoo Answers";
				$return["error"]["reason"] = "cURL Error";
				$return["error"]["message"] = "cURL is not installed on this server!";
				return $return;
			}
		}*/
		
		$response = $this->url_get_contents($request);
	    //sleep(rand(0,3));
		$pxml = simplexml_load_string($response);
		if ($pxml === False) {
			$emessage = "Failed loading XML, errors returned: ";
			foreach(libxml_get_errors() as $error) {
				$emessage .= $error->message . ", ";
			}
			$return["error"]["module"] = "Yahoo Answers";
			$return["error"]["reason"] = "XML Error";
			$return["error"]["message"] = $emessage;
			return $return;
		} else {
			return $pxml;
		}
	}
	
	function yahoo_get_answers($qid, $answercount) {
		$appid = $this->app_id;
		$requesturl = 'http://answers.yahooapis.com/AnswersService/V1/getQuestion?appid='.$appid.'&question_id='.$qid;
		
		$response = $this->url_get_contents($requesturl);
		
		if ($response === False) {
			return array();
		}
		
		$commentsFeed = simplexml_load_string($response);
		
		$answers = array();
		$i = 0;
		foreach ($commentsFeed->Question->Answers->Answer as $answer) {
			$answers[$i]["author"]    = $answer->UserNick;
			$answers[$i]["content"]   = $answer->Content;
			$answers[$i]["timestamp"] = $answer->Timestamp;
			$i++;
		}
		
		return $answers;
	}
	
	function yahoo_answers_post($keyword, $num, $start, $yapcat, $getcomments, $date_range) {
		global $wpdb, $wpr_table_templates;
		
		if($keyword == "") {
			$return["error"]["module"]  = "Yahoo Answers";
			$return["error"]["reason"]  = "No keyword";
			$return["error"]["message"] = "No keyword specified.";
			return $return;	
		}	
		
		/*$options = unserialize(get_option("wpr_options"));	
		$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'yahooanswers'");	
		if($template == false || empty($template)) {
			$return["error"]["module"] = "Yahoo Answers";
			$return["error"]["reason"] = "No template";
			$return["error"]["message"] = "Module Template does not exist or could not be loaded.";
			return $return;	
		}*/	
		$pxml = $this->yahoo_answers_request($keyword, $num, $start, $yapcat, $date_range);
		if(!empty($pxml["error"])) {return $pxml;}
		
		$posts = array();
		
		if ($pxml === False) {
			$posts["error"]["module"] = "Yahooanswers";
			$posts["error"]["reason"] = "Request fail";
			$posts["error"]["message"] = "API request could not be sent.";
			return $posts;		
		} else {
			if (isset($pxml->Question)) {
				foreach($pxml->Question as $question) {
					
					$attrs = $question->attributes();
					$qid = $question['id']; 			
					$title = $question->Subject;
					$content = $question->Content;
					$url = $question->Link;
					$user = $question->UserNick;
					$answercount = $question->NumAnswers;
					$timestamp = $question->Timestamp;
					
					//if ($options['wpr_ya_striplinks_q']=='yes') {$content = wpr_strip_selected_tags($content, array('a','iframe','script'));}
					
					$post = $template;				
					//$post = wpr_random_tags($post);
					
					// Answers
					$answerpost = "";
					//preg_match('#\{answers(.*)\}#iU', $post, $rmatches);			
					if (/*$rmatches[0] != false || */$getcomments == 1) {
						$answers = $this->yahoo_get_answers($qid,$answercount);				
					}
					/*if ($rmatches[0] != false && !empty($answers)) {
						$answernum = substr($rmatches[1], 1);
						for ($i = 0; $i < $answercount; $i++) {
							if($i == $answernum) {break;} else {	
								$answerpost .= "<p><i>Answer by ".$answers[$i]["author"]."</i><br/>".$answers[$i]["content"]."</p>";
								// Remove posted answer from comments array
								unset($answers[$i]);
							}
						}
						$answers = array_values($answers);
						$post = str_replace($rmatches[0], $answerpost, $post);				
					} else {
						$post = str_replace($rmatches[0], "", $post);					
					}*/				
					
					$posts[] = array(
						'title'     => $title,
						'content'   => $content,
						'url'       => $url,
						'user'      => $user,
						'keyword'   => $keyword,
						'answers'   => $answers,
						'timestamp' => $timestamp,
					);
					/*
					$post = str_replace("{question}", $content, $post);							
					$post = str_replace("{keyword}", $keyword, $post);
					$post = str_replace("{url}", $url, $post);	
					$post = str_replace("{user}", $user, $post);	
					$post = str_replace("{title}", $title, $post);	
						
					$posts[$x]["unique"] = $qid;
					$posts[$x]["title"] = $title;
					$posts[$x]["content"] = $post;	
					$posts[$x]["comments"] = $answers;	*/
					$x++;
				}
				
				if(empty($posts)) {
					$posts["error"]["module"] = "Yahooanswers";
					$posts["error"]["reason"] = "No content";
					$posts["error"]["message"] = "No (more) Yahoo Answers content found.";
					return $posts;			
				} else {
					return $posts;	
				}				
				
			} else {
				if (isset($pxml->Message)) {
					$message = 'There was a problem with your API request. This is the error Yahoo returned:'.' <b>'.$pxml->Message.'</b>';
					$posts["error"]["module"] = "Yahooanswers";
					$posts["error"]["reason"] = "API fail";
					$posts["error"]["message"] = $message;	
					return $posts;				
				} else {
					$posts["error"]["module"] = "Yahooanswers";
					$posts["error"]["reason"] = "No content";
					$posts["error"]["message"] = "No (more) Yahoo Answers content found.";
					return $posts;						
				}			
			}
		}	
	}
	
	public function get_items( &$rss, &$items ) {
		
		$posts = $this->yahoo_answers_post($this->keyword, $this->limit, 0, $this->yacat, $this->get_comments, $this->date_range);
		
		$items = array();
		$rss = new rss_wrap();
		
		$rss->set_title( 'Yahoo! Answers' . ': ' . $this->keyword );
		
		foreach ( $posts as $i => $post ) {
			$item = new yaans_item();
			$item->set_link( $post['url'] );
			$item->set_user( $post['user'] );
			$item->set_title( $post['title'] );
			$item->set_content( $post['content'] );
			$item->set_answers( $post['answers'] );
			$item->set_timestamp( $post['timestamp'] );
			
			$items[] = $item;
		}
		
		return $items;
		
	}
	
	public function url_get_contents( $url, $referer = "", $method = "GET" ) {
		if( $content = $this->get_cached($url) ) return $content;
		
		$curl_inst=true;
		if(!extension_loaded('curl') && !function_exists('ld') && !dl('curl.so') ) {
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
		    	$this->cache_it($url, $data);
				return $data;
		    }
		    return false;
		}
		
		$data = file_get_contents( $url );
		$this->cache_it($url, $data);
		return $data;
	}
	
	public function get_cached( $key ) {
		
		$path = mpco_plugin_dir() . '/cache/yaans';
		$file_name = $path . '/' . md5($key);
		
		if( file_exists( $file_name ) && (time() - filemtime($file_name) < $this->cache_lifetime) && ($content = file_get_contents( $file_name )) ) {
			return $content;
		}
		
		return null;
		
	}
	
	public function cache_it( $key, $content ) {
		
		$path = mpco_plugin_dir() . '/cache/yaans';
		
		if (!is_dir($path) && is_writable(mpco_plugin_dir() .'/cache')) {
			@mkdir($path, 0777);
			chmod($path, 0777);
		}
		
		if (!is_dir($path)) { return false; }
		
		$file_name = $path . '/' . md5($key);
		
		if (is_writable($path)) {
		
		if (!is_file($file_name)) @file_put_contents( $file_name, $content );
		chmod($file_name, "777");
		
		}
		
		return true;
		
	}
	
	public function get_keyword() {
		
		return $this->keyword;
		
	}
	
}

if(!class_exists('yaans_item')) {
	class yaans_item {
		
		private $title = '';
		private $link = '';
		private $content = '';
		private $description = '';
		private $copyright = '';
		private $user;
		private $answers;
		private $timestamp;
		
		public function get_timestamp() { return $this->timestamp; }
		public function set_timestamp( $timestamp ) { $this->timestamp = $timestamp; }
		
		public function get_user() { return $this->user; }
		public function set_user( $user ) { $this->user = $user; }
		
		public function get_answers() { return $this->answers; }
		public function set_answers( $answers ) { $this->answers = $answers; }
		
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
		
		private $title;
		
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