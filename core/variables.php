<?PHP

/////////////////////////////////////////////////////////
// multipressContent Defaults File
// 
// Use this file to modify any default settings
//


/////////////////////////////////////////////////////////
// Default Feed Settings

// General Options
$enabled = true;
$feed_type = 1; 
		//	 1 = RSS Feed,
		//	 2 = Article parsers
$source = 0;
		
$keywords_or_feed_url = '';
$default_status = 'publish';  // publish, pending, draft, private
$uniqify = false;
$use_date_from_feed = 0;
$schedule_posts_from_feed = 1;
$scheduleposts_range = 24;

// Feed Processing
$feed_processing_schedule = 0;
	// 0 = With every scheduled update
	// 1 = after every x updates
	// 2 = Manually or when notified via XML-RPC ping

$feed_processing_every_x_updates = 2;
$post_processing = 0;
	// 0 = Include all posts
	// 1 = Include first x posts
	// 2 = Randomly include x% of all posts
$max_posts_per_update = 20;
$randomly_include_x_percent_of_posts = 50;


// Yahoo! Answers /

$yahoo_numitems = 50;
$yahoo_region = "us";
$yahoo_category = "0";
$yahoo_range = "all";
$yahoo_repliesascomments = "1";
$yahoo_backdate = "1";
			  
// Tags
$additional_tags = '';

// Categories
$assign_posts_to_this_category = '';
$add_additional_categories = false;
$add_categories_as_tags = true;
$randomly_add_selected_categories = true;
$use_categories_from_original = true;
$add_categories_from_original = false;
			  
// Authors
$author = "";//RANDOM_AUTHOR;
	//	RANDOM_AUTHOR
	//	AUTHOR_FROM_FEED
	//  or specific author name
	
$exclude_author = "admin, www";
$author_group = "Author";

$alternate_author_if_doesnt_exist = ADD_AUTHOR;
	//	ADD_AUTHOR
	//	SKIP_POST
	//	RANDOM_AUTHOR
	//  or specific author name

$perc_author = 50;
$type_author = 0;

// 0 - all authors
// 1 - X% random authors
// 2 - specific authors
	
// Images
$save_full_images = true;
$create_thumbnails = true;

// Embedded Video Player
$video_width = 250;
$video_height = 206;
$custom_player_url = '';

// Include Posts that Contain
$all_these_words = '';
$any_of_these_words = '';
$the_exact_phrase = '';
$none_of_these_words = '';

// keywords for content

$use_author_keywords = 0;
$override_keywords = "";

// Custom Fields
//$custom_fields = Array('author', 'copyright');
//$custom_values = Array('YouTube', "Copyright (c) YouTube, LLC");

// Post Templates
$feed_post_templates = array();
$feed_post_templates[1] = '
<p>$content$</p>
{if $video$}<p>$video$</p>{/if}
{if $thumbnail$}<p>$thumbnail$</p>{/if}
random(Read more here|Read the original here|Read more from the original source|Continued here|Read more|More here|View original post here|More|See more here|See original here|Originally posted here|Here is the original post|See the original post|The rest is here|Read the rest here|See the rest here|Go here to read the rest|Go here to see the original|See the original post here|Read the original post|Original post|Read the original|Link|Excerpt from|View post|Visit link|Follow this link|Continue reading here|See the article here|Read this article|Read more): <a target="_blank" href="$link$" title="$title$">$title$</a>';

/*%if:video%<p>%video%</p>%endif:video%
%if:thumbnail%<p>%thumbnail%</p>%endif:thumbnail%*/

$feed_post_templates[2] = '
<p>$content$</p>
{if $video$}<p>$video$</p>{/if}
{if $thumbnail$}<p>$thumbnail$</p>{/if}
random(Read more here|Read the original here|Read more from the original source|Continued here|Read more|More here|View original post here|More|See more here|See original here|Originally posted here|Here is the original post|See the original post|The rest is here|Read the rest here|See the rest here|Go here to read the rest|Go here to see the original|See the original post here|Read the original post|Original post|Read the original|Link|Excerpt from|View post|Visit link|Follow this link|Continue reading here|See the article here|Read this article|Read more): <a target="_blank" href="$link$" title="$title$">$title$</a>';

$feed_post_templates[3] = '
<p>$content$</p>
<p></p>
<p>Best reply by $reply-author$:</p>
<p>$reply-text$</p>
random(Read the original question here|Read the original question on Yahoo! site|Read more replies)
<a target="_blank" href="$link$" title="$title$">$title$</a>
';



// Search and Replace
// $search_for_patterns = Array('Chevy');
// $replace_with_patterns = Array('Chevrolet');


/////////////////////////////////////////////////////////
// Default Tag Options

// General Options
$minimum_tag_length = 3;
$maximum_tag_length = 25;
$maximum_tags_per_post = 15;

// Tag Sources
$use_original_tags_from_feed = true;
$use_internal_tagging_engine = true;
$visit_source_url = false;
$get_yahoo_tags = false;
$yahoo_app_id = '';

// Additional Tags
$randomly_add_these_tags = '';

// Tag Filtering
$do_not_use_these_as_tags = '';


/////////////////////////////////////////////////////////
// Default Filtering Options

// Duplicate Posts
$match_title = true;
$match_link = true;
$uniques_per_author = false;

// Title filtering
$maximum_title_length = 250;
$long_title_handling = 0; 
		// 0 = Truncate to the nearest word
		// 1 = Skip the post
$skip_titles_in_all_caps = false;
$skip_titles_with_multiple_punctuation_marks = false;

// Blacklists
$url_blacklist = '';
$keywords_blacklist = '';


/////////////////////////////////////////////////////////
// Default Settings

// Registration
$serial_number = '';
$check_for_updates = false;

// General Options
$multipressContent_enabled = true;
$minimum_time_between_updates = 24; // minutes

// Excerpts
$minimum_excerpt_length = 1;
$maximum_excerpt_length = 3;
$excerpt_type = 1;   
		// 0 = Words
		// 1 = Sentences
		// 2 = Paragraphs

// WordPress Options
$use_link_info = true;
$use_author_info = false;

// HTTP Options
$http_referrer = "http://google.com/"; //get_settings('home');
$http_user_agent = 'Feedfetcher-Google; (+http://www.google.com/feedfetcher.html)';

// RSS Options
$rss_cache_timeout = 3600;

// Debug
$logging = false;
$show_debug = false;

$article_limit = 10;

?>