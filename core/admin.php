<?PHP

//--------------------------------------------------------------------------------------------------
// Admin options page


function mpco_adminPageHeader() // end function
{

	global $wpdb, $feedtypes, $mpco_options, $multipressContent;
	if (!current_user_can('manage_options')) {
		die(__('Warning: Access denied.'));
	}

	// First do DB version check
	$installed_ver = twoenough_get_option( "multipresscontent_installed_version" );
	
	if ($installed_ver != MPCO_FILE_VERSION) {
		mpco_installOnActivation();
	}
	
	// Check to see if we are disabling or enabling a feed
	if ($_GET['action'] == 'enable' && isset($_REQUEST['_fid'])) {
		$sql ='UPDATE ' . mpco_tableName() . ' SET `enabled`=1 WHERE id='.$wpdb->escape($_GET['_fid']).';';
		$ret = $wpdb->query($sql);
	}

	if ($_GET['action'] == 'disable' && isset($_REQUEST['_fid'])) {
		$sql ='UPDATE ' . mpco_tableName() . ' SET `enabled`=0 WHERE id='.$wpdb->escape($_GET['_fid']).';';
		$ret = $wpdb->query($sql);
	}
	
	if ($_REQUEST['action'] == 'bulk-disable' && isset($_REQUEST['rule']) && is_array($_REQUEST['rule'])) {
		foreach ($_REQUEST['rule'] as $fid) {
			$sql ='UPDATE ' . mpco_tableName() . ' SET `enabled`=0 WHERE id='.$wpdb->escape($fid).';';
			$ret = $wpdb->query($sql);
		}
	}

	if ($_REQUEST['action'] == 'bulk-enable' && isset($_REQUEST['rule']) && is_array($_REQUEST['rule'])) {
		foreach ($_REQUEST['rule'] as $fid) {
			$sql ='UPDATE ' . mpco_tableName() . ' SET `enabled`=1 WHERE id='.$wpdb->escape($fid).';';
			$ret = $wpdb->query($sql);
		}
	}

	if ($_GET['action'] == 'run') check_admin_referer('multipressContent-nav');

	// Save any options submitted via a form post
	if (array_key_exists('_submit_check', $_POST) || ( $_GET['action'] == 'add')) {
		mpco_validateFormInput();

		// Handle feed edits
		if (isset($_POST['_fid']) || $_GET['action'] == 'add') {
			
			$_REQUEST['blog_filter'] = $_REQUEST['mu_type'];
			// Special handling for checkboxes
/* 			$_POST['addothercats'] = intval(isset($_POST['addothercats'])); */
/* 			$_POST['addcatsastags'] = intval(isset($_POST['addcatsastags'])); */
			$_POST['saveimages'] = intval(isset($_POST['saveimages']));
			$_POST['uniqify'] = intval(isset($_POST['uniqify']));			
			$_POST['createthumbs'] = intval(isset($_POST['createthumbs']));
			$_POST['usepostcats'] = intval(isset($_POST['usepostcats']));
			$_POST['yahoo_repliesascomments'] = intval(isset($_POST['yahoo_repliesascomments']));			
			$_POST['yahoo_backdate'] = intval(isset($_POST['yahoo_backdate']));			
/* 			$_POST['addpostcats'] = intval(isset($_POST['addpostcats'])); */
/* 			$_POST['usefeeddate'] = intval(isset($_POST['usefeeddate'])); */
			$_POST['scheduleposts'] = intval(isset($_POST['scheduleposts']));
//			$_POST['use_author_keywords'] = intval(isset($_POST['use_author_keywords']));
			if($_POST['type'] == 1) {
				$_POST['source'] = intval(isset($_POST['source']));
				$_POST['yahoo_numitems'] = intval(isset($_POST['yahoo_numitems']));
			}
			$_POST['override_truncate_post'] = intval(isset($_POST['override_truncate_post']));
			if($_POST['override_truncate_post']) {
				$_POST['truncate_post'] = intval(isset($_POST['truncate_post']));
			}
            
			$_POST['override_spintax_links_limit'] = intval(isset($_POST['override_spintax_links_limit']));
			if($_POST['override_spintax_links_limit']) {
				$_POST['spintax_links_limit'] = intval(isset($_POST['spintax_links_limit']));
			}
            
            $_POST['spintaxes_skip_itself'] = intval(isset($_POST['spintaxes_skip_itself']));

			// Special handling for tags
			if (isset($_POST['tags_input'])) {
				$_POST['tags'] = explode(',', $_POST['tags_input']);
			}

			// Special handling for categories
			if (isset($_POST['post_category'])) {
				$_POST['category'] = $_POST['post_category'];
			}


			// Extra post stuff we won't be saving in the DB
			unset($_POST['post_category'], $_POST['tags_input'], $_POST['newtag'], $_POST['newcat'], $_POST['newcat_parent']);

			// Insert new record or update existing record
			if (empty($_POST['_fid'])) {
				unset($_POST['_fid']);
				$sql = "INSERT INTO " . mpco_tableName();
			} else {
				$sql = "UPDATE " . mpco_tableName();
			}

			$i=0;
			$sql .= " SET ";
			
			$fields = array(
				'title'                   => '',
				'type'                    => 0,
				'mu_type'                 => 0,
				'source'                  => 0,
				'url'                     => '',
				'category'                => '',
				'enabled'                 => 0,
				'tags'                    => '',
				'includeallwords'         => '',
				'includeanywords'         => '',
				'includephrase'           => '',
				'includenowords'          => '',
				'searchfor'               => '',
				'replacewith'             => '',
				'templates'               => '',
				'poststatus'              => '',
				'uniqify'                 => 0,
				'customfield'             => '',
				'customfieldvalue'        => '',
				'saveimages'              => 0,
				'createthumbs'            => 0,
				'playerwidth'             => 0,
				'playerheight'            => 0,
				'uselinkinfo'             => 0,
				'useauthorinfo'           => 0,
				'customplayer'            => '',
				'taggingengine'           => 0,
				'randomcats'              => 0,
				'usepostcats'             => 0,
				'author'                  => '',
				'perc_author'             => 0,
				'type_author'             => 0,
				'alt_author'              => '',
				'author_group'            => '',
				'exclude_author'          => '',
				'schedule'                => 0,
				'updatefrequency'         => 0,
				'post_processing'         => 0,
				'max_posts'               => 0,
				'post_perc'               => 0,
				'lastRun'                 => 0,
				'update_eachx'            => 0,
				'scheduleposts'           => 0,
				'scheduleposts_range'     => 0,
				'use_author_keywords'     => 0,
				'override_keywords'       => '',
				'yahoo_numitems'          => 0,
				'yahoo_region'            => 0,
				'yahoo_category'          => 0,
				'yahoo_range'             => '',
				'yahoo_repliesascomments' => 0,
				'yahoo_backdate'          => 0,
				'article_limit'           => 0,
			);
			if(isset($_POST['mu_type']) && $_POST['mu_type'] != 0) {
				$_POST['type_author'] = $_POST['type_author_2'];
				$_POST['exclude_author'] = $_POST['exclude_author_2'];
				// ! 222
			}
			unset($_POST['type_author_2'], $_POST['exclude_author_2']);
			
			if (empty($_POST['_fid'])) {
				foreach ($fields as $key => $val) {
					if(isset($_POST[$key])) {
						$val = $_POST[$key];
					}
					
					$i++;
					if ($i > 1) {
						$sql .=',';
					}
					if (is_array($val)) {
						$val = mpco_arrayEncode($val);
						$val = mpco_serialize($val);
					}
					$sql .= ' ' . $key . "='" . $wpdb->escape($val)."'";
				}
			} else {
				foreach (array_keys($_POST) as $postitem) {
					if (substr($postitem, 0, 1)<> '_') {
						$i++;
						if ($i > 1) {
							$sql .=',';
						}
						if (is_array($_POST[$postitem])) {
							$_POST[$postitem] = mpco_arrayEncode($_POST[$postitem]);
							$_POST[$postitem] =mpco_serialize($_POST[$postitem]);
						}
						$sql .= ' '.$postitem."='". $wpdb->escape($_POST[$postitem])."'";
					} // endif
				} // end foreach
			}
			if (isset($_POST['_fid'])) {
				$sql .= " WHERE id=" . $wpdb->escape($_POST['_fid']).";";
			}
			$ret = $wpdb->query($sql);
			echo mysql_error();
			if ($ret == 0) echo '<!--'.$sql.'-->';

			// Handle other page updates
		} else {

			// Handle checkboxes and other special items for each page
			if ($_GET['p'] == 'Settings') {
				$_POST['running'] = intval(isset($_POST['running']));
				$_POST['uselinkinfo'] = intval(isset($_POST['uselinkinfo']));
				$_POST['useauthorinfo'] = intval(isset($_POST['useauthorinfo']));
				$_POST['updatecheck'] = intval(isset($_POST['updatecheck']));
				$_POST['feedtags'] = intval(isset($_POST['feedtags']));
				$_POST['posttags'] = intval(isset($_POST['posttags']));
				$_POST['yahootags'] = intval(isset($_POST['yahootags']));
				$_POST['taggingengine'] = intval(isset($_POST['taggingengine']));
				$_POST['uniques_per_author'] = intval(isset($_POST['uniques_per_author']));
				$_POST['truncate_post_global'] = intval(isset($_POST['truncate_post_global']));
				$_POST['spintax_links_limit_global'] = intval(isset($_POST['spintax_links_limit_global']));
					
				if (isset($_POST['tags_input'])) $_POST['tags'] = explode(',', $_POST['tags_input']);
				
			}

			if ($_GET['p'] == 'Tag Options') {
				$_POST['feedtags'] = intval(isset($_POST['feedtags']));
				$_POST['posttags'] = intval(isset($_POST['posttags']));
				$_POST['yahootags'] = intval(isset($_POST['yahootags']));
				$_POST['taggingengine'] = intval(isset($_POST['taggingengine']));
				if (isset($_POST['tags_input'])) $_POST['tags'] = explode(',', $_POST['tags_input']);
			}

			if ($_GET['p'] == 'Filtering') {
				$_POST['filterbytitle'] = intval(isset($_POST['filterbytitle']));
				$_POST['filterbylink'] = intval(isset($_POST['filterbylink']));
				$_POST['skipcaps'] = intval(isset($_POST['skipcaps']));
				$_POST['skipmultiplepunctuation'] = intval(isset($_POST['skipmultiplepunctuation']));
			}
			
			if ($_GET['p'] == 'Support') {
				$_POST['logging'] = intval(isset($_POST['logging']));
				$_POST['showdebug'] = intval(isset($_POST['showdebug']));
			}


			foreach (array_keys($_POST) as $postitem) {
				if (substr($postitem, 0, 1) <> '_') {
					if (is_array($_POST[$postitem])) {
						$_POST[$postitem] = mpco_arrayEncode($_POST[$postitem]);
						$_POST[$postitem] =mpco_serialize($_POST[$postitem]);
					}
					$mpco_options[$postitem] = $_POST[$postitem];
				}
			} // foreach
		} // endif
	} // endif

	
	mpco_saveOptions();

	// Admin options page header
	echo '<link rel="stylesheet" href="'.get_option('siteurl').'/wp-includes/js/thickbox/thickbox.css" type="text/css" media="all" /> ';
	echo '<link rel="stylesheet" type="text/css" href="'.mpco_pluginURL().'/admin.css" />'."\r\n";
	
	?>

	<SCRIPT language="JavaScript">
		<!--
		function deleteRule(delurl)
		{ if (confirm("Delete this rule?")== true) { window.location=delurl; }}
		
		function runRule(runurl) {
			alert("You are running rules from browser. Please keep in mind that some rules take long time to grab and process articles, RSS data and data from other sources which means you will see the page as loading for the long time. Please be patient and let it finish loading.");
			window.location=runurl;
		}
		
		function doAction() {
			if(jQuery('select[name="action"] option:selected').val() == 'bulk-run' || jQuery('select[name="action2"] option:selected').val() == 'bulk-run') {
				alert("You are running rules from browser. Please keep in mind that some rules take long time to grab and process articles, RSS data and data from other sources which means you will see the page as loading for the long time. Please be patient and let it finish loading.");
			}
		}

		function setCookie(c_name,value,exdays) {
			var exdate=new Date();
			exdate.setDate(exdate.getDate() + exdays);
			var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
			document.cookie=c_name + "=" + c_value;
		}
		//-->
	</SCRIPT>
	<?PHP

}
//--------------------------------------------------------------------------------------------------
// Feeds summary admin page

