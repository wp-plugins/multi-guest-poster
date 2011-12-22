<?

global $isLite;

$isLite = TRUE;

require_once(dirname(__FILE__).'/core/license.php');
require_once(dirname(__FILE__).'/core/Spintaxer.class.php');
			
define("MPCO_VERSION", "1.0");

define("MPCO_FILE_VERSION", "1.0");
define("mpco_BETA", false);

//////////////////////////////////////////////////////////////////////////////////////
// additional options not available on the admin page that you can customize here

define("EXTRA_IMAGE_FIELDS", false);
define("ALWAYS_ATTACH_IMAGES", true);

// Set the weight given to different post elements
// e.g., a value of 10 would be the same as that word/phrase appearing 10 extra times in the
// article context. A value of 0 means no extra weight given (which will speed up processing).
define("META_KEYWORDS_WEIGHT", "15");
define("H1_WEIGHT", "12");
define("H2_WEIGHT", "10");
define("H3_WEIGHT", "6");
define("REL_TAGS_WEIGHT", "3"); // gives bonus to the last word in the link url if rel="tag" is set
define("LINK_TEXT_WEIGHT", "4");
define("ALT_TAGS_WEIGHT", "3");
define("URL_TAGS_WEIGHT", "3"); // gives bonus to words in the url that follow "tag", "category", or "wiki" even if rel="tag" isn't set
define("LINK_TITLE_WEIGHT", "3");
define("BOLD_WORD_WEIGHT", "4");
define("TAGS_TXT_WEIGHT", "500"); // Bonus for words found in tags.txt
define("YAHOO_TAGS_WEIGHT", "3");


// SimplePie by default filters out certain HTML tags for security purposes. You can override this by changing
// the following settings. Use these settings with caution. 
define("ALLOW_OBJECT_AND_EMBED_TAGS", false);  // Allows object, embed, param
define("ALLOW_FORM_TAGS", false);  // Allows form, input
define("ALLOW_FRAME_TAGS", false); // Allows frame, iframe, frameset
define("ALLOW_SCRIPT_TAGS", false); // Allows class, expr, script, noscript, onclick, onerror, onfinish, onmouseover, onmouseout, onfocus, onblur 

// This turns off all HTML tag and attribute filtering.
define("ALLOW_ALL_TAGS", false);

// Set the next line to true if you want HTML tags encoded rather than stripped out
define("ENCODE_INSTEAD_OF_STRIP", false);


// If SimplePie doesn't recognize a malformed feed, set the following to true to force processing anyway
define("FORCE_FEED", false);




/////////////////////////////////////////////////////////////////////////////////////
// Do not edit below this line


// Constants for combo boxes
define("RANDOM_AUTHOR", "(Use random author)");
define("AUTHOR_FROM_FEED", "(Use author from feed)");
define("ADD_AUTHOR", "(Create new author)");
define("SKIP_POST", "(Skip the post)");

// Other contstants
define("mpco_MANUAL_UPDATES", 2);
define("mpco_EVERY_X_UPDATES", 1);

define("mpco_ITEM_MAX_POSTS", 1);
define("mpco_ITEM_PERCENT_POSTS", 2);

define("mpco_TITLE_TRUNCATE", 0);
define("mpco_TITLE_SKIP", 1);

define("mpco_WP_27", "2.7-RC0");
define("mpco_WP_28", "2.8");


$mpco_options = array();
$feedtypes = array();
$multipressContent =  object;
$rss = object;