function mpco_showFeedsPage() {

	global $wpdb, $mpco_options, $feedtypes,$sources;
	
	mpco_adminPageHeader();
	
	// First check to see if we are deleting a feed
	if ($_GET['action'] == 'del' && isset($_REQUEST['_fid'])) {
		$sql = 'DELETE FROM '.mpco_tableName().' WHERE id='.$wpdb->escape($_GET['_fid']).' LIMIT 1;';
		$ret = $wpdb->query($sql);
	}
		
	if ( isset($_REQUEST['action']) && $_REQUEST['action'] == 'bulk-delete' && isset($_REQUEST['rule']) && is_array($_REQUEST['rule']) && count($_REQUEST['rule']) > 0 ) {
		if( !isset($_REQUEST['sure']) || $_REQUEST['sure'] != 'yes' ) {
			echo '<div>Are you sure you want to delete selected rules?</div><form action="edit.php?page=' . $_GET['page'] . '" method="post">';
			echo '<input type="hidden" name="action" value="bulk-delete">';
			echo '<input type="hidden" name="sure" value="yes">';
			
			$cond = array();
			foreach ( $_REQUEST['rule'] as $fid ) {
				echo '<input type="hidden" name="rule[]" value="' . $fid . '">';
				$cond[] = 'id='.$wpdb->escape($fid);
			}
			
			
			$sql = "SELECT id, title, type, source, url, enabled FROM " . mpco_tableName() .' WHERE ' . join(' OR ', $cond);
			$feeds = $wpdb->get_results($sql, 'ARRAY_A');
	
			foreach ($feeds as $feed) {
				$feed = mpco_arrayStripSlashes($feed);
	
				if (empty($feed['title'])) {
					$feedurl = $feed['url'];
					if (strlen($feedurl) > 40) $feedurl = substr($feed['url'], 0, 40).'...';
					if ($feed['type'] > 1) {
						$feedtitle = $feedurl ;
					} else {
						$feedtitle = $feedurl;
					}
				} else {
					$feedtitle = $feed['title'];
				}
				
				echo '<div class="'.strtolower(str_replace("!", "", str_replace(" ", "", $feedtypes[$feed['type']]))).'">&nbsp;'.$feedtypes[$feed['type']].'&nbsp;</div>';
				echo '&nbsp;&nbsp;'.$feedtitle.'<br />';
				
			} // foreach
			echo '<button>Yes</button> <button onclick="window.location=\'edit.php?page=' . $_GET['page'] . '\';return false;">No</button>';
			echo "</form>";
			return ;
		}
		foreach ( $_REQUEST['rule'] as $fid ) {
			$sql = 'DELETE FROM '.mpco_tableName().' WHERE id='.$wpdb->escape($fid);
			$ret = $wpdb->query($sql);
		}
	}
	
	// Load feeds list from DB
	$sql = "SELECT id, title, type, source, url, enabled, category, lastRun FROM " . mpco_tableName();

	if(isset($_REQUEST['blog_filter']) && $_REQUEST['blog_filter']) {
		$sql .= " WHERE `mu_type` = " . $_REQUEST['blog_filter'];
	}
	$feeds = $wpdb->get_results($sql, 'ARRAY_A');
	$categories = get_categories('orderby=name&hide_empty=0');
	
	global $isLite;
	
	if (!$isLite) {
		if(twoenoughWpCheckStoredLic('mpc', 3, 0)) {
			$twoeWPLstring = twoeWPL_string('mpc', 3, 0);
		} else {
			$twoeWPLstring = twoeWPL_string('mpc', 3, 2);
		}
	}
	
	echo '<div class="wrap"><div style="float:left;width:50%;"><h2>Content rules</h2></div><div style="float:right;width:50%;text-align:right;margin-top:10px;">'.$twoeWPLstring.'</div><div style="clear:both;"></div>';
	echo '<div id="poststuff" class="metabox-holder"><div id="crosspromoter"></div><script src="' . twoeGetCrossPromoterUrl('mpco') . '"></script>';
	if (function_exists('wp_nonce_url')) $navlink = wp_nonce_url($_SERVER['PHP_SELF'].'?page='. $_GET['page'], 'multipressContent-nav');

	$update_info = twoe_version_compare('mpco');
	if(is_array($update_info)) {
?>
<div style='padding:10px;margin:10px;margin-top:0px;background-color:#FFFBCC;border: 1px solid #E6DB55;'>
	<img style='vertical-align: top;' src='<?=mpco_pluginURL()?>/images/warn.png'/>&nbsp;Update is available for your install of the 'MultiPress Content'.
	Please visit <a href='#'>our site</a> for more information.
</div>
<?
	}
				
	echo '<div id="topmenu">';
	
	echo '<form action="edit.php?page=' . $_GET['page'] . '" method="post" onsubmit="doAction();">';
	
	echo '<a href="'.$navlink.'&amp;action=edit"><img style="vertical-align: middle;" src="'.mpco_pluginURL().'/images/add.png"/>&nbsp;New RSS rule</a>';
	echo "&nbsp;|&nbsp;";
		
	echo '<a href="'.$navlink.'&amp;action=edit&_type=2"><img style="vertical-align: middle;" src="'.mpco_pluginURL().'/images/add.png"/>&nbsp;New Article grabber</a>';
	echo "&nbsp;|&nbsp;";

	echo '<a href="'.$navlink.'&amp;action=edit&_type=3"><img style="vertical-align: middle;" src="'.mpco_pluginURL().'/images/add.png"/>&nbsp;New Yahoo! Answers rule</a>';
				
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				
	echo '<div style="float:right;display:block;">Last run: <b>'.round(((time() - (int)$mpco_options['lastupdate']) / 3600)).'</b> hours ago, ';
	echo 'next run in <b>'.round((((int)$mpco_options['lastupdate'] + (int)($mpco_options['mintime'] * 3600)) - time()) / 3600).'</b> hours.</div></div><br style="clear:both;">';
	
	global $isLite;
	
	if ($isLite) $folder_dir = "multipress-content-lite";
	else $folder_dir = "multipress-content";
	
	if (!$isLite && !twoenoughWpCheckStoredLic('mpc', 3, 2) && !twoenoughWpCheckStoredLic('mpc', 3, 0)) echo "<div style='padding:10px;margin:10px;margin-top:0px;padding-top:0px;color:gray;'><img style='vertical-align: middle;' src='".mpco_pluginURL()."/images/warn.png'/>&nbsp;<small>Warning! Your install is not registered, no posts will be added.</small></div>";

	if (!is_writeable(mpco_plugin_dir() . '/cache')) echo "<div style='padding:10px;margin:10px;margin-top:0px;padding-top:0px;color:gray;'><img style='vertical-align: middle;' src='".mpco_pluginURL()."/images/warn.png'/>&nbsp;<small>Folder <i>/wp-plugins/$folder_dir/cache</i> is not writable by this script. Please log-in with your FTP program and 'CHMOD' it to 777.</small></div>";
	
	echo '<div class="alignleft actions">
<select name="action"> 
<option value="-1" selected="selected">Bulk Actions</option> 
<option value="bulk-run">Run</option> 
<option value="bulk-preview">Preview</option> 
<option value="bulk-enable">Enable</option> 
<option value="bulk-disable">Disable</option> 
<option value="bulk-delete">Delete</option> 
</select>
<input type="submit" value="Apply" name="doaction" id="doaction" class="button-secondary action" /> 
<br class="clear" /> 
</div>';

	if(twoenough_is_mu()) {
		$blogs = mpco_get_blog_list();
		echo '<select name="blog_filter">';
		echo '<option value="0">All blogs</option>';
		foreach($blogs as $blog) {
			$sel = '';
			if($blog['blog_id'] == $_REQUEST['blog_filter']) {
				$sel = ' selected';
			}
			echo '<option value="' . $blog['blog_id'] . '"' . $sel . '>' . $blog['domain'] . '</option>';
		}
		echo '</select>';
		
		echo '<input type="submit" value="Filter" class="button-secondary" />';
		//! filtering
	}
	
	$orderby = isset($_COOKIE['mpco_orderby']) ? $_COOKIE['mpco_orderby'] : 'rule';
	$order   = isset($_COOKIE['mpco_order']) ? $_COOKIE['mpco_order'] : 'desc';
	if($_GET['orderby']) {
		$orderby = $_GET['orderby'];
		?>
		<script>
		setCookie('mpco_orderby', '<?=$orderby?>', 365);
		</script>
		<?php
	}
	if($_GET['order']) {
		$order = $_GET['order'];
		?>
		<script>
		setCookie('mpco_order', '<?=$order?>', 365);
		</script>
		<?php
	}

	$sign    = '<';
	if($order == 'desc') {
		$sign = '>';
	}
	
	$order_rule = $order_type = $order_categories = $order_last_run = '';
	
	$sort_rule = $sort_type = $sort_categories = $sort_last_run = 'sortable';
	switch($orderby) {
		case 'rule':
			$order_rule = array(' ' . ($order == 'desc' ? 'desc' : 'asc'), ($order == 'desc' ? 'asc' : 'desc'));
			$order_field = 'title';
			$sort_rule = 'sorted';
			break;
		case 'cnt':
			$order_cnt = array(' ' . ($order == 'desc' ? 'desc' : 'asc'), ($order == 'desc' ? 'asc' : 'desc'));
			$order_field = 'cnt';
			$sort_cnt = 'sorted';
			break;
		case 'type':
			$order_type = array(' ' . ($order == 'desc' ? 'desc' : 'asc'), ($order == 'desc' ? 'asc' : 'desc'));
			$order_field = 'type';
			$sort_type = 'sorted';
			break;
		case 'categories':
			$order_categories = array(' ' . ($order == 'desc' ? 'desc' : 'asc'), ($order == 'desc' ? 'asc' : 'desc'));
			$order_field = 'categories';
			$sort_categories = 'sorted';
			break;
		case 'last_run':
			$order_last_run = array(' ' . ($order == 'desc' ? 'desc' : 'asc'), ($order == 'desc' ? 'asc' : 'desc'));
			$order_field = 'lastRun';
			$sort_last_run = 'sorted';
			break;
	}
	
	echo '<table class="widefat post fixed" cellspacing="0"> 
	<thead> 
	<tr> 
	<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th> 
	<th scope="col" id="title" class="manage-column column-title ' . $sort_rule . $order_rule[0]. '" style=""><a href="?page=ContentBox&orderby=rule&order=' . $order_rule[1]. '"><span>Rule</span><span class="sorting-indicator"></span></a></th> 
	<th scope="col" id="type" class="manage-column column-author ' . $sort_cnt . $order_cnt[0]. '" style=""><a href="?page=ContentBox&orderby=cnt&order=' . $order_cnt[1]. '"><span># of posts</span><span class="sorting-indicator"></span></a></th> 
	<th scope="col" id="type" class="manage-column column-author ' . $sort_type . $order_type[0]. '" style=""><a href="?page=ContentBox&orderby=type&order=' . $order_type[1]. '"><span>Type</span><span class="sorting-indicator"></span></a></th> 
	<th scope="col" id="categories" class="manage-column column-categories ' . $sort_categories . $order_categories[0]. '" style=""><a href="?page=ContentBox&orderby=categories&order=' . $order_categories[1]. '"><span>Categories</span><span class="sorting-indicator"></span></a></th> 
	<th scope="col" id="date" class="manage-column column-tags ' . $sort_last_run . $order_last_run[0]. '" style=""><a href="?page=ContentBox&orderby=last_run&order=' . $order_last_run[1]. '"><span>Last run</span><span class="sorting-indicator"></span></a></th> 
	</tr> 
	</thead> 
 
	<tfoot> 
	<tr> 
	<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th> 
	<th scope="col" id="title" class="manage-column column-title ' . $sort_rule . $order_rule[0]. '" style=""><a href="?page=ContentBox&orderby=rule&order=' . $order_rule[1]. '"><span>Rule</span><span class="sorting-indicator"></span></a></th> 
	<th scope="col" id="type" class="manage-column column-author ' . $sort_cnt . $order_cnt[0]. '" style=""><a href="?page=ContentBox&orderby=cnt&order=' . $order_cnt[1]. '"><span># of posts</span><span class="sorting-indicator"></span></a></th> 
	<th scope="col" id="type" class="manage-column column-author ' . $sort_type . $order_type[0]. '" style=""><a href="?page=ContentBox&orderby=type&order=' . $order_type[1]. '"><span>Type</span><span class="sorting-indicator"></span></a></th> 
	<th scope="col" id="categories" class="manage-column column-categories ' . $sort_categories . $order_categories[0]. '" style=""><a href="?page=ContentBox&orderby=categories&order=' . $order_categories[1]. '"><span>Categories</span><span class="sorting-indicator"></span></a></th> 
	<th scope="col" id="date" class="manage-column column-tags ' . $sort_last_run . $order_last_run[0]. '" style=""><a href="?page=ContentBox&orderby=last_run&order=' . $order_last_run[1]. '"><span>Last run</span><span class="sorting-indicator"></span></a></th> 
	</tr> 
	</tfoot>
	<tbody>';
	
	mpco_OpenMainSection();

	if (function_exists('wp_nonce_url')) $baselink = wp_nonce_url($_SERVER['PHP_SELF'].'?page='. $_GET['page'], 'multipressContent-feeds-edit');
	if (sizeof($feeds) > 0) {
		// Loop through each feed
		$_feeds = array();
		foreach ($feeds as $feed) {
			$_feed = array();
			$feed = mpco_arrayStripSlashes($feed);
			if (empty($feed['title'])) {
				$feedurl = $feed['url'];
				if (strlen($feedurl) > 40) $feedurl = substr($feed['url'], 0, 40).'...';
				if ($feed['type'] > 1) {
					$feedtitle = $feedurl ;
				} else {
					$feedtitle = $feedurl;
				}
			} else {
				$feedtitle = $feed['title'];
			}
			
			$sql = 'SELECT COUNT(1) FROM ' . $wpdb->postmeta . ' WHERE `meta_key` = "_feed_id" AND `meta_value` = ' . (int) $feed['id'];
			$cnt = $wpdb->get_var($sql, 0);
			
			$_feed['id'] = $feed['id'];
			$_feed['title'] = $feedtitle;
			$_feed['enabled'] = $feed['enabled'];
			$_feed['type'] = $feed['type'];
			$_feed['lastRun'] = $feed['lastRun'];
			$_feed['cnt'] = $cnt;
			
			$feed_categories = mpco_unserialize( $feed['category']);
			
			$category_links = array();
			if(is_array($feed_categories)) {
				foreach ( $categories as $i => $cat ) {
					if( in_array( $cat->cat_ID, $feed_categories ) ) {
						$category_links[] = $cat->category_nicename;
					}
				}
				$_feed['categories'] = join( ', ', $category_links );
			}
			
			$_feeds[] = $_feed;
		}
		
		usort($_feeds, create_function('$a, $b', 'return $a["' . $order_field . '"] ' . $sign . ' $b["' . $order_field . '"];'));
		
		foreach($_feeds as $feed) {
			if ($feed['enabled']) {
				$action = "disable";
			} else {
				$action = "enable";
			}
			$feedtitle = $feed['title'];
			if (!$feed['enabled']) {
				$feedtitle = '<font color="gray"><span style="text-decoration: line-through;">'.$feedtitle.'</span></font>';
			}

			echo '<tr id="rule-'.$feed['id'].'" class="alternate author-other status-future iedit" valign="top">
<th scope="row" class="check-column"><input type="checkbox" name="rule[]" value="'.$feed['id'].'" /></th>
<td class="post-title column-title"><strong><a class="row-title" href="'.$baselink.'&amp;p=&amp;action=edit&_fid='.$feed['id'].'" title="'.htmlentities($feedtitle).'">'.$feedtitle.'</a></strong>
<div class="row-actions">
	<span class="edit"><a href="#" onclick="runRule(\''.$baselink.'&amp;p=&amp;action=run&_fid='.$feed['id'].'\')" title="Run this rule">Run</a> | </span> 
	<span class="edit"><a href="'.$baselink.'&amp;p=&amp;action=run&_fid='.$feed['id'].'&preview=1" title="Preview this rule output">Preview</a> | </span> 
	<span class="edit"><a href="'.$baselink.'&amp;p=&amp;action=edit&_fid='.$feed['id'].'" title="Edit this rule">Edit</a> | </span> 
	<span class="edit"><a href="'.$baselink.'&amp;p=&amp;action=copy&_fid='.$feed['id'].'" title="Copy this rule">Copy</a> | </span> 
	<span class="trash"><a class="submitdelete" title="Move this rule to the Trash" href="#" onclick="deleteRule(\''.$baselink.'&amp;p=Feeds&_fid='.$feed['id'].'&amp;action=del\')">Trash</a> | </span> 
	<span><a href="'.$baselink.'&amp;p=&amp;action='.$action.'&_fid='.$feed['id'].'">'.ucfirst($action).'</a></span>
</div>
</td>
<td class="author column-author">'.$feed['cnt'].'</td>
<td class="author column-author">'.$feedtypes[$feed['type']].'</td>
<td class="categories column-categories">' . $feed['categories'] . '</td>
<td class="date column-date"><abbr title="'.date( 'Y-m-d h:i A', $feed['lastRun'] ).'">'.date( 'Y-m-d h:i A', $feed['lastRun'] ).'</abbr></td></tr>';
			
		} // foreach
		
	} else {
		echo '<tr><td colspan="5">Looks like you haven\'t set up any rules yet so start with adding a <a href="'.$baselink.'&amp;action=edit">new RSS feed grabber</a> or a simple <a href="'.$baselink.'&amp;action=edit&_type=2">Article engine parsing rule</a></td></tr>';
	} //end if		
	
	echo '</tbody></table>';
		
	echo '<div class="alignleft actions">
<select name="action2"> 
<option value="-1" selected="selected">Bulk Actions</option> 
<option value="bulk-run">Run</option> 
<option value="bulk-preview">Preview</option> 
<option value="bulk-enable">Enable</option> 
<option value="bulk-disable">Disable</option> 
<option value="bulk-delete">Delete</option> 
</select> 
<input type="submit" value="Apply" name="doaction2" id="doaction2" class="button-secondary action" /> 
<br class="clear" /> 
</div>';
		
	echo '</form>';
	echo '<br/><br/>';
	
	global $isLite;
	
	if ($isLite) {
		echo '<center><span style="color:gray;font-size: 12px;">In order to keep this powerful tool free for all users, we may post sponsored links within, or at the end of, posts. A small price to pay for this much power</span>';
	}
	
    echo '<br/><br/>';
	
	echo '</div></div></div></div></div></div>';
}