if (!class_exists('multipressContent')) {
class multipressContent	{
    var $db_table_name = '';
    var $tags = array();
    var $keywords = array();
    var $rssmodules = array();

    var $exclude_domains = array();
    var $exclude_words = array();
    var $global_extra_tags = '';
    var $categories = array();
    var $bookmarks = array();
    var $own_domain = '';
    var $filtered_tags = array();
    var $upload_dir = '';
    var $upload_url = '';

    var $current_feed = array();
    var $current_item = array();
    var $postinfo = array();
    var $logger = object;

    var $show_output = true;
    var $debug = false;

    var $auth_key;

    //---------------------------------------------------------------
    function multipressContent() {
        $this->__construct();
    }

    //---------------------------------------------------------------
    function __construct() {
        global $wpdb, $mpco_options, $feedtypes, $sources,$mpc_license, 
                $yahoo_regions, $yahoo_categories, $yahoo_ranges, $isLite;

        $this->upgrade();

        // Load common functions
        require_once(dirname(__FILE__).'/core/functions.php');

        if (!$isLite && count(mpc_what_is_not_active()) > 0) {
            add_action('admin_footer', 'twoeWPL_notice');				
        }

        // WordPress hooks
        add_filter('the_content', 'mpc_uniqify', 100);
        add_action("admin_menu", array(&$this,"mpco_addAdminPages"));
        add_action('shutdown', array(&$this,'mpco_shutdownIntercept'));
        add_action('wp_footer', array(&$this,'mpco_wpfooterIntercept'));
        add_action('akismet_spam_caught', array(&$this,'mpco_akismetntercept'));

        add_action('content_wpcron_hook', array(&$this,'mpco_shutdownIntercept'));			

        register_activation_hook(__FILE__,"mpco_installOnActivation");

        // Load php4 compatibility functions if needed
        if (version_compare(PHP_VERSION, '5.0.0', '<')) require_once(dirname(__FILE__).'/core/compat.php');
        $mpco_options = mpco_getOptions();

        $feedtypes = array(
            "1" => "RSS Feed",
            "2" => "Article engines",
            "3" => "Yahoo! Answers",			
        );

        $sources[2] = array (
            '1' => 'Ezine Articles',
            '2' => 'iSnare Articles',
            '3' => 'Articlebase',
            '4' => 'Artigonal - Portuguese',
            '5' => 'Articuloz - Spanish',
            '6' => 'Articlonet - French',
            '7' => 'GoArticles',
        );

        $yahoo_regions = array (
            'us' => "USA",
            'ca' => "Canada",
            'uk' => 'United Kingdom',
            'au' => 'Australia',
            'in' => 'India',
            'es' => 'Spain',
            'br' => 'Brazil',
            'ar' => 'Argentina',
            'mx' => 'Mexico',
            'it' => 'Italy',
            'de' => 'Germany',
            'fr' => 'France',
            'sg' => 'Singapore'
        );

        $yahoo_categories = array (
            "0" => "All",
            "396545012" => "Arts &amp; Humanities",
            "396545144" => "Beauty &amp; Style",
            "396545013" => "Business &amp; Finance",
            "396545311" => "Cars &amp; Transportation",
            "396545660" => "Computers &amp; Internet",
            "396545014" => "Consumer Electronics",
            "396545327" => "Dining Out",
            "396545015" => "Education &amp; Reference",
            "396545016" => "Entertainment &amp; Music",
            "396545451" => "Environment",
            "396545433" => "Family &amp; Relationships",
            "396545367" => "Food &amp; Drink",
            "396545019" => "Games &amp; Recreation",
            "396545018" => "Health",
            "396545394" => "Home &amp; Garden",
            "396545401" => "Local Businesses",
            "396545439" => "News &amp; Events",
            "396545443" => "Pets",
            "396545444" => "Politics &amp; Government",
            "396546046" => "Pregnancy &amp; Parenting",
            "396545122" => "Science &amp; Mathematics",
            "396545301" => "Social Science",
            "396545454" => "Society &amp; Culture",
            "396545213" => "Sports",
            "396545469" => "Travel",
            "396546089" => "Yahoo! Products"
        );


        $yahoo_ranges = array (
            "all" => "Any time",
            "7" => "Within the last 7 days",
            "7-30"=> "A week to month old",
            "30-60" => "A month to two months old",
            "60-90" => "Two to three months old",
            "more90" => "Over three months old"
        );

    }

    private function upgrade() {
        global $wpdb;

        $new_fields = array(
            'override_truncate_post' => 'TINYINT(1) NOT NULL',
            'truncate_post'          => 'TINYINT(1) NOT NULL',
            'truncate_post_over'     => 'INT(11) NOT NULL',
            'spintaxes'              => 'TEXT NOT NULL',
            'override_spintax_links_limit' => 'TINYINT(1) NOT NULL',
            'spintax_links_limit'    => 'TINYINT(1) NOT NULL',
            'spintax_links_limit_x'  => 'INT(11) NOT NULL',
            'remove_words'           => 'TEXT NOT NULL',
            'spintaxes_skip_itself'  => 'TINYINT(1) NOT NULL',
        );

        $sql = "SHOW COLUMNS FROM `" . mpco_tableName() . "`";
        $columns = $wpdb->get_results($sql, 'ARRAY_A');
        foreach($columns as $col) {
            if(isset($new_fields[$col['Field']])) {
                unset($new_fields[$col['Field']]);
            }
        }

        foreach ($new_fields as $key => $value) {
            $sql = 'ALTER TABLE  `' . mpco_tableName() . '` ADD  `' . $key . '` ' . $value;
            $wpdb->query($sql);
        }

        /*
        // absolete. i hope...)
        foreach($columns as $col) {
            if($col['Field'] == 'mu_type') return true;
        }
        $sql = 'ALTER TABLE  `' . mpco_tableName() . '` ADD  `mu_type` INT NOT NULL AFTER  `title`';
        $wpdb->query($sql);
        */
        return true;
    }

    //---------------------------------------------------------------
    function mpco_addAdminPages(){
        add_posts_page('ContentBox', 'Automatic posting', 7, 'ContentBox', 'mpco_FeedsPage');
        add_options_page('ContentBox', 'Autoposting settings', 7, 'ContentBoxSettings', 'mpco_SettingsPage');

        global $isLite;

        if (!$isLite) twoenoughWpRegisterPages();

        // load the scripts we will need
        if (stristr($_REQUEST['page'], 'ContentBox')) {
            wp_enqueue_script('post');
            wp_enqueue_script('thickbox');
            wp_enqueue_script('postbox');
            wp_enqueue_script('admin-tags');
        }
    }

    //---------------------------------------------------------------
    function mpco_errorHandler($code, $message, $file, $line) {
        if (stristr($file, 'wp-includes') || stristr($file, 'readability')) return;

        switch ($code) {
            case E_WARNING:
            case E_USER_WARNING:
            $priority = PEAR_LOG_WARNING;
            break;

            case E_NOTICE:
            case E_USER_NOTICE:
            //$priority = PEAR_LOG_NOTICE;
            return;
            break;

            case E_ERROR:
            case E_USER_ERROR:
            $priority = PEAR_LOG_ERR;
            break;

            default:
            //$priority = PEAR_LOG_INFO;
            return;
        }

        $this->mpco_logMsg($message . ' in ' . $file . ' at line ' . $line,	$priority);
    }

    //---------------------------------------------------------------
    // main feed processing procedure
    function mpco_processFeeds($fid = '', $manual_update = false) {
        global $wpdb, $mpco_options, $rss;

        @set_time_limit(0);
        @ignore_user_abort(true);

        $box_not_closed = false;

        kses_remove_filters();

        // Logging, debugging, and error handling
        if (isset($multipressContent)) {
            $this->mpco_initlogger();
        }
        //set_error_handler(array(&$this, 'mpco_errorHandler'));
        if ($manual_update || $fid) {
            $type = 'manual';
        } else {
            $type = 'scheduled';
        }

        if ($mpco_options['running'] != false && $type == 'scheduled') {
            return;
        }


        // Includes
        if (!class_exists('SimplePie')) {
            require_once(mpco_plugin_dir().'/core/simplepie.php');
        }

        $this->rssmodules = array(
            "access" => "http://www.bloglines.com/about/specs/fac-1.0",
            "admin" => "http://webns.net/mvcb/",
            "ag" => "http://purl.org/rss/1.0/modules/aggregation/",
            "annotate" => "http://purl.org/rss/1.0/modules/annotate/",
            "app" => "http://www.w3.org/2007/app",
            "audio" => "http://media.tangent.org/rss/1.0/",
            "atom" => SIMPLEPIE_NAMESPACE_ATOM_10,
            "atom10" => SIMPLEPIE_NAMESPACE_ATOM_10,
            "atom03" => SIMPLEPIE_NAMESPACE_ATOM_03,
            "blogChannel" => "http://backend.userland.com/blogChannelModule",
            "cc" => "http://web.resource.org/cc/",
            "cf" => "http://www.microsoft.com/schemas/rss/core/2005",
            "creativeCommons" => "http://backend.userland.com/creativeCommonsRssModule",
            "company" => "http://purl.org/rss/1.0/modules/company",
            "content" => "http://purl.org/rss/1.0/modules/content/",
            "conversationsNetwork" => "http://conversationsnetwork.org/rssNamespace-1.0/",
            "cp" => "http://my.theinfo.org/changed/1.0/rss/",
            "dc" => "http://purl.org/dc/elements/1.1/",
            "dc10" => SIMPLEPIE_NAMESPACE_DC_10,
            "dc11" => SIMPLEPIE_NAMESPACE_DC_11,
            "dcterms" => "http://purl.org/dc/terms/",
            "ecommerce" => "http://shopping.discovery.com/erss", 
            "email" => "http://purl.org/rss/1.0/modules/email/",
            "ev" => "http://purl.org/rss/1.0/modules/event/",
            "fh" => "http://purl.org/syndication/history/1.0",
            "g" => "http://base.google.com/ns/1.0",
            "gCal" => "http://schemas.google.com/gCal/2005",
            "gd" => "http://schemas.google.com/g/2005",
            "geo" => "http://www.w3.org/2003/01/geo/wgs84_pos#",
            "geourl" => "http://geourl.org/rss/module/",
            "georss" => "http://www.georss.org/georss",
            "gml" => "http://www.opengis.net/gml",
            "icbm" => "http://postneo.com/icbm",
            "im" => "http://phobos.apple.com/rss",
            "image" => "http://purl.org/rss/1.0/modules/image/",
            "itunes" => "http://www.itunes.com/dtds/podcast-1.0.dtd",
            "feedburner" => "http://rssnamespace.org/feedburner/ext/1.0",
            "foaf" => "http://xmlns.com/foaf/0.1/",
            "l" => "http://purl.org/rss/1.0/modules/link/",
            "media" => "http://search.yahoo.com/mrss/",
            "mathml" => "http://www.w3.org/1998/Math/MathML",
            "opensearch10" => "http://a9.com/-/spec/opensearchrss/1.0/",
            "opensearch" => "http://a9.com/-/spec/opensearch/1.1/",
            "opml" => "http://www.opml.org/spec2",
            "rdf" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
            "rdfs" => "http://www.w3.org/2000/01/rdf-schema#",
            "ref" => "http://purl.org/rss/1.0/modules/reference/",
            "reqv" => "http://purl.org/rss/1.0/modules/richequiv/",
            "rss091" => "http://purl.org/rss/1.0/modules/rss091#",
            "rx" => "urn:ebay:apis:eBLBaseComponents",
            "search" => "http://purl.org/rss/1.0/modules/search/",
            "slash" => "http://purl.org/rss/1.0/modules/slash/",
            "ss" => "http://purl.org/rss/1.0/modules/servicestatus/",
            "str" => "http://hacks.benhammersley.com/rss/streaming/",
            "sub" => "http://purl.org/rss/1.0/modules/subscription/",
            "sx" => "http://feedsync.org/2007/feedsync",
            "svg" => "http://www.w3.org/2000/svg",
            "sy" => "http://purl.org/rss/1.0/modules/syndication/",
            "taxo" => "http://purl.org/rss/1.0/modules/taxonomy/",
            "thr" => "http://purl.org/rss/1.0/modules/threading/",
            "thr" => "http://purl.org/syndication/thread/1.0",
            "trackback" => "http://madskills.com/public/xml/rss/module/trackback/",
            "wfw" => "http://wellformedweb.org/CommentAPI/",
            "wiki" => "http://purl.org/rss/1.0/modules/wiki/",
            "wiki" => "http://www.usemod.com/cgi-bin/mb.pl?ModWiki",
            "soap" => "http://schemas.xmlsoap.org/soap/envelope/",
            "atom" => "http://www.w3.org/2005/Atom",
            "xhtml" => "http://www.w3.org/1999/xhtml",
            "rss" => '',
            "rss20" => '',	
            "rss090" => SIMPLEPIE_NAMESPACE_RSS_090,
            "rss10" => SIMPLEPIE_NAMESPACE_RSS_10,
            "rss11" => "http://purl.org/net/rss1.1#",
            "g" => "http://base.google.com/ns/1.0",
            "xml" => "http://www.w3.org/XML/1998/namespace",
            "openid" => "http://openid.net/xmlns/1.0",
            "kml20" => "http://earth.google.com/kml/2.0",
            "kml21" => "http://earth.google.com/kml/2.1",
            "kml22" => "http://earth.google.com/kml/2.2",
            "xlink" => "http://www.w3.org/1999/xlink",
            "yt" => "http://gdata.youtube.com/schemas/2007",
            "coupon" => "http://www.formetocoupon.com/rss/V2/",
        );

        $this->show_output =  (bool)(is_admin() && $manual_update);

        // Set last updated time
        $mpco_options['lastupdate'] = time();

        ////////////////////////////////////////////////////////////////////////////////////
        mpco_saveOptions();
        // Get the feed info from the db
        $sql = "SELECT * FROM " . mpco_tableName();
        if( is_array($fid) )	{
            $cond = array();
            foreach ( $fid as $_fid ) {
                $cond[] = 'id='.$wpdb->escape($_fid);
            }
            $sql .= ' WHERE ' . join(' OR ', $cond);
        } elseif (strlen($fid)) {
            $manual_update = true;
            $sql .= ' WHERE id = '.$wpdb->escape($fid);
        }
        $feeds = $wpdb->get_results($sql, 'ARRAY_A');
        // Get some global settings
        $this->exclude_domains = mpco_splitList(strtolower($mpco_options['domains_blacklist']));
        $this->exclude_words = mpco_splitList($mpco_options['keywords_blacklist']);
        $this->global_extra_tags = $mpco_options['tags'];
        $this->categories = get_categories('orderby=name&hide_empty=0');
        $this->bookmarks = get_bookmarks();
        $this->own_domain = str_replace("www.", "", str_ireplace("http://", "", twoenough_get_option('siteurl')));
        $this->filtered_tags = mpco_splitList($mpco_options['notags']);
        $uploaddir_t =  wp_upload_dir();
        $this->upload_dir = $uploaddir_t['path'];
        $this->upload_url = $uploaddir_t['url'];

        // Populate list of authors

        if(twoenough_is_mu()) {
            $this->userlist = array_map(create_function('$a', 'return (array)$a;'), get_users_of_blog($GLOBALS['current_blog']->blog_id));
        } else {
            $this->userlist =  array();
            $users = $wpdb->get_results("SELECT ID, user_login, display_name FROM $wpdb->users ORDER BY display_name");
            if (is_array($users)) {
                foreach ($users as $user) {
                    $this->userlist[$user->ID] = array('ID'=> $user->ID, 'display_name' => $user->display_name, 'user_login' => $user->user_login);
                }
            }
        }

        // Output for manual processing
        if (count($feeds) < 1) {
            $this->mpco_logMsg('There are no feeds to process.', 'warn');
            return;
        }

        if ($this->show_output) {
            if (count($feeds) > 1) $plural = 's';

            global $isLite;
            if (!$isLite && !twoenoughWpCheckStoredLic('mpc', 3, 2) && !twoenoughWpCheckStoredLic('mpc', 3, 0)) $activationText = '<b>Warning! This install of MultiPress is not registered. No posts will be added!</b>'; else $activationText = '';

            echo '<div id="message" class="updated fade"><p>Processing '.count($feeds).' rule'.$plural.'...</p>'.$activationText.'</div><br />';
            echo '<style type="text/css">.divHide{display:none;}.divShow{display:block;}</style>';
            echo '<script type="text/javascript" language="javascript">function showhide(obj) {';
            echo 'var el=document.getElementById(obj);';
            echo '	if (el.className == "divHide") {';
            echo '	el.className = "divShow";';
            echo '	} else {';
            echo '	el.className = "divHide";';
            echo '	}}</script>';
            echo '<div class="wrap"><div id="poststuff"><div class="post-body">';
        }


        $preview_mode= false;
        if( isset($_REQUEST['preview']) && $_REQUEST['preview'] ) {
            $preview_mode = true;
        }

        //---------------------------
        // Import feeds - main loop
        foreach ($feeds as $feed) {

            if(twoenough_is_mu()) {
                if($feed['mu_type'] != 0) {
                    switch_to_blog($feed['mu_type']);
                }
                $this->userlist = array_map(create_function('$a', 'return (array)$a;'), get_users_of_blog($GLOBALS['current_blog']->blog_id));
            }

            $this->current_feed = $feed;

            if ($box_not_closed == true) {
                if ($this->show_output) echo '</div></div><br />';
            }
            $box_not_closed = false;

            // Check to see if we are manually running one feed
            // if not, check the schedule
            if (!$fid && !$manual_update) {
                if (!$this->mpco_checkFeedSchedule($feed)) continue;
            }

            // And make sure the feed is enabled
            if ($this->current_feed['enabled'] == false && (!$fid)) continue;

            if(!$preview_mode) {
                $sql ='UPDATE ' . mpco_tableName() . ' SET `lastRun`='.time().' WHERE id='.$wpdb->escape($this->current_feed['id']).';';
                $ret = $wpdb->query($sql);
            }

            //// Initialize feed settings

            // load custom fields for this feed
            if (count(mpco_unserialize($this->current_feed['customfield'])) > 0 && count(mpco_unserialize($this->current_feed['customfieldvalue'])) > 0) {
                if (is_array(mpco_unserialize($this->current_feed['customfield']))) $this->current_feed['customFields'] = @array_combine(mpco_unserialize($this->current_feed['customfield']),mpco_unserialize($this->current_feed['customfieldvalue']));
            }

            // Load other feed-level settings
            $this->current_feed['feed_extra_tags'] = mpco_unserialize($this->current_feed['tags']);
            $this->current_feed['nowords'] = mpco_splitList($this->current_feed['includenowords']);
            $this->current_feed['allwords'] = mpco_splitList($this->current_feed['includeallwords']);
            $this->current_feed['anywords'] = mpco_splitList($this->current_feed['includeanywords']);
            $this->current_feed['phrase'] = mpco_splitList($this->current_feed['includephrase']);

            if (strlen($this->current_feed['customplayer']) == 0) {
                $this->current_feed['customplayer']= mpco_pluginURL() . '/mediaplayer.swf';
            }

            if (is_array(mpco_unserialize($this->current_feed['searchfor']))) $this->current_feed['search'] = array_merge(mpco_unserialize($this->current_feed['searchfor']));
            $this->current_feed['replace'] =mpco_unserialize($this->current_feed['replacewith']);

            // we need to decide which authors we are running this feed for
            /*

            0 - all
            1 - % of them
            2 - specific usernames

            */

            $runAuthors = array();

            $excludeAuthors = array_map('trim', explode(',', $feed['exclude_author']));					

            switch ($feed['type_author']) {
                case "0":
                    foreach ($this->userlist as $lUser) {
                        if (!in_array($lUser['user_login'], $excludeAuthors)) $runAuthors[] = $lUser['ID'];
                    }
                    break;

                case "1":
                    $usersTemp = $this->userlist;
                    shuffle ($usersTemp);
                    $cnt = round(((int)$feed['perc_author']/100)*count($this->userlist));
                    if ($cnt < 1) {
                        $cnt = 1;
                    }

                    $lcnt = 0;

                    foreach ($usersTemp as $lUser) {
                        if (in_array($lUser['user_login'], $excludeAuthors)) {
                            continue;
                        }

                        $runAuthors[] = $lUser['ID'];
                        $lcnt++;
                        if ($lcnt == $cnt) {
                            break;
                        }
                    }
                    break;

                case "2":

                    $authorsTemp = array_map('trim', explode(',', $feed['author']));
                    foreach ($this->userlist as $lUser) {
                        foreach ($authorsTemp as $lAuthor) {
                            if ($lUser['user_login'] == $lAuthor && !in_array($lUser['user_login'], $excludeAuthors)) {
                                $runAuthors[] = $lUser['ID'];
                            }
                        }
                    }

                    break;

                case "3":

                    $authorsTemp = mpc_getUsersWithRole($this->userlist, $feed['author_group']);
                    foreach ($this->userlist as $lUser) {
                        foreach ($authorsTemp as $lAuthor) {
                            if ($lUser['ID'] == $lAuthor && !in_array($lUser['user_login'], $excludeAuthors)) {
                                $runAuthors[] = $lUser['ID'];
                            }
                        } 
                    }
                    break;
            }

            $this->current_feed['original_url'] = $this->current_feed['url'];

            $authors_cnt = 0;

            // reset posting array if we are content-only //
            if (!mpc_core_installed()) {
                $runAuthors = array('1');
            }

            foreach ($runAuthors as $runAuthorId) {

                if (mpc_core_installed()) {
                    $keyword = get_option('keywords_author_'.$runAuthorId);

                    if ($this->current_feed['use_author_keywords'] && $this->current_feed['override_keywords']) {
                        $keyword = $this->current_feed['override_keywords'];
                    }

                    if (!$keyword) {
                        $keyword = $this->userlist[$runAuthorId]['display_name'];
                    }

                    if (!$keyword || !$runAuthorId) continue;

                } else {
                    $keyword = $this->current_feed['override_keywords'];
                }

///////////////////////// check feed type and if is not rss do another action //////////////

                switch ( $this->current_feed['type'] ) {
                    case 3:

                        if( !class_exists('mpco_yahoo_answers') ) {
                            require_once( mpco_plugin_dir() . '/core/yahoo_answers.php' );
                        }
                        $limit = $this->current_feed['yahoo_numitems'];
                        if( $preview_mode ) {
                            $limit = 1;
                        }

                        $get_comments = $this->current_feed['yahoo_repliesascomments'];
                        $date_range   = $this->current_feed['yahoo_range'];
                        $region = $this->current_feed['yahoo_region'];
                        $yacat  = $this->current_feed['yahoo_category'];


                        $yaans = new mpco_yahoo_answers($keyword, $limit);
                        $yaans->set_comments( $get_comments );
                        $yaans->set_region( $region );
                        $yaans->set_cat( $yacat );
                        $yaans->set_date_range($date_range);

                        $yaans->get_items( $rss, $items );

                        break;
                    case 2:
                        if( !class_exists('article_parser') ) {
                            require_once( mpco_plugin_dir() . '/article_parser.php' );
                        }
                        global $sources;

                        $limit = $this->current_feed['article_limit'];
                        if( $preview_mode ) {
                            $limit = 1;
                        }

                        $parser = new article_parser( $keyword, $this->current_feed['source'], $sources[2][$this->current_feed['source']], $limit );
                        $parser->grab( $rss, $items );

                        break;
                    case 1:
                    default:
                        $feed['url'] = preg_replace("/%keyword%/", urlencode($keyword), $feed['url']);

                        $this->current_feed['url'] = preg_replace("/%keyword%/", urlencode($keyword), $this->current_feed['original_url']);


                        // Retrieve the feed now
                        $this->mpco_grabFeed($feed, $items);
                }

///////////////////////// from here should go as it was, $items should be array of items //////////////

                // Output for manual processing
                if ($this->show_output) {
                    if( 3 == $this->current_feed['type'] ) {
                        echo '<div id="feeddiv" class="postbox "><h3>Yahoo! Answers';
                        echo '<br />&nbsp;&nbsp;'.$keyword.'</h3><div class="inside">';
                        $box_not_closed = true;
                    } else {
                        echo '<div id="feeddiv" class="postbox "><h3>'.stripslashes($rss->get_title()).'';
                        echo '<br />&nbsp;&nbsp;<a href="'.$rss->subscribe_url().'" target="_blank">'.$rss->subscribe_url().'</a></h3><div class="inside">';
                        $box_not_closed = true;
                    }
                }

                if (count($items) < 1) {
                    $this->mpco_logMsg('Feed returned no items.', 'warn');
                    if ($this->show_output) {
                        echo "</div>";
                    }
                    continue;
                }

                //--------------------------------------
                // Loop through each item in the feed
                $this->current_feed['post_count'] = 0;
                $itemid = 0;
                if ($_GET['preview']) $subtext = "<br><b>Please note, this is only rule preview, during actual posting this rule might produce much more results.</b>"; else $subtext = "";
                $this->mpco_logMsg('The rule returned '.count($items).' item(s).<br />'.$subtext, 'view');


                // Temp placeholders for [[ and ]]
                $this->current_feed['templates'] = str_replace('[[', '~~@-$', $this->current_feed['templates']);
                $this->current_feed['templates'] = str_replace(']]', '$-@~~', $this->current_feed['templates']);

                if (is_array($this->current_feed['customFields'])) {
                    foreach ($this->current_feed['customFields'] as $customField) {
                            $customField = str_replace('[[', '~~@-$', $customField);
                            $customField = str_replace(']]', '$-@~~', $customField);
                    }
                }

                if ($this->current_feed['replace']) {
                    foreach ($this->current_feed['replace'] as $pattern) {
                        $pattern = str_replace('[[', '~~@-$', $pattern);
                        $pattern = str_replace(']]', '$-@~~', $pattern);
                    }
                }

                foreach ($items as $item) {

                    $this->current_item = $item;
                    $this->postinfo = array();

                    $this->postinfo['feed_title'] = $this->current_feed['title'];

                    if ($this->show_output) {
                        echo '<div style="border-style: dotted none none none;border-width: thin;border-color: #A0A0A0;margin-bottom: 5px;margin-top: 5px;">&nbsp;</div>';
                    }

                    if (!$this->mpco_itemGetLink()) continue;
                    if (!$this->mpco_itemGetTitle()) continue;

                    $this->postinfo['title'] = $this->remove_words($this->postinfo['title']);
                    $this->postinfo['title'] = $this->spintaxing($this->postinfo['title'], 0);

                    if ($this->show_output) {
                        echo '<p style="font-size:12pt"><b><a target="_blank" href="' . $this->postinfo['link'].'">' .
                                $this->postinfo['title'] . '</a></b></p>';
                    }

                    // Check to make sure we haven't hit max_posts
                    if (($this->current_feed['post_processing'] == mpco_ITEM_MAX_POSTS) && ($this->current_feed['post_count'] >= $this->current_feed['max_posts'])) {
                            $this->mpco_logMsg('Maximum posts reached for this feed.', 'stop');
                            continue 2;
                    }

                    if (($this->current_feed['post_processing'] == mpco_ITEM_PERCENT_POSTS) && (rand(100,0) > $this->current_feed['post_perc'])) {
                            $this->mpco_logMsg('Randomly skipping '.$this->current_feed['post_perc'].'% of this feed\'s posts.', 'skip');
                            continue;
                    }

                    if (!$this->mpco_itemDupeCheck($runAuthorId)) continue;

                    if (!$this->mpco_itemGetContent()) continue;
                    if (!$this->mpco_itemFilter()) continue;
                    if (!$this->mpco_itemGetExcerpt()) continue;
                    if (!$this->mpco_itemGetDate($runAuthorId, count($items))) continue;

                    if (!mpc_core_installed() && !twoenough_is_mu()) {
                        $randomAuthors = mpc_getUsersWithRole($this->userlist, $feed['author_group']);

                        $randomActiveAuthors = array();
                        foreach ($this->userlist as $i => $lUser) {
                            foreach ($randomAuthors as $lAuthor) {
                                if ($lUser['ID'] == $lAuthor && !in_array($lUser['user_login'], $excludeAuthors)) $randomActiveAuthors[] = $i;
                            }
                        }

                        shuffle($randomActiveAuthors);

                        if (!$this->mpco_itemGetAuthor($this->userlist[$randomActiveAuthors[0]]['user_login'])) continue;					
                    } else {
                        if (!$this->mpco_itemGetAuthor($this->userlist[$runAuthorId]['user_login'])) continue;
                    }

                    if (!$this->mpco_itemGetCopyright()) continue;
                    if (!$this->mpco_itemGetSource()) continue;
                    if (!$this->mpco_itemGetAttachments()) continue;
                    if (!$this->mpco_itemGetCategoriesAndTags()) continue;
                    if (!$this->mpco_itemGetCustomFields()) continue;

                    if($this->current_feed['type'] == 3) {
                            $this->postinfo['answers'] = $this->current_item->get_answers();
                            $this->postinfo['question'] = $this->current_item->get_content();
                    }

                    $spintax_links_limit_x = -1;
                    $spintax_links_limit = $this->current_feed['override_spintax_links_limit'] ? $this->current_feed['spintax_links_limit'] : $mpco_options['spintax_links_limit_global'];
                    if($spintax_links_limit) {
                        $spintax_links_limit_x = $this->current_feed['override_spintax_links_limit'] ? $this->current_feed['spintax_links_limit_x'] : $mpco_options['spintax_links_limit_x_global'];
                    }

                    $this->postinfo['content'] = $this->remove_words($this->postinfo['content']);
                    $this->postinfo['content'] = $this->spintaxing($this->postinfo['content'], $spintax_links_limit_x);

                    $this->truncateContent();

                    $this->postinfo['post'] = $this->mpco_applyTemplate($this->current_feed['templates']);

                    $this->mpco_itemDoSearchReplace();

                    // Put back the replaced double brackets
                    $this->postinfo['post'] = str_replace('~~@-$', '[', $this->postinfo['post']);
                    $this->postinfo['post'] = str_replace('$-@~~', ']', $this->postinfo['post']);

                    if ($this->current_feed['uniqify']) $this->postinfo['post'] = $this->postinfo['post']."<!--(u)-->";

                    // Print out feed info if we are doing a visible run
                    if ($this->show_output) {
                            echo $this->postinfo['excerpt'];
                            $itemid++;
                    }

                    if (!$this->mpco_itemAddPost()) continue;

                } // foreach ($rss->items as $item)

                if ($this->show_output) echo '</div></div><br />';
                $box_not_closed = false;

                $authors_cnt++;
                if( $preview_mode && $authors_cnt > 10) break;

            }

            ////////////////// author loop end //////////

            if(twoenough_is_mu()) {
                    if($feed['mu_type'] != 0) {
                            restore_current_blog();
                    }
            }
        } // foreach ($feeds as $feed)

        if ($this->show_output) echo '</div></div><br /><br /><br /><br /><br /><br />';

        if (isset($rss)) {
            if(method_exists($rss, '__destruct')) {
                @$rss->__destruct();
            }
            unset($rss);
        }

        if (is_object($this->logger)) {
            $this->logger->flush();
            $this->logger->close();
        }

    } // end function

    protected function remove_words($text) {
        $words = explode("\n", $this->current_feed['remove_words']);
        $words = array_filter(array_map('trim', $words), 'strlen');
        
        $words_regexp = array();
        foreach($words as $key => $word) {
            $words_regexp[] = '/\b(' . $word . ')\b/si';
        }

        $text = preg_replace($words_regexp, '', $text);
        
        return $text;
    }

    protected function spintaxing($text, $links_limit = -1) {
        global $mpco_options;
        
        $spintaxes = $this->current_feed['spintaxes'] . "\n"
            . $mpco_options['spintaxes'];

        $spintaxer = new mpcoSpintaxer($spintaxes, $text, $links_limit, $this->current_feed['spintaxes_skip_itself']);
        
        return $spintaxer->getResult();
    }

     //---------------------------------------------------------------
    function mpco_checkFeedSchedule(&$feed) {
        global  $mpco_options, $wpdb;
        // action: check_schedule

        switch ($this->current_feed['schedule']) {
            case mpco_MANUAL_UPDATES:
            return false;
            break;

            case mpco_EVERY_X_UPDATES:
            if ($this->current_feed['update_eachx'] > 0) {
                // Decrement the counter
                $sql ='UPDATE ' . mpco_tableName() . ' SET `update_eachx`='.($this->current_feed['update_eachx']-1).' WHERE id='.$wpdb->escape($this->current_feed['id']).';';
                $ret = $wpdb->query($sql);
                return false;
            } else {
                // Reset the counter
                $sql ='UPDATE ' . mpco_tableName() . ' SET `update_eachx`='.$this->current_feed['updatefrequency'].' WHERE id='.$wpdb->escape($this->current_feed['id']).';';
                $ret = $wpdb->query($sql);
                return true;
            }
            break;

            default:
            // Always update this feed
            return true;
            break;
        }
    } // end function

    //---------------------------------------------------------------
    function mpco_grabFeed(&$feed, &$items) {
        global  $mpco_options, $rss;
        //action: grmpco_feed

        // Initialize SimplePie
        $rss = new SimplePie();

        // Get URL and handle variations of feed uri
        $feedurl = mpco_getFeedURL($this->current_feed['type'], $this->current_feed['url']);
        $feedurl = str_replace("feed://", "http://", $feedurl);
        $feedurl = str_replace("feed:http", "http", $feedurl);
        $rss->set_feed_url($feedurl);

        // Special handling for Yahoo! pipes if they just enter the Pipe URL itself
        if (stristr($feedurl, 'pipes.yahoo') && (!strstr($feedurl, 'rss'))) $feedurl .= '&_render=rss';

        // Cache settings
        $rss->enable_cache(true);
        $rss->set_cache_location(mpco_plugin_dir() . '/cache');
        $rss->set_cache_duration($mpco_options['rss_cache_timeout']);

        // Autodiscovery settings
        $rss->set_autodiscovery_level(SIMPLEPIE_LOCATOR_ALL);
        $rss->set_autodiscovery_cache_duration(1209600); // 2 weeks
        $rss->set_max_checked_feeds(10);

        // Other settings
        $rss->enable_order_by_date(false);
        $rss->set_useragent($mpco_options['useragent'].' (' . mt_rand().')');
        $rss->set_item_limit(50);
        $rss->set_url_replacements(array('a' => 'href', 'img' => 'src'));

        // Timeout
        if (stristr($feedurl, 'pipes')) {
            $rss->set_timeout(60);
        } else {
            $rss->set_timeout(20);
        }

        // HTML tag and attribute stripping
        $strip_htmltags = $rss->strip_htmltags;

        if (ALLOW_ALL_TAGS) {
            $strip_htmltags = array();
            $rss->strip_attributes(false);

        } else {
            if (ALLOW_OBJECT_AND_EMBED_TAGS) {
                unset($strip_htmltags[array_search('object', $strip_htmltags)]);
                unset($strip_htmltags[array_search('embed', $strip_htmltags)]);
                unset($strip_htmltags[array_search('param', $strip_htmltags)]);
            }

            if (ALLOW_FORM_TAGS) {
                unset($strip_htmltags[array_search('form', $strip_htmltags)]);
                unset($strip_htmltags[array_search('input', $strip_htmltags)]);
            }

            if (ALLOW_FRAME_TAGS) {
                unset($strip_htmltags[array_search('frame', $strip_htmltags)]);
                unset($strip_htmltags[array_search('iframe', $strip_htmltags)]);
                unset($strip_htmltags[array_search('frameset', $strip_htmltags)]);
            }

            if (ALLOW_SCRIPT_TAGS) {
                unset($strip_htmltags[array_search('class', $strip_htmltags)]);
                unset($strip_htmltags[array_search('expr', $strip_htmltags)]);
                unset($strip_htmltags[array_search('script', $strip_htmltags)]);
                unset($strip_htmltags[array_search('noscript', $strip_htmltags)]);
                unset($strip_htmltags[array_search('onclick', $strip_htmltags)]);
                unset($strip_htmltags[array_search('onerror', $strip_htmltags)]);
                unset($strip_htmltags[array_search('onfinish', $strip_htmltags)]);
                unset($strip_htmltags[array_search('onmouseover', $strip_htmltags)]);
                unset($strip_htmltags[array_search('onmouseout', $strip_htmltags)]);
                unset($strip_htmltags[array_search('onfocus', $strip_htmltags)]);
                unset($strip_htmltags[array_search('onblur', $strip_htmltags)]);
            }
        }

        $strip_htmltags = array_values($strip_htmltags);
        $rss->strip_htmltags($strip_htmltags);


        if (ENCODE_INSTEAD_OF_STRIP) {
            $rss->encode_instead_of_strip(true);
        }

        // Force feed handling with unrecognized or malformed feeds
        if (FORCE_FEED) {
            $rss->force_feed(true);
        }


        // Retrieve the feed
        $rss->init();


        // Handle errors
        if ($rss->error()) {
            $this->mpco_logMsg('Error occurred retrieving feed.', 'stop');
            // Special handling for urls that aren't really feeds
            if (stristr($rss->error(), 'syntax error at line')) {
                $this->mpco_logMsg('Error occurred retrieving feed or feed is invalid.<br />Feed URI: '.$rss->subscribe_url(), 'stop');
            } else {
                $this->mpco_logMsg('Error occurred processing rule: '.$rss->error(), 'stop');
            }
            return false;
        }

        // Grab the feed items
        $items = $rss->get_items();

        if(!$this->current_feed['source']) {
            return true; 
        }
        foreach ($items as $key => $item) {
            $this->grabFull($item);
        }
        return true;
    }

    protected function getExtractor() {
        static $extractor;

        require_once(mpco_plugin_dir() . '/lib/content-extractor/ContentExtractor.php');
        require_once(mpco_plugin_dir() . '/lib/content-extractor/SiteConfig.php');
        require_once(mpco_plugin_dir() . '/lib/readability/Readability.php');

        if(!$extractor) {
            $extractor = new ContentExtractor(mpco_plugin_dir() . '/lib/content-extractor/config/custom', mpco_plugin_dir() . '/lib/content-extractor/config/standard');
        }

        return $extractor;
    }
		
    protected function grabFull(&$item) {
        $extractor = $this->getExtractor();
        $permalink = htmlspecialchars_decode($item->get_permalink());
        // Colons in URL path segments get encoded by SimplePie, yet some sites expect them unencoded
        $permalink = str_replace('%3A', ':', $permalink);

        $html = twoenoughWpGet($permalink);

        if(!$html) return;

        $effective_url = $permalink;

        $extract_result = $extractor->process($html, $effective_url);
        $readability = $extractor->readability;
        $content_block = ($extract_result) ? @$extractor->getContent() : null;
        $title = ($extract_result) ? $extractor->getTitle() : '';

        if(!$extract_result) return;;

        $readability->clean($content_block, 'select');

        //if ($options->rewrite_relative_urls) makeAbsolute($effective_url, $content_block);

        if ($content_block->childNodes->length == 1 && $content_block->firstChild->nodeType === XML_ELEMENT_NODE) {
            $html = $content_block->firstChild->innerHTML;
        } else {
            $html = $content_block->innerHTML;
        }
        // post-processing cleanup
        $html = preg_replace('!<p>[\s\h\v]*</p>!u', '', $html);

        /**
         * @TODO: add option to remove links. so there will be something like "if($this->current_feed['strip_links']) { ... } 
         */
        /* if ($links == 'remove') {
            $html = preg_replace('!</?a[^>]*>!', '', $html);
        }*/

        if($this->current_feed['yahoo_numitems']) {
            $html = strip_tags($html);
        }

        $item->set_content($html);
    }
		
    //---------------------------------------------------------------
    function mpco_itemGetLink() {
        global $mpco_options;
        // action: filter_link
        $link = urldecode($this->current_item->get_link());

        // Skip if the link is empty
        if (empty($link)) {
            $this->mpco_logMsg('Skipping post with empty link.', 'skip');
            return false;
        }

        // Skip the blog's own domain
        if (stristr($link, $this->own_domain)) {
            $this->mpco_logMsg('Skipping post from own domain.', 'skip');
            return false;
        }

        // Check for blacklisted domains and url sequences in the link
        if (is_array($this->exclude_domains)) {
            foreach ($this->exclude_domains as $domain) {
                if (stristr($link, $domain)) {
                    $this->mpco_logMsg('Skipping post with blacklisted domain or URL sequence: "'.$domain.'"', 'skip');
                    return false;
                }
            } // end for
        }
        $this->postinfo['link'] = $link;

        // filter: link
        return true;
    }

    //---------------------------------------------------------------
    function mpco_itemGetTitle() {
        global  $mpco_options;
        // action: get title

        $title = strip_tags($this->current_item->get_title());

        // Check for multiple punctuation marks
        if ($mpco_options['skipmultiplepunctuation'] && preg_match("/[!$%&*?]{2,}/", $title)) {
            $this->mpco_logMsg('Skipping post with multiple punctuation marks in title.', 'skip');
            return false;
        }

        // Check for all-caps titles
        if ($mpco_options['skipcaps'] && $title == strtoupper($title)) {
            $this->mpco_logMsg('Skipping post in all caps.', 'skip');
            return false;
        }

        // Title filtering
        If (strlen($title) > $mpco_options['maxtitlelen']) {
            if ($mpco_options['longtitlehandling'] == mpco_TITLE_TRUNCATE) {
                // Truncate
                $lines = explode("\n", wordwrap($title, $mpco_options['maxtitlelen'], "\n", true));
                $title = $lines[0].'...';
            } else {
                // Skip
                $this->mpco_logMsg('Skipping post with long title.', 'skip');
                return false;
            }
        }

        $this->postinfo['title'] = $title;
        // filter: title
        return true;
    }

    //---------------------------------------------------------------
    function mpco_itemDupeCheck($authorId = "") {
        global  $mpco_options, $wpdb;
        //action:  Dupe check

        if (!$mpco_options['uniques_per_author']) $authorId = null;

        // Check for duplicate title
        $titledupesfound = false;
        $wpdb->flush;
        if ($mpco_options['filterbytitle'] == true) {
            $checktitle = mysql_escape_string($this->postinfo['title']);

            if (!$authorId) $sql = "SELECT ID FROM $wpdb->posts WHERE post_name = '" . sanitize_title_with_dashes($this->postinfo['title']). "' OR post_title = '".mysql_escape_string($checktitle)."'";
            else $sql = "SELECT ID FROM $wpdb->posts WHERE (post_name = '" . sanitize_title_with_dashes($this->postinfo['title']). "' OR post_title = '".mysql_escape_string($checktitle)."') AND `post_author` = '$authorId'";

            $titledupesfound = $wpdb->query($sql);


            if ($titledupesfound === false) {
                $this->mpco_logMsg('Error connecting to database to check for duplicate titles.', 'stop');
                return false;
            }


            if ($titledupesfound > 0) {
                $this->mpco_logMsg('This item already exists on the blog.', 'skip');
                return false;
            } else {
                // Secondary check
                if (!$authorId) $post_name_check = $wpdb->get_var($wpdb->prepare("SELECT post_name FROM $wpdb->posts WHERE post_name = %s LIMIT 1", sanitize_title($this->postinfo['title'])));
                else $post_name_check = $wpdb->get_var($wpdb->prepare("SELECT post_name FROM $wpdb->posts WHERE post_name = %s AND `post_author` = '$authorId' LIMIT 1", sanitize_title($this->postinfo['title'])));					
                if ($post_name_check) {
                    $this->mpco_logMsg('This item already exists on the blog.', 'skip');
                    return false;
                }
            }
        }

        // Check for dupe link
        $linkdupesfound = false;
        if ($mpco_options['filterbylink'] == true) {

            $sql = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'link' AND meta_value = '" . addslashes($this->postinfo['link']) . "'";

            $linkdupesfound = $wpdb->get_row($sql);

            if ($linkdupesfound === false) {
                $this->mpco_logMsg('Error connecting to database to check for duplicate links.', 'stop');
                return false;
            }

            if ($linkdupesfound->post_id > 0) {
                if ($authorId) {
                    $sql = "SELECT ID FROM $wpdb->posts WHERE (ID = '".$linkdupesfound->post_id."' AND `post_author` = '$authorId')";	

                    $linkcheck = $wpdb->get_row($sql);

                    if ($linkcheck->ID > 0) {
                        $this->mpco_logMsg('This item already exists on the blog.', 'skip'); return false;
                    } else {
                        if ($_GET['preview']) {
                            $this->mpco_logMsg('This item will be posted', 'added');
                        }
                        return true;
                    }						
                } else {
                    $this->mpco_logMsg('This item already exists on the blog.', 'skip');
                    return false;
                }
            }
        }

        // Otherwise, let the post through
        if ($_GET['preview']) $this->mpco_logMsg('This item will be posted<br><br>', 'added');			
        return true;
    }

    //---------------------------------------------------------------
    function mpco_itemGetContent() {
        global  $mpco_options;
        $page_content = '';

        //action: get content

        // Get the content of the feed item
        $content = mpco_fuck_bad_symbols($this->current_item->get_content());


        if (empty($content)) {
            $content = $this->current_item->get_description();
        }

        // Handle encoded content
        if (!ENCODE_INSTEAD_OF_STRIP) {
            $content = html_entity_decode($content);
        }

        $this->postinfo['content'] = $content;

        $this->postinfo['description'] = $this->current_item->get_description();


        // We only need to grab the original page if we are going to
        // be getting tags from it
        if ($mpco_options['posttags'] == true) {
            $result = mpco_httpFetch($this->postinfo['link']);

            if (strlen($result['error'])) {
                // Warn but don't stop
                $this->mpco_logMsg('Unable to retrieve the original post: '. $result['error'], 'warn');
            }

            if ($result['http_code'] >= 400) {
                // Warn but don't stop
                $this->mpco_logMsg('Cannot retrieve URL to get tags: '.$this->postinfo['link'].' ('.$result['http_code'].')', 'warn');
            }

            // Fall back to using the content from the feed
            if (strlen($result['contents'])) {
                $page_content = $result['contents'];
            } else {
                $page_content = htmlentities2($this->current_item->get_content());
            }
        }


        // filter: content
        return true;
    }

    //---------------------------------------------------------------
    function mpco_itemFilter() {
        global $mpco_options;
        // action: filter item

        // Check for globally blacklisted words

        foreach ($this->exclude_words as $word) {
            if (preg_match('/\b' . $word . '\b/i', $this->postinfo['content'] . ' ' . $this->postinfo['title'], $matches)) {
                $this->mpco_logMsg('Skipping post with blacklisted word: ' . $word, 'skip');
                return false;
            }
        }

        // Perform per-feed filtering
        $filterpass = true;

        // None of these words
        if (strlen($this->current_feed['includenowords'])) {
            $filterpass = (mpco_countItemsFound($this->postinfo['content'].' '.$this->postinfo['title'], $this->current_feed['nowords']) == 0);
            if (!$filterpass) {
                $this->mpco_logMsg('Skipping post due to "None of these words" filter.', 'skip');
                return false;
            }
        }

        // All of these words
        if (strlen($this->current_feed['includeallwords']) && $filterpass = true) {

            $filterpass = (mpco_countUniqueItemsFound($this->postinfo['content'].' '.$this->postinfo['title'], $this->current_feed['allwords']) >= count($this->current_feed['allwords']));
            if (!$filterpass) {
                $this->mpco_logMsg('Skipping post due to "All of these words" filter.', 'skip');
                return false;
            }
        }

        // Any of these words
        if (strlen($this->current_feed['includeanywords']) && $filterpass = true) {
            $filterpass = (mpco_countItemsFound($this->postinfo['content'].' '.$this->postinfo['title'], $this->current_feed['anywords']) > 0);
            if (!$filterpass) {
                $this->mpco_logMsg('Skipping post due to "Any of these words" filter.', 'skip');
                return false;
            }
        }

        // The exact phrase
        if (strlen($this->current_feed['includephrase']) && $filterpass = true) {
            $filterpass = (mpco_countItemsFound($this->postinfo['content'].' '.$this->postinfo['title'], $this->current_feed['phrase']) > 0);
            if (!$filterpass) {
                $this->mpco_logMsg('Skipping post due to "Exact phrase" filter.', 'skip');
                return false;
            }
        }

        // We passed all filters
        return true;
    }

    //---------------------------------------------------------------
    function mpco_itemGetExcerpt() {
        global  $mpco_options;

        // action: Get excerpt

        // Make a text-only excerpt from the description
        $excerpt_delim = array('/([\s,-;:]+)/', '/([\.\?]\s)/', '/\r\n/');
        $content = $this->postinfo['content'];



        //cleanup
        $content = str_replace('>', '> ', $content);
        $content = strip_tags($content);

        $content = str_replace('[...]', '', $content);
        $content = preg_replace('/\\s+/',' ', $content);

        if (strlen($content)) {
            $words = preg_split($excerpt_delim[$mpco_options['excerpt_type']], $content, -1, PREG_SPLIT_DELIM_CAPTURE+PREG_SPLIT_NO_EMPTY);
            $wordcount = count($words);
            $words = array_slice($words, 0, rand($mpco_options['minexcerptlen']*2, $mpco_options['maxexcerptlen']*2)); //doubled because we are capturing delims
            $excerpt = implode($excerpt_delim[$mpco_options['excerpt_type']], $words);

            if ($mpco_options['excerpt_type'] == 0 && $wordcount > $mpco_options['maxexcerptlen']*2) {
                $words[] = '...';
            }

            $excerpt = implode('', $words);
            $this->postinfo['excerpt'] = $excerpt;
        }
        // filter: the excerpt
        return true;
    }

    //---------------------------------------------------------------
    function mpco_itemGetDate($author = '', $items = 0) {

        if ($this->current_feed['yahoo_backdate'] && method_exists($this->current_item, 'get_timestamp')) {
            // Date
            $date = $this->current_item->get_timestamp();
            $date = date('Y-m-d H:i:s', (int) $date);
            $this->postinfo['date']     = $date;
            $this->postinfo['date_gmt'] = $date;
        } else {
            $time = time();
            if ($this->current_feed['scheduleposts'] && $this->current_feed['scheduleposts_range']) {
                $range = $this->current_feed['scheduleposts_range'] * 3600;
                $range = round($range / $items);
                    if (!isset($this->current_feed['scheduleposts_counter_'.$author])) {
                        $this->current_feed['scheduleposts_counter_'.$author] = 0;
                    } else {
                        $this->current_feed['scheduleposts_counter_'.$author]++;
                        $time = $time + $this->current_feed['scheduleposts_counter_'.$author]*$range+rand(-300,300);
                    }

                $date = date('Y-m-d H:i:s', $time);
                $this->postinfo['date'] = $date;
            }

        }
        return true;
    }

    //---------------------------------------------------------------
    function mpco_itemGetAuthor($user_login = "") {
        global  $mpco_options, $wpdb;

        $user = $this->mpco_findAuthor($user_login);
        $this->postinfo['author_id'] = $user['ID'];
        $this->postinfo['author_display_name'] = $user['display_name'];
        $this->postinfo['author'] = $user['display_name'];
        $this->postinfo['author_email'] = $user['user_email'];

        $this->postinfo['author_url'] = $user['user_url'];

        $this->postinfo['author_bio'] = $user['user_description'];

        return true;
    }

    //---------------------------------------------------------------
    function mpco_findAuthor($login = null, $email = null, $uri = null) {
        global $wpdb;

        // If all parameters are empty, return a random author
        if (empty($login) && empty($email) && empty($uri)) {
            $sql = "SELECT * FROM $wpdb->users ORDER BY rand() LIMIT 1";
        } else {

            $sql = "SELECT * FROM $wpdb->users WHERE ";

            if (!empty($login)) {
                $where = " `user_login` = '$login' OR `user_nicename` = '$login' OR `display_name` = '$login' ";
            }

            if (!empty($email)) {
                if (!empty($where)) $where .= 'OR ';
                $where .= "`user_email` - '$email' ";
            }

            if (!empty($uri)) {
                if (!empty($where)) $where .= 'OR ';
                $where .= "`user_url` = '$uri' ";
            }
        }

        // Execute the query
        $user = $wpdb->get_row($sql.$where, ARRAY_A);
        if (empty($user)) {
            return false;
        } else {
            return $user;
        }
    }

    //---------------------------------------------------------------
    function mpco_itemGetCopyright() {
        // Copyright
        $copyright = $this->current_item->get_copyright();
        if (is_object($copyright)) {
            $this->postinfo['copyright'] = $copyright->get_attribution();
            $this->postinfo['copyright_url'] = $copyright->get_url();
        }
        return true;
    }

    //---------------------------------------------------------------
    function mpco_itemGetSource() {
        global $rss;
        $this->postinfo['source'] = $rss->get_title();
        $this->postinfo['source_url'] = $rss->get_permalink();
        $this->postinfo['source_description'] = $rss->get_description();

        $this->postinfo['icon'] = $rss->get_favicon();
        $this->postinfo['logo_url'] = $rss->get_image_url();
        $this->postinfo['logo_link'] = $rss->get_image_link();
        $this->postinfo['logo_title'] = $rss->get_image_title();

        // Pull extra info from blogroll if that option is selected
        if ($this->current_feed['uselinkinfo']) {
            foreach ($this->bookmarks as $bookmark) {

                if (stristr($this->postinfo['link'], str_replace("http://", "", $bookmark->link_url))) {
                    if (strlen($bookmark->link_url) > strlen($linkmatch->link_url)) $linkmatch = $bookmark;
                }
                if (stristr(str_replace("http://", "", $bookmark->link_url), str_replace("http://", "", $this->postinfo['link']))) {
                    if (strlen($bookmark->link_url) > strlen($linkmatch->link_url)) $linkmatch = $bookmark;
                }
            }

            if ($linkmatch) {
                $this->postinfo['source_url'] = $this->postinfo['link'];
                $this->postinfo['source'] = $linkmatch->link_name;
                $this->postinfo['logo_url'] = $linkmatch->link_image;
                $this->postinfo['source_description'] = $linkmatch->link_description;
            }
        }
        return true;
    }

    //---------------------------------------------------------------
    function mpco_itemGetAttachments() {
        global  $mpco_options;
        // Images and video
        $enclosures = $this->current_item->get_enclosures();
        $image_urls = array();
        $enclosure_tags = array();
        $this->postinfo['attachments'] = array();



        // get images from all fields
        require_once ABSPATH.'/wp-admin/includes/image.php';
        foreach (array_keys($this->postinfo) as $field) {
            if (is_array($this->postinfo[$field])) {
                preg_match_all('%http://[^"<:]{5,255}\.(?:jpg|jpeg|gif|png)%', htmlspecialchars_decode(implode(' ', $this->postinfo[$field])), $extractedimageurls);
            } else {
                preg_match_all('%http://[^"<:]{5,255}\.(?:jpg|jpeg|gif|png)%', htmlspecialchars_decode($this->postinfo[$field]), $extractedimageurls);
            }

            if (is_array($extractedimageurls)) $image_urls = array_merge($image_urls, $extractedimageurls[0]);
        }

        // Add any media:thumbnail elements
        $elements = $this->current_item->get_item_tags('http://search.yahoo.com/mrss/', 'group');
        $thumbnails = $elements[0]['child']['http://search.yahoo.com/mrss/']['thumbnail'];
        if (is_array($thumbnails)) {
            foreach($thumbnails as $thumbnail) {
                $media_thumbnails[] = $thumbnail['attribs']['']['url'];
            }
        }

        if (is_array($media_thumbnails)) $image_urls = array_merge($image_urls, $media_thumbnails);

        if (is_array($enclosures)) {
            $j=0;
            foreach ($enclosures as $enclosure) {
                $j++;
                //Get additional tags from each enclosure
                if ($mpco_options['feedtags']) {
                    $kw = $enclosure->get_keywords();
                    if (is_array($kw)) $enclosure_tags =  array_merge($enclosure_tags, $kw);
                    $enc_cats = $enclosure->get_categories();
                    if (is_array($enc_cats)) {
                        foreach ($enc_cats as $enc_cat) {
                            $enclosure_tags[] = $enc_cat->get_label();
                        }
                    }
                    if (is_array($enclosure_tags)) array_unique($enclosure_tags);
                }
                $enc_link = $enclosure->get_link();
                $enc_type = $enclosure->get_type();

                if (stristr($enc_type, "image")) {
                    $image_urls[] = $enc_link;
                } else {
                    $vid_embed = mpco_getEmbeddedVideo($enc_link, $this->current_feed['playerwidth'], $this->current_feed['playerheight'], $enclosure->get_handler());
                    if ($j==1) $this->postinfo['video'] = $vid_embed.' ';

                    $this->postinfo['videos'][] = $vid_embed;
                    if (!empty($enc_link)) $this->postinfo['video_urls'][] = $enc_link;
                }
                $this->postinfo['video_url'] = $this->postinfo['video_urls'][0];
            }
        }

        // Add image attachments if there are any
        if (is_array($image_urls)) {
            $image_urls = array_unique($image_urls);
            foreach ($image_urls as $image) {

                // Skip these images
                if (stristr($image, 'icn_star')) continue; // YouTube star icon
                if ($image == $this->postinfo['logo_url']) continue; // Skip the feed's logo image
                if (strlen($image) > 255) continue;  // Very long image paths

                $attachment_info = array();

                // Only need to do this if we are saving images or creating thumbs
                if ($this->current_feed['saveimages'] || $this->current_feed['createthumbs']) {

                    $upload = array();

                    // First check to see if we already have the image cached
                    $imageurl = parse_url($image);
                    $pathinfo = pathinfo($imageurl['path']);
                    $filehash = substr(md5($image), -10).sanitize_file_name(substr(basename($imageurl['path']),-10)).'.'.$pathinfo['extension'];  // This should be unique enough for our purposes

                    if (file_exists($this->upload_dir.'/'.$filehash)) {
                        $the_url = $this->upload_url.'/'.$filehash;
                        $the_file = $this->upload_dir.'/'.$filehash;
                        $this->postinfo['content'] = str_replace($image, $the_url, $this->postinfo['content']);

                    } else {

                        // Grab the original image
                        unset($upload);
                        $upload = mpco_httpFetch($image);

                        // Make sure we actually got something
                        if ($upload['headers']['status'] >= 400) {
                            $this->mpco_logMsg('Unable to retrieve image ('.$upload['headers']['status'].'): '. $image, 'warn');
                            continue;
                        }

                        // Special handling for blogger.com, blogspot.com, wikipedia.com, etc.
                        if (stristr($upload['headers']['content-type'], 'text')) {

                            if (preg_match('/<img[^>]*src="([^"]*)"/i', $upload['content'], $matches)) {

                                // If we found an image in the text, try it again
                                $urlParsed = parse_url($matches[1]);
                                $upload = mpco_httpFetch($matches[1], $urlParsed['host']);
                            } else {
                                $this->mpco_logMsg('Server did not return a valid image for the URL '.$image, 'warn');
                                continue;
                            }
                        }

                        // Check again to make sure we are dealing with an image
                        if (!empty($upload['headers']['content-type']) && !stristr($upload['headers']['content-type'], 'image')) {
                            // Additional check of the actual content
                            $header = substr($upload['content'], 0,10);
                            if (stristr($header, 'GIF8')==0 && stristr($header, 'PNG')==0 && stristr($header, 'JFIF')==0) {
                                $this->mpco_logMsg('Server did not return valid image type ('.$upload['headers']['content-type'].') for '.$image, 'warn');
                            }
                            continue;
                        }

                        $content_type = $upload['headers']['content-type'];

                        // Save the image locally
                        //   Create an empty placeholder file in the upload dir
                        //   returns array with 'file', 'url', and 'error'
                        $result = wp_upload_bits($filehash, 0, '');

                        if ($result['error']) {
                            $this->mpco_logMsg('Unable to write to upload directory: '.$result['error'], 'warn');
                            $this->postinfo['error'] .= "Unable to write to upload directory.\r\n";
                            $the_url = $image;
                            continue;
                        }

                        // Create a handle to the destination file
                        $fp = @fopen($result['file'], 'w');
                        if (!$fp) {
                            $this->mpco_logMsg('Unable to save image to upload directory.', 'warn');
                            $this->postinfo['error'] .= "Unable to save image to upload directory.\r\n";
                            $the_url = $image;
                            continue;
                        }

                        // Write the file
                        fwrite($fp, $upload['content']);
                        @fclose($fp);


                        if ($this->current_feed['saveimages']) {

                            $the_url = $result['url'];
                            $this->postinfo['content'] = str_replace($image, $result['url'], $this->postinfo['content']);
                            $attachment_info = array();
                            $attachment_info['post_title'] = 'Image '. sanitize_file_name(basename($imageurl['path']));
                            $attachment_info['post_content'] = '';
                            $attachment_info['post_status'] = $this->current_feed['poststatus'];
                            $attachment_info['post_mime_type'] = $content_type;
                            $this->postinfo['attachments'][$result['file']] = $attachment_info;

                        } else {

                            $the_url = $image;
                        }
                        $the_file = $result['file'];
                    }

                    //$this->postinfo['images'][] = '<img src="'.$the_url.'" />';
                    $this->postinfo['images'][] = $the_url;
                    $this->postinfo['image_urls'][] = $the_url;

                    $parse_url = parse_url($the_url);
                    $this->postinfo['image_paths'][] = $parse_url['path'];

                    // Now create a thumbnail for it and get the thumbnail's path
                    if ($this->current_feed['createthumbs']) {
                        $thumbpath = image_resize($the_file, get_option('thumbnail_size_w'), get_option('thumbnail_size_h'));
                        if ($thumbpath) {
                            if (is_string($thumbpath)) {								
                                $postdata['guid'] = str_replace(basename($the_file), basename($thumbpath), $result['url']);
                                $attachment_info = array();
                                $attachment_info['post_title'] = 'Thumbnail';
                                $attachment_info['post_content'] = '';
                                $attachment_info['post_status'] = $this->current_feed['poststatus'];
                                $attachment_info['post_mime_type'] = $content_type;
                                $this->postinfo['attachments'][$thumbpath] = $attachment_info;

                                // Kill the original file if the option is not set to save
                                if (!$this->current_feed['saveimages']) @unlink($the_file);

                            } else {

                                // use the image itself as the url if we have an error here
                                $thumbpath = $the_url;
                                $postdata['guid'] = $the_url;
                            }

                        } else {

                            // The image is small enough to be its own thumbnail
                            $thumbpath = $the_url;
                            $postdata['guid'] = $the_url;
                        }
                        $this->postinfo['thumbnails'][] = '<img src="'.$postdata['guid'].'" />';
                        $this->postinfo['thumbnail_urls'][] = $postdata['guid'];

                        $url_parsed = parse_url($thumbpath);
                        $this->postinfo['thumbnail_paths'][] = stristr($url_parsed['path'], '/wp-content');
                    }			

                } else {

                    //$this->postinfo['images'][] = '<img src="'.$image.'" />';;
                    $this->postinfo['images'][] = $image;
                    $this->postinfo['image_urls'][] = $image;

                }
            }  // foreach ($image_urls as $image)

            $this->postinfo['image'] = $this->postinfo['images'][0];
            $this->postinfo['image_path'] = $this->postinfo['image_paths'][0];
            $this->postinfo['image_url'] = $this->postinfo['image_urls'][0];
            $this->postinfo['thumbnail'] = $this->postinfo['thumbnails'][0];
            $this->postinfo['thumbnail_path'] = $this->postinfo['thumbnail_paths'][0];
            $this->postinfo['thumbnail_url'] = $this->postinfo['thumbnail_urls'][0];

        }
        return true;
    }
		
    //---------------------------------------------------------------
    function mpco_itemGetCategoriesAndTags() {
        global  $mpco_options, $notags;

        // Clear out any keywords and tags from previous item
        $feed_tags = array();
        $original_post_tags = array();
        $more_categories = array();
        $keywords = array();
        $enclosure_tags = array();
        $this->current_feed['tags_list'] = array();

        // Grab tags from feed
        if (count($this->current_item->get_categories()) > 0) {
            if ($mpco_options['feedtags']) {
                foreach ($this->current_item->get_categories() as $cat) {
                    $feed_tags[] = $cat->get_label();
                }
            }
        }

        // Add categories from original source
        $original_categories = array();
        if ($this->current_feed['usepostcats'] == 1) {
            $source_cats = $this->current_item->get_categories();
            if (count($source_cats)) {
                foreach ($source_cats as $category) {
                    if (strlen($category->get_label()) < $mpco_options['maxtaglen']) {
                        if ($this->current_feed['addpostcats'] == 0) {
                            if (is_term($category->get_label(), 'category') == 0) continue;
                        }
                        $original_categories[] = $category->get_label();
                    }
                }
            }
        }

        // Add all or random categories set by user
        $newcategories = array();
        $feedcategory_ids =  mpco_unserialize($this->current_feed['category']);
        if (is_array($feedcategory_ids)) {
            shuffle($feedcategory_ids);
        } else {
            $feedcategory_ids[0] = get_option('default_category');
        }

        $newcategories[] = get_term_field('name', $feedcategory_ids[0], 'category');
        if (count($feedcategory_ids) > 0) {
            for ($i = 1; $i <= count($feedcategory_ids)-1; $i++) {
                if ($this->current_feed['randomcats'] == 0 || (rand(0,2) == 0)) {
                    $newcategories[] = get_term_field('name', $feedcategory_ids[$i], 'category');
                }
            }
        }

        // Add blog categories as tags or additional categories if they exist in the post
        if (($this->current_feed['addcatsastags'] == true) || ($this->current_feed['addothercats'] == true)) {
            $more_categories = array();

            foreach ($this->categories as $cat) {
                if ($cat->name) {
                    if ((stristr($this->postinfo['page_content'],$cat->name)) || (stristr($this->postinfo['content'],$cat->name))) {
                        if ($this->current_feed['addcatsastags'] == true) {
                            $feed_tags[] = $cat->name;
                        }
                        if ($this->current_feed['addothercats'] == true) {
                            $more_categories[] = $cat->name;
                        }
                    }
                }
            } // end foreach
        }

        // Put them all together
        $this->current_feed['feedcategories'] = array_merge($original_categories, $newcategories, $more_categories);

        // Temporary hack
        $object_item = array('Object');
        $this->current_feed['feedcategories'] = array_diff($this->current_feed['feedcategories'], $object_item);

        // randomly add additional tags from global and per-feed lists
        $num = rand(0, min((count($this->global_extra_tags) + count($this->current_feed['feed_extra_tags'])/2), 4));
        for ($i = 0; $i <= $num; $i++) {
            if (is_array($this->global_extra_tags)) $feed_tags[] = $this->global_extra_tags[array_rand($this->global_extra_tags)];
        }
        for ($i = 0; $i <= $num; $i++) {
            if ($this->current_feed['feed_extra_tags']) $feed_tags[] = $this->current_feed['feed_extra_tags'][array_rand($this->current_feed['feed_extra_tags'], 1)];
        }

        $feed_tags = array_unique($feed_tags);

        // Add tags based on the original post
        if ($mpco_options['taggingengine']) {			
            $original_post_tags = $this->mpco_getKeywords($this->postinfo['content']);
        }

        if (count($original_post_tags) > 0) $feed_tags = array_merge($feed_tags, $original_post_tags, $enclosure_tags);
        if (count($yahootags) > 0) $feed_tags = array_merge($feed_tags, $yahootags);

        $notags = array();

        // Clean up the tags
        if (is_array($feed_tags)) {
            foreach ($feed_tags as $post_tag) {
                $flagged = false;
                $i = 0;
                if (in_array($post_tag, $this->filtered_tags)) {
                    continue;
                } else {
                        if (strlen($post_tag) < $mpco_options['mintaglen']) {
                            $flagged = true;
                            continue;
                        }
                        if (strlen($post_tag) > $mpco_options['maxtaglen']) {
                            $flagged = true;
                            continue;
                        }
                    foreach ($notags as $pattern) {
                        $i++;
                        if (preg_match('/' . $pattern . '/ism', $post_tag)) {
                            $flagged = true;
                            continue 2; 
                        }
                    } // end foreach
                } // end if

                if ($flagged == false) {
                    $this->current_feed['tags_list'][] = strtolower($post_tag);
                }
            } // end foreach
        }

        if (is_array($this->current_feed['tags_list'])) {
            shuffle($this->current_feed['tags_list']);
            $this->current_feed['tags_list'] = array_slice($this->current_feed['tags_list'], 0, $mpco_options['maxtags'] - rand(0, $mpco_options['maxtags']/2));
        }
        return true;
    }
		
    //---------------------------------------------------------------
    function mpco_itemGetCustomFields() {
        // Custom Fields
        $this->customfields = array();
        if (is_array($this->current_feed['customFields'])) {
            foreach (array_keys($this->current_feed['customFields']) as $fieldItem) {
                $this->customfields[$fieldItem] = $this->mpco_applyTemplate($this->current_feed['customFields'][$fieldItem]);
            }
        }
        return true;
    }

    //---------------------------------------------------------------
    function mpco_itemDoSearchReplace() {
        // Search and replace
        if ($this->current_feed['search']) {
            foreach (array_keys($this->postinfo) as $postfield) {
                $i=0;
                foreach ($this->current_feed['search'] as $pattern) {
                    $ret = @preg_replace('/'.stripslashes($pattern).'/i', $this->current_feed['replace'][$i], $this->postinfo[$postfield]);
                    if ($ret) {
                        if (is_array($ret)) {
                            foreach ($ret as $retitem) {
                                $retitem = $this->mpco_applyTemplate($retitem);
                            }
                        } else {
                            $ret = $this->mpco_applyTemplate($ret);
                        }
                        $this->postinfo[$postfield] = $ret;
                    }
                    $i++;
                }
            }
        }
        return true;
    }

    function mpco_insertcomments($postid, $comments, $backdate = 1) {

        $cnum = count($comments);

        $i = 0;
        foreach ($comments as $comment) {
            if($i >= $cnum) {
                continue;
            }
            $comment_post_ID=$postid;

            if( 1 == $backdate ) {
                $comment_date = date("Y-m-d H:i:s", (int)$comment['timestamp']);
                $comment_date_gmt = $comment_date;
            } else {
                $comment_date = current_time('mysql');
                list( $today_year, $today_month, $today_day, $hour, $minute, $second ) = split( '([^0-9])', $comment_date );
                $comment_date = mktime($hour, $minute + rand(0, 59), $second + rand(0, 59), $today_month, $today_day, $today_year);
                $comment_date=date("Y-m-d H:i:s", $comment_date);
                $comment_date_gmt = $comment_date;
            }

            $rnd = rand(1,9999);
            $comment_author_email="someone$rnd@domain.com";
            $comment_author=$comment["author"];
            $comment_author_url=mpc_process_textlinks("http://", 'comment-author');  
            $comment_content="";

            $comment_content.=$comment["content"];

            $comment_content = mpc_process_textlinks($comment_content, 'comment-text');

            $comment_type='';
            $user_ID='';
            $comment_approved = 1;
            $commentdata = compact('comment_post_ID', 'comment_date', 'comment_date_gmt', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'user_ID', 'comment_approved');
            $comment_id = wp_insert_comment( $commentdata );
            $i++;
        }

    }
		
		
    //---------------------------------------------------------------
    function mpco_itemAddPost() {

        // Override all fields with any custom fields set by the user
        if (is_array($this->customfields)) {
            foreach (array_keys($this->customfields) as $field) {
                $this->postinfo[$field] = $this->customfields[$field];
            }
        }

        // Add fields to post array
        $post = array();

        $post['post_content'] =  $this->postinfo['post'];
        $post['post_title'] = $this->postinfo['title'];
        $post['post_excerpt'] = $this->postinfo['excerpt'];
        $post['post_date'] = $this->postinfo['date'];
        $post['post_status'] = $this->current_feed['poststatus'];

        // Set the author
        $post['post_author'] = $this->postinfo['author_id'];

        foreach (array_keys($this->postinfo) as $postfield) {
            if (!strlen($postfield)||
                    !stristr('post_author|post_date|post_date_gmt|post_content|' .
                    'post_title|post_category|post_excerpt|post_status|' .
                    'comment_status|ping_status|post_password|post_name|' .
                    'to_ping|pinged|post_modified|post_modified_gmt|' .
                    'post_content_filtered|post_parent|guid|menu_order|' .
                    'post_type|post_mime_type', $postfield)
                    || $postfield == 'author') {
                continue;
            }
            $post[$postfield] = $this->postinfo[$postfield];
        }

        //---------------------------------------------------------------------
        // Customization for specific themes

        $theme = get_current_theme();	
        switch ($theme) {

            // === Colorlabs Project
            case 'Arthemia Premium':
                if (!empty($this->postinfo['image_path'])) {
                    if (stristr($this->postinfo['image_path'], 'wp-content')) {
                        $this->postinfo['Image'] = $this->postinfo['image_path'];
                        $this->postinfo['image'] = $this->postinfo['image_path'];
                    }
                }
                break;

            // === WPThemesmarket
            case 'MagazineNews':
                $this->postinfo['image'] = $this->postinfo['image_url'];
                break;

            // === WooThemes
            case 'Ambience':
            case 'BlogTheme':
            case 'Busy Bee':
            case 'Flash News':
            case 'Fresh Folio':
            case 'Fresh News':
            case 'Gazette Edition':
            case 'Geometric':
            case 'Gotham News':
            case 'Live Wire':
            case 'NewsPress':
            case 'OpenAir':
            case 'Over Easy':
            case 'Papercut':
            case 'Original Premium News':
            case 'ProudFolio':
            case 'Snapshot':
            case 'THiCK':
            case 'Typebased':
            case 'Vibrant CMS':

                $this->postinfo['image'] = $this->postinfo['image_url'];
                $this->postinfo['preview'] = $this->postinfo['image_url'];

                $this->postinfo['thumb'] = $this->postinfo['thumbnail_urls'][0];
                $this->postinfo['url'] = $this->postinfo['link'];

                // Specific theme settings
                if (stristr("Gotham News", $theme)) {
                    if (!isset($this->postinfo['post_thumbnail_value'])) $this->postinfo['thumb'] = $this->postinfo['thumbnail_urls'][0];
                }

                if (stristr("OpenAir", $theme)) {
                    // Videos
                    if (!isset($this->postinfo['video'])) {
                        $this->postinfo['url'] = $this->postinfo['video'];
                        $this->postinfo['video'] = $this->postinfo['title'];
                    }
                }

                if (stristr("Snapshot", $theme)) {
                    if (!isset($this->postinfo['image'])) $this->postinfo['large-image'] = $this->postinfo['image_url'];
                }				
                break;
				
			// === Press75
			case 'Video Elements':
				$post['post_excerpt'] = $this->postinfo['image'];
				$this->postinfo['videolink'] = $this->postinfo['video_urls'][0];
				$this->postinfo['videowidth'] = $this->current_feed['playerwidth'];
				$this->postinfo['videoheight'] = $this->current_feed['playerheight'];
				$this->postinfo['videoembed'] = $this->postinfo['video'];
				$this->postinfo['thumbnail'] = $this->postinfo['thumbnail_urls'][0];
			  break;

			case 'On Demand':
				$post['post_excerpt'] = $this->postinfo['image'];
				$this->postinfo['videoembed'] = $this->postinfo['video'];
				$this->postinfo['thumbnail'] = $this->postinfo['thumbnail_urls'][0];
			  break;

			default:
				$this->postinfo['Image'] = $this->postinfo['image_url'];
				$this->postinfo['Images'] = $this->postinfo['image_urls'];
				
				// Capitalized for Revolution and Options and other themes
				$this->postinfo['Thumbnail'] = $this->postinfo['thumbnail_urls'][0];
				$this->postinfo['Thumbnails'] = $this->postinfo['thumbnail_urls'];
				$this->postinfo['Video'] = $this->postinfo['video_urls'][0];  // Capitalized for Revolution theme
				$this->postinfo['Videos'] = $this->postinfo['video_urls'];
		}


		$answers = array();
		if(isset($this->postinfo['answers']) && is_array($this->postinfo['answers'])/*method_exists($this->current_item, 'get_answers')*/) {
			//$answers = $this->current_item->get_answers();
			$answers = $this->postinfo['answers'];
		}
			
        // we don't want these saved as post metadata
        unset($this->postinfo['author']);
        unset($this->postinfo['author_display_name']);
        unset($this->postinfo['author_email']);
        unset($this->postinfo['author_url']);
        unset($this->postinfo['source']);
        unset($this->postinfo['source_url']);
        unset($this->postinfo['logo_url']);
        unset($this->postinfo['author_id']);
        unset($this->postinfo['content']);
        unset($this->postinfo['post']);
        unset($this->postinfo['title']);
        unset($this->postinfo['excerpt']);
        unset($this->postinfo['date']);
        unset($this->postinfo['poststatus']);
        unset($this->postinfo['category']);
        unset($this->postinfo['categories']);
        unset($this->postinfo['thumbnails']);
        unset($this->postinfo['video']);
        unset($this->postinfo['videos']);
        unset($this->postinfo['page_content']);
        unset($this->postinfo['description']);
        unset($this->postinfo['tags']);
        unset($this->postinfo['image_url']);
        unset($this->postinfo['image_urls']);
        unset($this->postinfo['thumbnail_url']);
        unset($this->postinfo['thumbnail_urls']);

        $post['post_content'] = balanceTags($post['post_content'], true);

        //-----------------
        //  Add the post
        global $isLite; 

        if (!$_REQUEST['preview'] && ($isLite || twoenoughWpCheckStoredLic('mpc', 3, 2) || twoenoughWpCheckStoredLic('mpc', 3, 0))) {

            $post['post_content'] = mpc_process_textlinks($post['post_content'], 'post');

            $pid = wp_insert_post($post);

            update_post_meta($pid, '_feed_id', $this->current_feed['id']);

            if($answers) {
                $this->mpco_insertcomments($pid, $answers, $this->current_feed['yahoo_backdate']);
            }

            $this->current_feed['post_count']++;

            // Add categories and tags for this post
            $res = wp_set_object_terms($pid, $this->current_feed['feedcategories'], 'category');
            wp_set_object_terms($pid, $this->current_feed['tags_list'], 'post_tag');
            // Add all other info as custom fields
            foreach (array_keys($this->postinfo) as $itemfield) {
                if (is_array($this->postinfo[$itemfield])) {
                    if (EXTRA_IMAGE_FIELDS) {
                        for ($j = 0; $j <= 1; $j++) {
                            add_post_meta($pid, $itemfield.'_'.$j, $this->postinfo[$itemfield][$j]);
                        }
                    }
                } else {
                    if (strlen($this->postinfo[$itemfield])) {
                        if (is_string($this->postinfo[$itemfield])) {
                            add_post_meta($pid, $itemfield, $this->postinfo[$itemfield]);
                        }
                    }
                }
            }

            // Add all thumbnail and image attachments to the post

            array_unique($this->postinfo['attachments']);

            if (ALWAYS_ATTACH_IMAGES) {
                foreach (array_keys($this->postinfo['attachments']) as $attachment) {				
                    if (strlen($attachment)) {
                        $attach_post_id = wp_insert_attachment($this->postinfo['attachments'][$attachment], $attachment, $pid);
                    }
                }
            }

            $editlink = '<a href="'.get_option('siteurl').'/wp-admin/post.php?action=edit&post='.$pid.'" target="_blank">Edit</a>';
            $viewlink = '<a href="'.get_option('siteurl').'/?p='.$pid.'" target="_blank">View</a>';
            $this->mpco_logMsg('Post added.&nbsp;&nbsp;&nbsp;&nbsp;'.$editlink.' | '.$viewlink.'<br />', 'added');

        }
        return true;
    }

    protected function truncateContent() {
        global $mpco_options;

        $truncate_post = $this->current_feed['override_truncate_post'] ? $this->current_feed['truncate_post'] : $mpco_options['truncate_post_global'];
        $truncate_post_over = $this->current_feed['override_truncate_post'] ? $this->current_feed['truncate_post_over'] : $mpco_options['truncate_post_over_global'];

        $this->postinfo['content'] = trim($this->postinfo['content']);

        if(!$truncate_post) return;

        $this->postinfo['content'] = mpco_truncate($this->postinfo['content'], $truncate_post_over, '...', false, true);

    }
		
    //---------------------------------------------
    function mpco_applyTemplate($templates) {
        // Split multiple templates if there are any
        if (preg_match('/<!--\s*template\s*-->/', $templates)) {
            $working_templates = preg_split('/<!--\s*template\s*-->/',$templates);
        } else {
            $working_templates[0] = $templates;
        }

        $post_template = $working_templates[array_rand($working_templates)];

        if( isset($this->postinfo['answers']) && !empty($this->postinfo['answers']) ) {
            preg_match_all('/\$reply-author\$/', $post_template, $matches);
            if($matches) {
                $answer = array_shift($this->postinfo['answers']);
                $post_template = str_replace('$reply-author$', $answer['author'], $post_template);
                $post_template = str_replace('$reply-text$', $answer['content'], $post_template);
            }
        }

        preg_match_all('/\{if\s+\$([^\$]+)\$\}(.*)\{\/if\}/Usi', $post_template, $matches);
        $i=0;
        foreach ($matches[0] as $match) {
            if (empty($this->postinfo[$matches[1][$i]])) {
                $post_template = str_replace($match, '', $post_template);
            } else {
                $post_template = str_replace($match, $matches[2][$i], $post_template);
            }
            $i++;
        }

        preg_match_all("/random\([^\)]+\)/s", $post_template, $matches);
        foreach ($matches as $matchset) {
            foreach ($matchset as $match) {
                $tmp = preg_split("/[\(|\)]/", $match, -1, PREG_SPLIT_NO_EMPTY);
                $selected = $tmp[mt_rand( 1, count($tmp) - 1 )];
                $post_template = str_replace($match, $selected, $post_template);
            }
        }

        preg_match_all('/\{for\s+\$([^\$]+)\$\}(.*)\{\/for\}/si', $post_template, $matches);
        $i=0;

        foreach ($matches[0] as $match) {
            $var = $matches[1][$i];
            $subtemplate = $matches[2][$i];
            $values = array();
            $replacement = '';

            if (!is_array($this->postinfo[$var])) {
                $values[0] = $this->postinfo[$var];
            } else {
                $values =  $this->postinfo[$var];
            }

            foreach ($values as $value) {
                $replacement .= str_ireplace('$'.$var.'$', $value, $subtemplate);
            }
            $i++;
            $post_template = str_replace($match, $replacement, $post_template);
        }


      ////
        // Namespace elements
        // Examples:  
        //   %gd:rating%
        //   %http://schemas.google.com/g/2005:rating%
        //   %media:group/media:category%
        //   %http://schemas.google.com/g/2005:rating%
        //   %gd:rating@test%
        //   %http://schemas.google.com/g/2005:rating@test%
        //   %media:group/category@test%

        // Grab variable placeholders for this pattern
        preg_match_all("/%((?:http:\/\/[^:]*)?\w*):([^@%]*)@?(\w*)%/s", $post_template, $matches);


        $placeholders = $matches[0];
        $namespaces = $matches[1];
        $elements = $matches[2];
        $attributes = $matches[3];


        // Loop through each placeholder
        $i=0;
        if (count($placeholders)) {
            foreach ($placeholders as $placeholder) {
                // Get the primary (first) namespace
                if (stristr($placeholder, 'http://')) {						
                    $namespace = $namespaces[$i];
                } else {
                    $namespace = $this->rssmodules[strtolower($namespaces[$i])];
                }

                // Get the element
                If (!strstr($elements[$i], '/')) {
                    // Simple element: %media:content%
                    $element = $elements[$i];
                } else {
                    // Element with subelements: 
                    // group/media:content
                    // or group/content
                    // or group/http://namespace.com/ns-definition:content

                    // Parse elements into subnamespaces/subelements
                    preg_match('/([^\/]*)\/((?:http:\/\/[^:]*)?\w*:)?(\w*)/i', $elements[$i], $elems_parsed);
                    $element = $elems_parsed[1];
                    $sub_ns = rtrim($elems_parsed[2], ':');
                    $sub_elem = $elems_parsed[3];

                    if (!stristr($sub_ns, 'http://')) {
                        $sub_ns = $this->rssmodules[strtolower($sub_ns)];
                    }
                }

                // Get the attribute if there is one
                $attribute = $attributes[$i];

                // Call get_item_tags on the feed item
                $item_tags = $this->current_item->get_item_tags($namespace, $element);

                // Parse out the data we need
                if (empty($sub_elem)) {
                    // If there is only a simple element (i.e., media:content)
                    if (empty($attribute)) {
                        // e.g. %media:content%
                        $the_data = $item_tags[0]['data'];
                    } else {
                        // e.g. %media:content@url%
                        $the_data = $item_tags[0]['attribs'][''][$attribute];						
                    }
                } else {
                    // If there are subelements
                    if (empty($attribute)) {
                        // e.g. %media:group/media:content%
                        $the_data = $item_tags[0]['child'][$sub_ns][$sub_elem][0]['data'];
                    } else {
                        // e.g. %media:group/media:content@url%
                        $the_data = $item_tags[0]['child'][$sub_ns][$sub_elem][0]['attribs'][''][$attribute];
                    }
                }

                // Do the replacement
                $post_template = str_ireplace($placeholder, $the_data, $post_template);
                $i++;
            }
        }


        // Replace all remaining variables with the actual values
        foreach (array_keys($this->postinfo) as $variable) {
            if (!empty($this->postinfo[$variable])) {
                if (is_array($this->postinfo[$variable])) {
                    $this->postinfo[$variable] = array_merge($this->postinfo[$variable]);
                    $delim = '&nbsp;';
                    $var = implode($delim, $this->postinfo[$variable]);
                } else {
                    $var = $this->postinfo[$variable];
                }
                $post_template = str_ireplace('$'.$variable.'$', $var, $post_template);
            } else {
            }
        }

        // Remove any remaining unmatched variables
        if (preg_match('/\$[^\$\s]{3,}\$/', $post_template)) {
            $post_template = preg_replace('/\$[^\$\s]{3,}\$/', '', $post_template);
        }

        return $post_template;
    }


    //---------------------------------------------------------------
    // extract significant keywords from the given content
    function mpco_getKeywords($content) {
        global $mpco_options;

        $keywords = array();

        if( !class_exists('SiteemoLSA') ) {
            require_once( mpco_plugin_dir() . '/core/lsa.class.php' );
        }			

        $lsa = new SiteemoLSA();

        @$lsa->SetText( $content );

        @$lsa->ReturnRanks(0);
        @$lsa->ReturnLimit( $mpco_options['maxtags'] * 1.5 );
        @$lsa->DoPhrases(0);
        @$lsa->Process();
        $keywords = @$lsa->Keywords();

        return $keywords;
    }

    //---------------------------------------------------------------
    function mpco_logMsg($message, $icon = '') {
        global $mpco_options;
        $icon = strtolower($icon);

        // Normal messages
        if ($this->show_output) {
            if (isset($icon)) {
                echo '<br /><img style="vertical-align: middle;" src="'.mpco_pluginURL().'/images/'.$icon.'.png" />&nbsp;&nbsp;';
            }
            echo $message;
        }
    }


    function mpco_initlogger() {
        return;
    }

    function mpco_wpfooterIntercept() {
        $this->mpco_shutdownIntercept();
    }

    function mpco_akismetntercept() {
        $this->mpco_shutdownIntercept();
    }

    //---------------------------------------------------------------
    // Used to trigger the scheduler
    function mpco_shutdownIntercept() {
        global $mpco_options;
        $mpco_options = mpco_getOptions();

        if (time() >= $mpco_options['lastupdate'] + ($mpco_options['mintime'] * 3600)) {
            $this->mpco_processFeeds();
        }
    } // end function

} // end class
} // end if