function mpco_MetaFeedsPageSidebar() 
{
	global $mpco_options;
    if (function_exists('wp_nonce_url')) {
        $navlink = wp_nonce_url($_SERVER['PHP_SELF'].'?page='. $_GET['page'], 'multipressContent-nav');
    }

    echo '<div id="feedssidebar">';
    echo '<div id="major-publishing-actions">';
    echo '<div id="previewview"><a href="'.$navlink.'&amp;action=edit"><img style="vertical-align: middle;" src="'.mpco_pluginURL().'/images/add.png"/>&nbsp;New RSS Feed rule</a></div>';
    echo '<div id="previewview"><a href="'.$navlink.'&amp;action=edit&_type=2"><img style="vertical-align: middle;" src="'.mpco_pluginURL().'/images/add.png"/>&nbsp;New Article engine rule</a></div>';		
    echo '</div><br />';

    echo '<a href="'.$navlink.'&amp;p=&amp;action=run" ><img style="vertical-align: middle;" src="'.mpco_pluginURL().'/images/processall.png" />&nbsp;Run all rules</a><br />';
    echo '<a href="'.$navlink.'&amp;p=&amp;action=run&preview=1"><img style="vertical-align: middle;" src="'.mpco_pluginURL().'/images/preview.png" />&nbsp;Preview rules output</a><br /><br />';
    echo 'Rules last processed <b>'.round(((time() - (int)$mpco_options['lastupdate']) / 3600)).'</b> hours ago<br />';
    echo 'Rules will be processed again in <b>'.round((((int)$mpco_options['lastupdate'] + (int)($mpco_options['mintime'] * 3600)) - time()) / 3600).'</b> hours.</p><br />';

    echo '</div>';

}