//---------------------------------------------------------------
function mpco_createClass() {
    global $isLite;
    
	if(!function_exists('get_plugin_data')) {
		require_once(ABSPATH . '/wp-admin/includes/plugin.php');
	}
	require_once(dirname(__FILE__).'/core/functions.php');
	
	if(!$isLite) {
		$plugin_file = mpco_plugin_dir() . '/multipress-content-full.php';
	} else {
		$plugin_file = mpco_plugin_dir() . '/multipress-content.php';		
	}
    
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
	$plugin_data = get_plugin_data($plugin_file);
	
	twoenough_update_option('mpco_version', $plugin_data['Version']);

	global $isActive;
	
	$isActive = twoenoughWpCheckStoredLic('mpc', 3, 2) || twoenoughWpCheckStoredLic('mpc', 3, 0);
	
	// Create class instance
	if (class_exists('multipressContent')) {
		global $multipressContent;
		global $wp_version;
		
		$multipressContent = new multipressContent();
		// Upgrade Check
		$installed_ver = twoenough_get_option( "multipresscontent_installed_version" );
		if ($installed_ver < 1) {
			mpco_installOnActivation();
		}
		
		$auth_key = md5('multipress-content-' . twoenough_get_option('siteurl') . '-somesecureshit');
		
		$multipressContent->auth_key = $auth_key;
		
		//echo "<!-- " . get_option('siteurl') . "?scheduled_run=" . $auth_key . " -->";
		
		if(isset($_GET['scheduled_run']) && $_GET['scheduled_run'] == $auth_key) {
			$multipressContent->mpco_processFeeds();
		}
		
		if (is_admin()) require_once(dirname(__FILE__).'/core/admin.php');
	}
}