//--------------------------------------------------------------------------------------------------
// Edit feed admin options page
function mpco_showEditFeedPage()
{

	global $wpdb;
	global $feedtypes;
	global $mpco_options;
	
	mpco_adminPageHeader();
	
	global $isLite;
	
	if (!$isLite) {
		if(twoenoughWpCheckStoredLic('mpc', 3, 0)) {
			$twoeWPLstring = twoeWPL_string('mpc', 3, 0);
		} else {
			$twoeWPLstring = twoeWPL_string('mpc', 3, 2);
		}
	}
		
	echo '<div class="wrap"><div style="float:left;width:50%;"><h2>Rule settings</h2></div><div style="float:right;width:50%;text-align:right;margin-top:10px;">'.$twoeWPLstring.'</div><div style="clear:both;"></div>';
	echo '<div id="poststuff" class="metabox-holder"><div id="crosspromoter"></div><script src="http://twoenough.com/crosspromoter.js.php?installedproducts='.twoenough_get_option('2e_installed_products').'&current=mpco_editrule"></script>';
		
	$update_info = twoe_version_compare('mpco');
	if(is_array($update_info)) {
?>
<div style='padding:10px;margin:10px;margin-top:0px;background-color:#FFFBCC;border: 1px solid #E6DB55;'>
	<img style='vertical-align: top;' src='<?=mpco_pluginURL()?>/images/warn.png'/>&nbsp;Update is available for your install of the 'MultiPress Content'.
	Please visit <a href='#'>our site</a> for more information.
</div>
<?
	}
		
	if (array_key_exists('_submit_check', $_POST)) check_admin_referer('multipressContent-feeds-edit');
	if (empty($_REQUEST['_fid'])) {

		$sql = "SELECT id FROM " . mpco_tableName() .' order by id desc limit 1;';
		$lastid = $wpdb->get_row($sql, 'ARRAY_A');
		// Load defaults if we are adding a new feed
		require(mpco_plugin_dir().'/core/variables.php');
		$feeds = Array();
		$feeds[] = Array();
		$feeds[0] = array(
            "id" => '',
            "enabled" => $enabled,
            "type" => $feed_type,
            "url" => $keywords_or_feed_url,
            "title" => "Rule #".((int)$lastid['id']+1),
            "poststatus" => $default_status,
            "uniqify" => $uniqify,
            "category" => $assign_posts_to_this_category,
            "addothercats" => $add_additional_categories,
            "addcatsastags" => $add_categories_as_tags,
            "tags" => $additional_tags,
            "saveimages" => $save_full_images,
            "createthumbs" => $create_thumbnails,
            "playerwidth" => $video_width,
            "playerheight" => $video_height,
            "includeallwords" => $all_these_words,
            "includeanywords" => $any_of_these_words,
            "includephrase" => $the_exact_phrase,
            "includenowords" => $none_of_these_words,
            "customfield" => $custom_fields,
            "customfieldvalue" => $custom_values,
            "templates" => $feed_post_templates[1],
            "searchfor" => $search_for_patterns,
            "replacewith" => $replace_with_patterns,
            "uselinkinfo" => $use_link_info,
            "useauthorinfo" => $use_author_info,
            "customplayer" => $custom_player_url,
            "randomcats" => $randomly_add_selected_categories,
            "usepostcats" => $use_categories_from_original,
            "addpostcats" => $add_categories_from_original,
            "author" => $author,
            "alt_author" => $alternate_author_if_doesnt_exist,
            "schedule" => $feed_processing_schedule,
            "updatefrequency" => $feed_processing_every_x_updates,
            "post_processing" => $post_processing,
            "max_posts" => $max_posts_per_update,
            "post_perc" => $randomly_include_x_percent_of_posts,
            "lastRun" => '',
            "update_eachx" => '',
            "last_ping" => '',
            "scheduleposts" => $schedule_posts_from_feed,
            "perc_author" => $perc_author,
            "type_author" => $type_author,
            "exclude_author" => $exclude_author,
            "scheduleposts_range" => $scheduleposts_range,
            "use_author_keywords" => $use_author_keywords,
            "override_keywords" => $override_keywords,
            "source" => $source, 		
            "yahoo_numitems" => $yahoo_numitems,
            "yahoo_region" => $yahoo_region,
            "yahoo_category" => $yahoo_category,
            "yahoo_range" => $yahoo_range,
            "yahoo_repliesascomments" => $yahoo_repliesascomments,
            "yahoo_backdate" => $yahoo_backdate,
            "author_group" => $author_group,
            "limit"        => $article_limit,
		);	
		
		if (!mpc_core_installed() || !(twoenoughWpCheckStoredLic('mpc', 3, 1) || twoenoughWpCheckStoredLic('mpc', 3, 0))) $feeds[0]['exclude_author'] = "";
		
		if ($_GET['_type']) $feeds[0]['templates'] = $feed_post_templates[$_GET['_type']];

	} else {
		// Load the specified feed
		$sql = "SELECT * FROM " . mpco_tableName() .' WHERE id='.$wpdb->escape($_REQUEST['_fid']).';';
		$feeds = $wpdb->get_results($sql, 'ARRAY_A');
		
	}
	$categories = Array();
	$blogcategories = get_categories('orderby=name&hide_empty=0');
	foreach ($blogcategories as $cat) {
		$categories[] = $cat->cat_name;
	}

	// There should only be one feed in this loop
	foreach ($feeds as $feed) {
	
		if ($_GET['_type']) $feed['type'] = $_GET['_type'];
		
        if ($feed['id']) {
	
			if ($feed['enabled']) {
				$action = "disable";
			} else {
				$action = "enable";
			}
			if (function_exists('wp_nonce_url')) $navlink = wp_nonce_url($_SERVER['PHP_SELF'].'?page='. $_GET['page'], 'multipressContent-nav');

			echo '<div id="topmenu"><a href="'.$navlink.'&amp;p=&amp;action='.$action.'&_fid='.$feed['id'].'"><img style="vertical-align: middle;" style="vertical-align: middle;" src="'.mpco_pluginURL().'/images/'.$action.'.png" />&nbsp;'.ucfirst($action).' this rule</a>';

            echo "&nbsp;|&nbsp;";		
			
			echo '<a href="#" onclick="deleteRule(\''.$navlink.'&amp;p=Feeds&_fid='.$feed['id'].'&amp;action=del\')"><img style="vertical-align: middle;" style="vertical-align: middle;"  src="'.mpco_pluginURL().'/images/del.png" />&nbsp;Delete this rule</a>';


            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";		
		
            echo '<a href="'.$navlink.'&amp;p=&amp;action=run&_fid='.$feed['id'].'"><img style="vertical-align: middle;" src="'.mpco_pluginURL().'/images/process.png" />&nbsp;Run this rule</a>';
            echo "&nbsp;|&nbsp;";		
            echo '<a href="'.$navlink.'&amp;p=&amp;action=run&preview=1&_fid='.$feed['id'].'"><img style="vertical-align: middle;" src="'.mpco_pluginURL().'/images/preview.png" />&nbsp;Preview rule output</a>';

            echo '<div style="float:right;display:block;">';
            echo 'Next run in <b>'.round((((int)$mpco_options['lastupdate']+ (int)($mpco_options['mintime'] * 3600)) - time()) / 3600).'</b> hours.</div></div><br style="clear:both;">';
		}
		
		if (empty($_GET['_fid'])) {
			echo '<form action="admin.php?page=' . $_GET['page'] . '&amp;p=Feeds&amp;action=add" method="post">';
		} else {
			echo '<form action="admin.php?page=' . $_GET['page'] . '&amp;p=Feeds&_fid='.$feed['id'].'" method="post">';
			$feed = mpco_arrayStripSlashes($feed);
		}
		
		if ( function_exists('wp_nonce_field') ) {
            $wpnonce = wp_nonce_field('multipressContent-feeds-edit');
        }
		echo '<input type="hidden" name="_submit_check" value="1" />'.$wpnonce;
		echo '<input type="hidden" name="_fid" value="'.$feed['id'].'"/>';
		echo '<input type="hidden" name="enabled" value="'.$feed['enabled'].'"/>';

        $sidebars = array('Feed' => 'mpco_MetaEditFeedSidebar', 'Links' => 'mpco_MetaLinksSidebar');
	
		mpco_OpenMainSection();
		
			// Authors

			$author_groups_select = '';

			$wp_roles = new WP_Roles();
	
			foreach ($wp_roles->get_names() as $role=>$roleName) {
                $selected = null;
				if (trim($role) == trim($feed['author_group'])) {
                    $selected = " selected";
                }
			
				$author_groups_select.='<option value="'.$role.'"'.$selected.'>'.$roleName.'</option>';
			}
			
			if(twoenough_is_mu()) {
?>

<script language="JavaScript">

on_mu_type = function(type) {

	if(type == 0) {
		jQuery('#mu_current_site').hide();
		jQuery('#mu_all_sites').show();
	} else {
		jQuery('#mu_current_site').show();
		jQuery('#mu_all_sites').hide();
	}

}

</script>			
<?php
				global $blog_id;
				
				$blogs = mpco_get_blog_list();
				
				$current_site = get_blog_details($blog_id);
				
				if($feed['mu_type'] == 0) {
					$checked_all_sites = ' checked';
					$checked_current_site = '';
					
					$show_all_sites = '';
					$show_current_site = ' style="display:none"';
				} else {
					$checked_all_sites = '';
					$checked_current_site = ' checked';
					
					$show_all_sites = ' style="display:none"';
					$show_current_site = '';
				}
			
				echo mpco_makeBoxStart("Post to MU blog");
				echo '<table id=settingsTable>';
				echo '<tr valign="top"><td width=200>Post items from this rule to</td><td>';
				
				foreach($blogs as $blog) {
					$checked_current_site = '';
					if($blog['blog_id'] == $feed['mu_type']) {
						$checked_current_site = ' checked';
					}
					echo '<label for="mu_type"><input name="mu_type" type="radio" value="' . $blog['blog_id'] . '" onclick="on_mu_type(' . $blog['blog_id'] . ');"' . $checked_current_site . '> ' . $blog['domain'] . '</label><br/>';
				}
				echo '</td></tr>';
			 	
				echo '</table><br /></div></div>';
				
				echo '<div id="mu_all_sites"' . $show_all_sites . '>';

				echo mpco_makeBoxStart("Add posts on behalf");
				echo '<table id=settingsTable>';
				echo '<tr valign="top"><td width=200>All users of the group</td><td><select name="author_group">'.$author_groups_select.'</select></td></tr>';
			 	echo mpco_makeTextInput('exclude_author', $feed['exclude_author'], 60, 'Exclude these users', '<br>This rule will not post items on behalf of this users');
					
				echo '<input type="hidden" name="type_author" value="3"/>';
				echo '</table><br /></div></div>';

				echo '</div>';
				
				echo '<div id="mu_current_site"' . $show_current_site . '>';
				echo mpco_makeBoxStart("Post to authors blogs");
				echo '<table id=settingsTable>';
				$options = array(
					'All authors blogs',
					'Randomly chosen <input name="perc_author" type="text" style="width: 30px" value="'.stripslashes(attribute_escape($feed['perc_author'])).'"/>%&nbsp;of authors blogs',
					'Specified comma-separated author usernames <input name="author" type="text" style="width: 200px" value="'.stripslashes(attribute_escape($feed['author'])).'"/>',
					'All blogs of this group: <select name="author_group">'.$author_groups_select.'</select>'
				);
				echo '<tr valign="top"><td width=200>Post items from this rule to</td><td>';
				echo mpco_makeRadioOnly('type_author_2', $feed['type_author'], $options, '');
				echo '</td></tr>';
			 	echo mpco_makeTextInput('exclude_author_2', $feed['exclude_author'], 60, 'Exclude these blogs', '<br>This rule will not run and post output to mentioned author\'s blogs.');
				echo '</table><br /></div></div>';
				echo '</div>';
			} else {
				
				if (mpc_core_installed() && (twoenoughWpCheckStoredLic('mpc', 3, 1) || twoenoughWpCheckStoredLic('mpc', 3, 0))) {
						
					echo mpco_makeBoxStart("Post to authors blogs");
					echo '<table id=settingsTable>';
					$options = array(
						'All authors blogs',
						'Randomly chosen <input name="perc_author" type="text" style="width: 30px" value="'.stripslashes(attribute_escape($feed['perc_author'])).'"/>%&nbsp;of authors blogs',
						'Specified comma-separated author usernames <input name="author" type="text" style="width: 200px" value="'.stripslashes(attribute_escape($feed['author'])).'"/>',
						'All blogs of this group: <select name="author_group">'.$author_groups_select.'</select>'
					);
					echo '<tr valign="top"><td width=200>Post items from this rule to</td><td>';
					echo mpco_makeRadioOnly('type_author', $feed['type_author'], $options, '');
					echo '</td></tr>';
				 	echo mpco_makeTextInput('exclude_author', $feed['exclude_author'], 60, 'Exclude these blogs', '<br>This rule will not run and post output to mentioned author\'s blogs.');
	
					echo '</table><br /></div></div>';
			
				} else {
					echo mpco_makeBoxStart("Add posts on behalf");
					echo '<table id=settingsTable>';
					echo '<tr valign="top"><td width=200>All users of the group</td><td><select name="author_group">'.$author_groups_select.'</select></td></tr>';
				 	echo mpco_makeTextInput('exclude_author', $feed['exclude_author'], 60, 'Exclude these users', '<br>This rule will not post items on behalf of this users');
						
					echo '<input type="hidden" name="type_author" value="3"/>';
					echo '</table><br /></div></div>';
				
				}
			}
			
		// General Settings
		echo mpco_makeBoxStart("Rule Settings");
		echo '<table id=settingsTable>';
 		echo mpco_makeTextInput('title', $feed['title'], 60, 'Rule title', '<br>This is an optional name you can assign to help manage your content grabbing rules.');
		?>
		
		<script>
		jQuery(document).ready(function() {
			jQuery('input[name="source"]').click(toggleStripHtml);
			toggleStripHtml();

			jQuery('input[name="override_truncate_post"]').click(toggleTruncateOverride);
			toggleTruncateOverride();

            jQuery('input[name="override_spintax_links_limit"]').click(toggleSpintaxOverride);
			toggleSpintaxOverride();
		});
		
		toggleTruncateOverride = function() {
			var $cb = jQuery('input[name="override_truncate_post"]'); 
			if($cb.is(':checked')) {
				jQuery('input[name="truncate_post"]').removeAttr('disabled');
				jQuery('input[name="truncate_post_over"]').removeAttr('disabled');
			} else {
				jQuery('input[name="truncate_post"]').attr('disabled', 'disabled');
				jQuery('input[name="truncate_post_over"]').attr('disabled', 'disabled');
			}
		};

        toggleSpintaxOverride = function() {
			var $cb = jQuery('input[name="override_spintax_links_limit"]'); 
			if($cb.is(':checked')) {
				jQuery('input[name="spintax_links_limit"]').removeAttr('disabled');
				jQuery('input[name="spintax_links_limit_x"]').removeAttr('disabled');
			} else {
				jQuery('input[name="spintax_links_limit"]').attr('disabled', 'disabled');
				jQuery('input[name="spintax_links_limit_x"]').attr('disabled', 'disabled');
			}
		};
		
		// var truncate_post_global = <?=($mpco_options['truncate_post_global']) ? 'true' : 'false'; ?>;
		// var truncate_post_over_global = <?=intval($mpco_options['truncate_post_over_global']); ?>;
		
		toggleStripHtml = function() {
			var $cb = jQuery('input[name="source"]'); 
			if($cb.is(':checked')) {
				$cb.closest('tr').next().show();
			} else {
				$cb.closest('tr').next().hide();
			}
		};

		function setFeedUrl(url) {
			document.getElementById('feedUrl').value='http://'+url;
			document.getElementById('feedUrl').focus();
		}
		</script>
		
		<?
		
		switch($feed['type']) {
		
            case "1":
                echo '<input type=hidden name=type value="1">';
                echo '<tr><td scope="row">Quick feeds</td><td>
<a id=jslink href="javascript:setFeedUrl(\'blogsearch.google.com/blogsearch_feeds?q=%keyword%&num=20&output=rss&safe=active&hl=en&lr=lang_en\');void(0);">Google BlogSearch</a>, 
<!-- <a id=jslink href="javascript:setFeedUrl(\'feeds.technorati.com/search/%keyword%?authority=authority&language=en\');void(0);">Technorati</a>, -->
<!-- <a id=jslink href="javascript:setFeedUrl(\'www.blogdigger.com/search?q=%keyword%&sortby=date&type=rss\');void(0);">BlogDigger</a>, -->
<!-- <a id=jslink href="javascript:setFeedUrl(\'blogpulse.com/rss?query=%keyword%&sort=date&operator=\');void(0);">Blog Pulse</a>, -->
<a id=jslink href="javascript:setFeedUrl(\'bing.com/search?q=%keyword%+site:spaces.live.com++meta:search.market(en-US)+&mkt=en-US&format=rss\');void(0);">Live! Search</a>, 
<a id=jslink href="javascript:setFeedUrl(\'news.search.yahoo.com/news/rss?p=%keyword%\');void(0);">Yahoo! News</a>, 
<a id=jslink href="javascript:setFeedUrl(\'gdata.youtube.com/feeds/api/videos?vq=%keyword%&max-results=20&lr=en\');void(0);">YouTUBE Videos</a>
</td></tr>';
                echo mpco_makeTextInput('url', htmlentities($feed['url']), 60, 'Feed URL', '<br>URL of the RSS feed (URL output should be correctly formatted RSS, not the page which contains link to RSS feed)','','feedUrl');
                echo mpco_makeCheckBox('source', $feed['source'], 'Attempt to grab full articles', 'Attempt to parse complete item text from the original website');
                echo mpco_makeCheckBox('yahoo_numitems', $feed['yahoo_numitems'], 'Strip HTML', 'Remove all HTML from original website');
                break;

            case "2":

                global $sources;

                $articlesources = $sources[2];

                echo '<input type=hidden name=type value="2">';

                echo mpco_makeSelect('source', $articlesources, $feed['source'], 'Article source', '', false, true);
                echo mpco_makeSelect('article_limit', array(10, 20, 50, 100, 200, 500), $feed['article_limit'], 'Maximum number of articles to grab from source', '', false, false);

                break;

            // yahoo answers //
            case "3":

                echo '<input type=hidden name=type value="3">';

                global $yahoo_regions, $yahoo_categories, $yahoo_ranges;

                echo mpco_makeTextInput('yahoo_numitems', $feed['yahoo_numitems'], 4, 'Number of questions to fetch', '"Yahoo! Answers" can return up to 50 questions for each request');

                echo mpco_makeSelect('yahoo_region', $yahoo_regions, $feed['yahoo_region'], '"Yahoo! Answers" country', '', false, true);		
                echo mpco_makeSelect('yahoo_category', $yahoo_categories, $feed['yahoo_category'], 'Questions category', '', false, true);						
                echo mpco_makeSelect('yahoo_range', $yahoo_ranges, $feed['yahoo_range'], 'Questions posting date', '', false, true);						
                echo mpco_makeCheckBox('yahoo_repliesascomments', $feed['yahoo_repliesascomments'], "Post replies as comments", "Add each question replies as post comments", '');		
                echo mpco_makeCheckBox('yahoo_backdate', $feed['yahoo_backdate'], "Backdate questions and answers", "If enabled posts and comments will be dated as original questions and answers grabbed from Yahoo!", '');		

                break;
		}
		
		if (mpc_core_installed() && (twoenoughWpCheckStoredLic('mpc', 3, 1) || twoenoughWpCheckStoredLic('mpc', 3, 0))) {

			$options = array('Use keywords defined in each author blog settings',
			'Use this keyword: <input name="override_keywords" type="text" style="width: 100px" value="'.stripslashes(attribute_escape($feed['override_keywords'])).'"/>');
			echo '<tr valign="top"><td>Keywords to grab content:</td><td>';
			echo mpco_makeRadioOnly('use_author_keywords', $feed['use_author_keywords'], $options, '');
			echo '</td></tr>';

		} else {
		
			echo '<input type="hidden" name="use_author_keywords" value="1"/>';
	    	echo mpco_makeTextInput('override_keywords', $feed['override_keywords'], 30, 'Keyword to grab content', '<br>This keyword will be used to grab data from the selected source');
		
		}
		
		echo mpco_makeSelect('poststatus', Array("publish", "pending", "draft", "private"), $feed['poststatus'], 'Status for new posts', '');
 		echo mpco_makeCheckBox('uniqify', $feed['uniqify'], "Try make content unique", "The rule will attempt to make contents of the post unique using Javascript", '');		
		echo '</table>';
		echo mpco_makeBoxClose();

		// Post processing
		echo mpco_makeBoxStart("Rule Processing");
		echo '<table id=settingsTable>';
		$options = array('With every scheduled update',
		'One ouf of each <input name="updatefrequency" type="text" style="width: 30px" value="'.stripslashes(attribute_escape($feed['updatefrequency'])).'"/>&nbsp;scheduled updates',
		'Only manually');
		echo '<tr valign="top"><td>Process this rule:</td><td>';
		echo mpco_makeRadioOnly('schedule', $feed['schedule'], $options, '');
		echo '</td></tr><tr><td>&nbsp;</td></tr><tr valign="top"><td>With rule items:</td><td>';
		$options = array('Post all items to blog',
		'Only post the first <input name="max_posts" type="text" style="width: 30px" value="'.stripslashes(attribute_escape($feed['max_posts'])).'"/> items',
		'Randomly post <input name="post_perc" type="text" style="width: 40px" value="'.stripslashes(attribute_escape($feed['post_perc'])).'"/><span id=helper>% of all items</span>');
		echo mpco_makeRadioOnly('post_processing', $feed['post_processing'], $options, '');
 		echo mpco_makeCheckBox('scheduleposts', $feed['scheduleposts'], "Schedule posts", "First item will be added as live, rest will appear added within <input type=text size=3 name=scheduleposts_range value='".$feed['scheduleposts_range']."'> hours from the time when rule is processed", '');
		
		echo '</tr></td></table>'.mpco_makeBoxClose();

		// Tags
		echo mpco_makeBoxStart("Automatic tagging");
		echo '<p>You can put comma-separated tags here and few of those will be randomly assinged to posts created by this rule</p>';

		$tags = mpco_unserialize($feed['tags']);
		if (!is_array($tags)) $tags=array();
		?>
		
		<textarea style='width: 100%;height:150px;' name="tags_input"><?PHP echo implode(",", $tags); ?></textarea>
		
		<?php
		echo mpco_makeBoxClose();

		// Categories
		echo mpco_makeBoxStart("Categories", "categorydiv");

		?>
		
		<ul id="category-tabs">
		<li class="tabs"><a href="#categories-all" tabindex="3"><?php _e( 'All Categories' ); ?></a></li>
		<li class="hide-if-no-js"><a href="#categories-pop" tabindex="3"><?php _e( 'Most Used' ); ?></a></li>
		</ul>
		
		<div id="categories-pop" class="tabs-panel" style="display: none;">
			<ul id="categorychecklist-pop" class="categorychecklist form-no-clear" >
		<?php $popular_ids = wp_popular_terms_checklist('category'); ?>
			</ul>
		</div>
		
		<div id="categories-all" class="tabs-panel">
			<ul id="categorychecklist" class="list:category categorychecklist form-no-clear">
				
				<?php
				if (function_exists('wp_category_checklist')) {
					wp_category_checklist('', false, mpco_unserialize($feed['category']), $popular_ids);
				} else {
					global $checked_categories;
					$cats = array();
					$checked_categories = mpco_unserialize($feed['category']);
					dropdown_categories();
				}
				?>
				
			</ul>
		</div>
		
		<?php if ( current_user_can('manage_categories') ) : ?>
		<div id="category-adder" class="wp-hidden-children">
			<h4><a id="category-add-toggle" href="#category-add" class="hide-if-no-js" tabindex="3"><?php _e( '+ Add New Category' ); ?></a></h4>
			<p id="category-add" class="wp-hidden-child">
			<label class="screen-reader-text" for="newcat"><?php _e( 'Add New Category' ); ?></label><input type="text" name="newcat" id="newcat" class="form-required form-input-tip" value="<?php esc_attr_e( 'New category name' ); ?>" tabindex="3" aria-required="true"/>
			<label class="screen-reader-text" for="newcat_parent"><?php _e('Parent category'); ?>:</label><?php wp_dropdown_categories( array( 'hide_empty' => 0, 'name' => 'newcat_parent', 'orderby' => 'name', 'hierarchical' => 1, 'show_option_none' => __('Parent category'), 'tmpco_index' => 3 ) ); ?>
			<input type="button" id="category-add-sumbit" class="add:categorychecklist:category-add button" value="<?php esc_attr_e( 'Add' ); ?>" tabindex="3" />
		<?php	wp_nonce_field( 'add-category', '_ajax_nonce', false ); ?>
			<span id="category-ajax-response"></span></p>
		</div>
		<?php
		endif;
		?>

			<?PHP
			echo '<table id=settingsTable>';
			echo '<tr valign="top"><td scope="row">With the selected categories:</td><td>';

			echo mpco_makeRadioOnly('randomcats', $feed['randomcats'], array('Add each posts to each category', 'Randomly add posts to categories'), '');

			echo '</table>'.mpco_makeBoxClose();

			// Filtering
			echo mpco_makeBoxStart("Filtering", '', true);
			echo '<h4>Include posts that contain (separate words with commas):</h4>';
			echo '<table>';
			echo mpco_makeTextInput('includeallwords', $feed['includeallwords'], 70, 'All these words', '');
			echo mpco_makeTextInput('includeanywords', $feed['includeanywords'], 70, 'Any of these words', '');
			echo mpco_makeTextInput('includephrase', $feed['includephrase'], 70, 'The exact phrase', '');
			echo mpco_makeTextInput('includenowords', $feed['includenowords'], 70, 'None of these words', '');
			echo '</table>'.mpco_makeBoxClose();

			// Post Templates
			echo mpco_makeBoxStart("Post Template", '', true);
			echo 'You can define the contents of the WordPress post which will be created based on items resulted from this rule. Please use our <a href="http://multipressplugin.com/guides#template">template reference</a> to learn more about tokens you can use.<br />';
			echo '<table class="form-table" id=settingsTable>';
			echo '<tr><td colspan="2"><textarea name="templates" rows="20" style="width: 100%" >'.$feed['templates'].'</textarea></td></tr>';
			
			$truncate_post = $feed['override_truncate_post'] ? $feed['truncate_post'] : $mpco_options['truncate_post_global'];
			$truncate_post_over = $feed['override_truncate_post'] ? $feed['truncate_post_over'] : $mpco_options['truncate_post_over_global'];
			
			echo '<tr valign="top"><td width=200>Truncate: </td><td>
				<label for="override_truncate_post">
					<input name="override_truncate_post" id="override_truncate_post" type="checkbox" ' .($feed['override_truncate_post'] ? ' checked' : '') . '/>
					<span id=helper>Override global</label><br/>
					
					<span id=helper><input type="checkbox" name="truncate_post"' . ($truncate_post ? ' checked' : '') . '>Truncate post if over <input type=text size=3 name="truncate_post_over" value="' . intval($truncate_post_over) . '"> characters</span>
			</td></tr>';
					
			echo '</table>'.mpco_makeBoxClose();

			echo mpco_makeBoxStart("Content rewriting", '', true);
            
			echo '<table width=100% id=settingsTable>';
			echo '<tr>' . mpco_makeHalfWidthTextArea('remove_words', $feed['remove_words'], 10, 'Remove words', '<div style="font-size: 10pt">One word by line (no punctuation or special characters). These words will be removed from original content, before spinning and posting. </div>') . '</tr>';
			echo '<tr>' . mpco_makeHalfWidthTextArea('spintaxes', $feed['spintaxes'], 10, 'Spintaxes', '<div style="font-size: 10pt">Here you can easily rewrite the posts created by the rule. Just add the words you want replace in the following format:<br/>
<strong>{blue|green|red}</strong> <i>means any word found in the post will be replaced with one of the others</i><br/>
<strong>{blue|green|red||http://www.colors.com}</strong> <i>same as above plus that the word will also be transformed in a hyperlink to the given URL.<br/>
The URL is optional. If you want to use it, pay attention to the double || before the URL</i></div>') . '</tr>';
            //echo mpco_makeCheckBox('spintaxes_skip_itself', $feed['spintaxes_skip_itself'], 'Skip itself', 'If checked founded words will be skiped from spintax so all occurrences will be replaced.');
            echo '</table>';
			
			$spintax_links_limit   = $feed['override_spintax_links_limit'] ? $feed['spintax_links_limit'] : $mpco_options['spintax_links_limit_global'];
			$spintax_links_limit_x = $feed['override_spintax_links_limit'] ? $feed['spintax_links_limit_x'] : $mpco_options['spintax_links_limit_x_global'];

            echo '<table>
            <tr valign="top"><td width=200>Skip itself: </td><td>
				<label for="spintaxes_skip_itself">
					<input name="spintaxes_skip_itself" id="spintaxes_skip_itself" type="checkbox" ' .($feed['spintaxes_skip_itself'] ? ' checked' : '') . '/>
					<span id=helper>If checked a word can NOT be replaced with itself. If unchecked, a word can also be replaced with itself. </label>
			</td></tr>            
            <tr><td colspan="2">&nbsp;</td></tr>
            <tr valign="top"><td width=200>Links limit: </td><td>
				<label for="override_spintax_links_limit">
					<input name="override_spintax_links_limit" id="override_spintax_links_limit" type="checkbox" ' .($feed['override_spintax_links_limit'] ? ' checked' : '') . '/>
					<span id=helper>Override global</label><br/>
					
					<span id=helper><input type="checkbox" name="spintax_links_limit"' . ($spintax_links_limit ? ' checked' : '') . '>Limit by maximum <input type=text size=3 name="spintax_links_limit_x" value="' . intval($spintax_links_limit_x) . '"> links</span>
			</td></tr>
            </table>';

			echo mpco_makeBoxClose();
		}
		echo '</table><input type="submit" name="_Submit" id="publish" value="Save Changes" tabindex="4" class="button-primary" /></form><br><br></div></div></div></div>';
		
	}
	
	function mpco_MetaEditFeedSidebar($feed = '') 
	{
        global $mpco_options;
        if (function_exists('wp_nonce_url')) {
            $navlink = wp_nonce_url($_SERVER['PHP_SELF'].'?page='. $_GET['page'], 'multipressContent-nav');
        }

        echo '<div id="feedsidebar">';
        if ($feed['id']) {
            echo '<div id="major-publishing-actions">';

            echo '<a href="'.$navlink.'&amp;p=&amp;action=run"><img style="vertical-align: middle;" src="'.mpco_pluginURL().'/images/processall.png" />&nbsp;Run all rules</a></div>';
            echo '<br/><a href="'.$navlink.'&amp;p=&amp;action=run&_fid='.$feed['id'].'"><img style="vertical-align: middle;" src="'.mpco_pluginURL().'/images/process.png" />&nbsp;Run this rule</a><br />';
            echo '<a href="'.$navlink.'&amp;p=&amp;action=run&preview=1&_fid='.$feed['id'].'"><img style="vertical-align: middle;" src="'.mpco_pluginURL().'/images/preview.png" />&nbsp;Preview rule output</a><br /><br>';

            if ($feed['enabled']) {
                $action = "disable";
            } else {
                $action = "enable";
            }

            echo '<a href="'.$navlink.'&amp;p=&amp;action='.$action.'&_fid='.$feed['id'].'"><img style="vertical-align: middle;" style="vertical-align: middle;" src="'.mpco_pluginURL().'/images/'.$action.'.png" />&nbsp;'.ucfirst($action).' this rule</a><br />';
            echo '<a href="#" onclick="deleteRule(\''.$baselink.'&amp;p=Feeds&_fid='.$feed['id'].'&amp;action=del\')"><img style="vertical-align: middle;" style="vertical-align: middle;"  src="'.mpco_pluginURL().'/images/del.png" />&nbsp;Delete this rule</a><br /><br>';

            echo 'Rule will be processed again in <b>'.round((((int)$mpco_options['lastupdate']+ (int)($mpco_options['mintime'] * 3600)) - time()) / 3600).'</b> hours.</p><br />';
        }

		echo '<div class="clear"></div><div id="major-publishing-actions">';

        echo '<input type="submit" name="_Submit" id="publish" value="Save Changes" tabindex="4" class="button-primary" />';
		echo '</div></div>';
	}

	//--------------------------------------------------------------------------------------------------
	function mpco_showSettingsPage()
	{
		global $mpco_options;

		mpco_adminPageHeader();

		global $isLite;
		
		if (!$isLite) {
			if(twoenoughWpCheckStoredLic('mpc', 3, 0)) {
				$twoeWPLstring = twoeWPL_string('mpc', 3, 0);
			} else {
				$twoeWPLstring = twoeWPL_string('mpc', 3, 2);
			}
		}
		
		echo '<div class="wrap"><div style="float:left;width:50%;"><h2>MultiPress Settings</h2></div><div style="float:right;width:50%;text-align:right;margin-top:10px;">'.$twoeWPLstring.'</div><div style="clear:both;"></div>';
		echo '<div id="poststuff" class="metabox-holder">
		<div id="crosspromoter"></div><script src="http://twoenough.com/crosspromoter.js.php?installedproducts='.twoenough_get_option('2e_installed_products').'&current=mpco_settings"></script>';
		if (array_key_exists('_submit_check', $_POST)) check_admin_referer('multipressContent-settings');
		echo '<form action="admin.php?page=' . $_GET['page'] . '&amp;p=Settings'.$feed['id'].'" method="post">';
		if ( function_exists('wp_nonce_field') )	$wpnonce = wp_nonce_field('multipressContent-settings');
		echo '<input type="hidden" name="_submit_check" value="1" />'.$wpnonce;

		if (function_exists('wp_nonce_url')) $navlink = wp_nonce_url($_SERVER['PHP_SELF'].'?page='. $_GET['page'], 'multipressContent-nav');

		$sidebars = array('Settings' => 'mpco_MetaOptionPagesSidebar', 'Links' => 'mpco_MetaLinksSidebar');
		
		mpco_OpenMainSection();

		// General Options
		echo mpco_makeBoxStart("General");
		echo '<table id=settingsTable>';
		
		echo mpco_makeCheckBox('running', $mpco_options['running'], 'Pause content updates', 'If checked no new content will be added', '');

		echo mpco_makeTextInput('mintime', $mpco_options['mintime'], 10, 'Update frequency', 'hours');
		
		echo mpco_makeCheckBox('uniques_per_author', $mpco_options['uniques_per_author'], 'Filter duplicates for each author blog separately', 'if checked MultiPress will make sure author blogs do not get duplicate posts. If unchecked MultiPress will make sure no item is ever repeated on any author blog of the install.');		

 		echo mpco_makeCheckBox('truncate_post_global', $mpco_options['truncate_post_global'], "", "Truncate post if over <input type=text size=3 name=truncate_post_over_global value='".$mpco_options['truncate_post_over_global']."'> characters", '');

		echo '</table>'.mpco_makeBoxClose();
		// Tag Sources
		echo mpco_makeBoxStart("Tags");
		echo '<table id=settingsTable>';
		echo '<tr valign="top"><td>Automatically tag posts with:</td><td>';
		echo mpco_makeCheckBoxOnly('feedtags', $mpco_options['feedtags'], 'Original tags from RSS feeds (not all feeds will support this)', '').'<br />';
		echo mpco_makeCheckBoxOnly('taggingengine', $mpco_options['taggingengine'], 'Built-in LSA-based tagging engine (text will be analyzed to find most "important" words and they will be used as tags)', '').'<br />';
		echo '</table>'.mpco_makeBoxClose();

		// Excerpts
		echo mpco_makeBoxStart("Post Excerpts");
		echo '<table id=settingsTable>';
		echo '<tr colspan="2"><td>For post excerpts (added to each post) randomly use ';
		echo '<input name="minexcerptlen" type="text" size="3" value="'.stripslashes(attribute_escape($mpco_options['minexcerptlen'])).'" /> to ';
		echo '<input name="maxexcerptlen" type="text" size="3" value="'.stripslashes(attribute_escape($mpco_options['maxexcerptlen'])).'" />&nbsp;</td></tr>';
		echo '<tr><td>'.mpco_makeRadioOnly('excerpt_type', $mpco_options['excerpt_type'], array("Words from original item", "Sentences from original item", "Paragraphs from original item"), '');
		echo '</td></tr></table>'.mpco_makeBoxClose();

		// Blacklists
		echo mpco_makeBoxStart("Blacklists");
		echo '<table width=100% id=settingsTable><tr>';
		echo mpco_makeHalfWidthTextArea('domains_blacklist', $mpco_options['domains_blacklist'], 10, 'URL Blacklist', 'Reject posts if is originating from any of those domains, added one per line. Do not add "www.domain.com" or "http://domain.com" - put only "domain.com"');
		echo mpco_makeHalfWidthTextArea('keywords_blacklist', $mpco_options['keywords_blacklist'], 10, 'Keywords Blacklist', 'Reject posts that contain any of those keywords, one per line.');
		echo '</tr></table>';
		echo '</table>'.mpco_makeBoxClose();
        
        echo mpco_makeBoxStart("Content rewriting", '', true);
        
        echo '<table width=100% id=settingsTable><tr>';
        echo mpco_makeHalfWidthTextArea('spintaxes', $mpco_options['spintaxes'], 10, 'Spintaxes', '<div style="font-size: 10pt">Here you can easily rewrite the posts created by the rule. Just add the words you want replace in the following format:<br/>
<strong>{blue|green|red}</strong> <i>means any word found in the post will be replaced with one of the others</i><br/>
<strong>{blue|green|red||http://www.colors.com}</strong> <i>same as above plus that the word will also be transformed in a hyperlink to the given URL.<br/>
The URL is optional. If you want to use it, pay attention to the double || before the URL</i></div>');
        echo '</tr></table>';
 		echo mpco_makeCheckBox('spintax_links_limit_global', $mpco_options['spintax_links_limit_global'], "", "Limit by maximum <input type=text size=3 name=spintax_links_limit_x_global value='".$mpco_options['spintax_links_limit_x_global']."'> links", '');

        echo mpco_makeBoxClose();

		echo '</div>
		<input type="submit" name="_Submit" id="publish" value="Save Changes" tabindex="4" class="button-primary" /></form><br><br>		
		</div></div></div>';
	}

	function mpco_MetaOptionPagesSidebar() {
		echo '<div id="major-publishing-actions">';
		echo '<div class="clear"></div><div id="major-publishing-actions">';
		echo '<input type="submit" name="_Submit" id="publish" value="Save Changes" tabindex="4" class="button-primary" />';
		echo '</div></div>';
	}
		
	// Show the sidebar links
	function mpco_MetaLinksSidebar()
	{
		$links = array(
            "Comprehensive manual" => "http://multipressplugin.com/manual",
            "Help & Support" => "http://multipressplugin.com/support",
            "'<b>two enough</b>' website " => "http://twoenough.com",
            "Affiliate program" => "http://twoenough.com/affiliates"
        );

		$url = mpco_pluginURL();
		$html = '<ul>';
		foreach (array_keys($links) as $link) {
			$html .= '<li><a href="'.$links[$link].'" target="_blank">'.$link.'</a></li>';
		}

		$html .= '</ul><br /><script src="http://multipressplugin.com/script.js.php?version='.MULTIPRESS_VERSION.'"></script>'; //<ul>';

		echo $html;
	}
	
	
	function mpco_makeBoxStart($title, $div = '', $closed = false)
	{
		if (!isset($div)) $div = str_replace(' ', '', $title).'div';
		$html = '<div id="'.$div.'" class="postbox ';
		if ($closed) $html .= 'if-js-closed';
		$html .= '"><h3>'.$title.'</h3><div class="inside">';
		$html .= "\r\n";
		return $html;
	}

	function mpco_makeBoxClose()
	{
		$html = '</div></div>';
		return $html;
	}

	function mpco_makeCheckBox($field, $val, $title, $label, $help='')
	{
		if (strlen($title)) $title .= ': ';
		$html = '<tr valign="top"><td width=200>'.$title.'</td><td><label for="'.$field.'"><input name="'.$field.'" id="'.$field.'" type="checkbox" ';

		if ($val == true) {
			$html .= 'checked="checked" value="checked"';
		}
		$html .= '/>&nbsp;<span id=helper>'.$label."</span></label>";
		if (!empty($help)) $html .= '<br /><span id=helper>'.$help.'</span>';

		$html .= "</td></tr>\r\n";
		return $html;
	}

	function mpco_makeCheckBoxOnly($field, $val, $label, $help)
	{
		$html = '<label for="'.$field.'"><input name="'.$field.'" id="'.$field.'" type="checkbox" ';
		if ($val == true) {
			$html .= 'checked="checked" value="checked"';
		}
		$html .= '/>&nbsp;<span id=helper>'.$label.'</span></label>';

		return $html;
	}

	// $label is an array of options, $val is the index of the selected option
	function mpco_makeRadioOnly($field, $val, $label, $help)
	{
		if (is_array($label)) {
			$i=0;
			foreach ($label as $itemlabel) {
				$html .= '<label for="'.$field.'"><input name="'.$field.'" id="'.$field.$i.'" type="radio" ';
				if ($val == $i) {
					$html .= 'checked value="'.$i.'"';
				} else {
					$html .= 'value="'.$i.'"';
				}
				$html .= '/>&nbsp;<span id=helper>'.$itemlabel.'</span></label><br />';
				$i++;
			}

			return $html;
		} else {
			// Why use a radio if there's only one option?
            // hm... really... WHY?!
		}
	}


	function mpco_makeSelect($field, $values, $selected, $title, $help, $allowblank = true, $keyed = false)
	{
	
		$html = '<tr valign="top"><td width=200>'.$title.':</td><td><select name="'.$field.'">';

		foreach ($values as $key => $value) {
			
			$html .= '<option';
			if ($keyed) $html .=' value="'.$key.'"';						
			if (strcasecmp($value, $selected) == 0) {
			
			$html .= ' selected="selected"';			
			
			} elseif ($keyed && $key == $selected) {

			$html .= ' selected="selected"';			

			}
			
			$html .= '>'.stripslashes(attribute_escape($value)).'</option>';
			
		}
		$html .= "</select></td></tr>\r\n";
		return $html;
	}

	function mpco_makeTextInput($field, $value, $defaultWidth, $title, $help, $backcolor='',$id='')
	{
		$html = '<tr valign="top"><td width=200>'.$title.':</td><td><input ';
		if (strlen($backcolor)) $html.= 'style="background-color: '.$backcolor.';" ';
		$html .= 'name="'.$field.'" value="'.stripslashes(attribute_escape($value)).'"';
		if ($id) $html.= 'id="'.$id.'"';
		if (!empty($defaultWidth)) {
			$html .= ' size="'.$defaultWidth.'"';
		}
		$html .= ' />';
		if (!empty($help)) {
			$html .= '&nbsp;<span id=helper>'.$help.'</span>';
		}
		$html .= "</td></tr>\r\n";
		return $html;
	}

	function mpco_makeWideTextArea($field, $value, $rows, $title, $caption, $help)
	{
		//$html = '<h3>'.$title.'</h3><table>';
		$html = '<table>';
		$html .= '<tr valign="top"><td width=200>'.$caption.':</td></tr><tr><td><textarea name="'.$field.'" rows="'.$rows.'" style="width: 100%">'.stripslashes(attribute_escape($value)).'</textarea>';
		if (!empty($help)) {
			$html .= '<br /><span id=helper>'.$help.'</span>';
		}
		$html .= "</td></tr>\r\n</table><br /><br />";
		return $html;
	}

	function mpco_makeHalfWidthTextArea($field, $value, $rows, $title, $help)
	{
		$html .= '<td width=50%><b>'.$title.'</b><br><br><textarea name="'.$field.'" rows="'.$rows.'" style="width: 100%">'.stripslashes(attribute_escape($value)).'</textarea>';
		if (!empty($help)) {
			$html .= '<br /><span id=helper>'.$help.'</span>';
		}
		$html .= "</td>\r\n";
		return $html;
	}


	// $items is a 2-dimensional array of values like this:
	//     [0] => Array {
	//            [Search] => this
	//            [Replace] => that
	//        )
	// Keys are the column headings, must include at least one value to use as a template
	function mpco_makeValuePairTable($headings, $colOneItems, $colTwoItems)
	{

		$html = '<table><tr>';
		foreach ($headings as $heading) {
			$html .= '<td width="50%"><b>'.$heading.'</b></td>';
		}
		$html .= '</tr>';
		$i = 0;
		if (is_array($colOneItems)) {
			foreach ($colOneItems as $item) {
				if (!empty($colOneItems[$i])) {

					$html .= '<tr>';
					$html .= '<td><input name="'.strtolower(str_replace(" ", "", $headings[0])).'['.$i.']" value="'.stripslashes(attribute_escape($colOneItems[$i])).'" size="50"></td>';
					$html .= '<td><input name="'.strtolower(str_replace(" ", "", $headings[1])).'['.$i.']" value="'.stripslashes(attribute_escape($colTwoItems[$i])).'" size="50"></td></tr>';
					$i++;
				}
			}
		}

		// Add a couple blank lines
		for ($k = 0; $k <= 1; $k++) {
			$html .= '<tr><td><input name="'.strtolower(str_replace(" ", "", $headings[0])).'['.(int)($k + $i).']" size="50"></td>';
			$html .= '<td><input name="'.strtolower(str_replace(" ", "", $headings[1])).'['.(int)($k + $i).']" size="50"></td></tr>';
		}

		$html .= "</table>\r\n";
		return $html;
	}
	
	function mpco_validateFormInput()
	{
	}

	// $sidebars is an array of titles as keys, functions as values
	function mpco_doSideBar($sidebars, $page, $feed = '')
	{
        echo '<div class="submitbox" id="submitlink">';
			
        foreach (array_keys($sidebars) as $sidebar) {
            add_meta_box(str_replace(' ', '', $sidebar).'div', __($sidebar), $sidebars[$sidebar], $page, 'side', 'core');
        }

        echo '<div id="side-info-column" class="inner-sidebar">';

        $side_meta_boxes = do_meta_boxes( $page, 'side', $feed);

        echo '</div></div>';
	}
	
	function mpco_OpenMainSection()
	{
			echo '<div id="post-body" class="has-sidebar">';
			echo '<div id="post-body-content" class="has-sidebar-content">';
	}