function mpco_copy_feed($id) {
	global $wpdb;
	
	$sql = "SELECT * FROM " . mpco_tableName() . ' WHERE id=' . $wpdb->escape($id) . ';';
	$feed = $wpdb->get_row($sql, 'ARRAY_A');
	
	unset($feed['id']);
	$feed['title'] .= ' Copy';
	
	$wpdb->insert(mpco_tableName(), $feed);
	
	echo '<script>location = "?page=ContentBox"</script>';
}

//---------------------------------------------------------------
// Main page
if (!function_exists('mpco_FeedsPage')) {
	function mpco_FeedsPage() {
		global $wp_version;
		mpco_createClass();
		
		if( isset( $_REQUEST['doaction2'] ) ) {
			$_REQUEST['action'] = $_REQUEST['action2'];
		}
		
		switch ($_REQUEST['action']) {
			case 'copy':
				$id = null;
				if(isset($_REQUEST['_fid'])) {
					$id = $_REQUEST['_fid'];
				}
				mpco_copy_feed($id);
				break;
				
			case 'edit':
				require_once(dirname(__FILE__).'/core/admin.php');
				mpco_showEditFeedPage();
				break;
				
			case 'bulk-preview':
				$_REQUEST['preview'] = 1;
			case 'bulk-run':
				if (is_array($_REQUEST['rule']) && count($_REQUEST['rule']) > 0 ) $feeds = $_REQUEST['rule'];
				else {
					mpco_showFeedsPage();
					break;
				}
				global $multipressContent;
				$multipressContent->mpco_processFeeds($feeds, true);
				break;

			case 'run':
			if (is_numeric($_GET['_fid'])) $feed_id = $_GET['_fid'];
			global $multipressContent;
			$multipressContent->mpco_processFeeds($feed_id, true);
			break;
			default:
			mpco_showFeedsPage();
			break;
		}
		return;
	}
}


//---------------------------------------------------------------
// Settings
if (!function_exists('mpco_SettingsPage')) {
	function mpco_SettingsPage() {
		mpco_createClass();
		mpco_showSettingsPage();
	}
}
