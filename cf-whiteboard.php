<?php
/*
Plugin Name: CF Whiteboard
Plugin URI: http://cfwhiteboard.com
Description: Connects CF Whiteboard to your blog.
Version: 1.12
Author: CF Whiteboard
*/
global $CFWHITEBOARD_VERSION;
$CFWHITEBOARD_VERSION = '1.12';

abstract class cfwhiteboard_Visibility
{
    const Users = 'users'; // logged-in WP users only
    const Everyone = 'everyone';
}

abstract class cfwhiteboard_Position
{
    const TitleRight = 'titleright'; // to the right of the title
    const CustomSelector = 'customselector'; // custom jquery selector

    // Custom Selector: Insertion Method
    const CustomSelectorInsertionAppend = 'append';
    const CustomSelectorInsertionPrepend = 'prepend';
    const CustomSelectorInsertionBefore = 'before';
    const CustomSelectorInsertionAfter = 'after';

    // Custom Selector: Alignment
    const CustomSelectorAlignmentFloatLeft = 'floatleft';
    const CustomSelectorAlignmentFloatRight = 'floatright';
    const CustomSelectorAlignmentInline = 'inline';
    const CustomSelectorAlignmentBlock = 'block';
}

global $CFWHITEBOARD_DEFAULT_OPTIONS;
$CFWHITEBOARD_DEFAULT_OPTIONS = array();
$CFWHITEBOARD_DEFAULT_OPTIONS['affiliate_id'] = esc_attr( str_replace(array('http://', 'https://', 'www.', '.com/', '.com'), '', home_url()) );
$CFWHITEBOARD_DEFAULT_OPTIONS['visibility'] = cfwhiteboard_Visibility::Users;
$CFWHITEBOARD_DEFAULT_OPTIONS['position'] = cfwhiteboard_Position::TitleRight;
$CFWHITEBOARD_DEFAULT_OPTIONS['position_customselectorinsertion'] = cfwhiteboard_Position::CustomSelectorInsertionAppend;
$CFWHITEBOARD_DEFAULT_OPTIONS['position_customselectortarget'] = '';
$CFWHITEBOARD_DEFAULT_OPTIONS['position_customselectorparent'] = '';
$CFWHITEBOARD_DEFAULT_OPTIONS['position_customselectoralignment'] = cfwhiteboard_Position::CustomSelectorAlignmentFloatLeft;
$CFWHITEBOARD_DEFAULT_OPTIONS['position_customselectormargin'] = '0';
// $CFWHITEBOARD_DEFAULT_OPTIONS['categories'] = array();

function cfwhiteboard_get_options() {
    global $CFWHITEBOARD_DEFAULT_OPTIONS;
    if (empty($CFWHITEBOARD_DEFAULT_OPTIONS)) $CFWHITEBOARD_DEFAULT_OPTIONS = array();

    $options = get_option('cfwhiteboard_options');
    if ($options == FALSE) $options = array();
    return array_merge($CFWHITEBOARD_DEFAULT_OPTIONS, $options);
}

function cfwhiteboard_is_visible($options) {
    if (empty($options) || empty($options['visibility'])) {
        $options = cfwhiteboard_get_options();
    }

    if ($options['visibility'] == cfwhiteboard_Visibility::Users) {
        return is_user_logged_in();
    }

    // assume $options['visibility'] == cfwhiteboard_Visibility::Everyone
    return true;
}

function cfwhiteboard_is_preview_mode($options) {
    if (empty($options) || empty($options['visibility'])) {
        $options = cfwhiteboard_get_options();
    }

    if ($options['visibility'] == cfwhiteboard_Visibility::Users) {
        return true;
    }

    // assume $options['visibility'] == cfwhiteboard_Visibility::Everyone
    return false;
}

function cfwhiteboard_is_proper_post($id = -1) {
    $currentId = get_the_ID();
    
    return ($id == $currentId) &&
        !is_feed() &&
        !is_page() &&
        in_the_loop() &&
        (get_post_type() == 'post');
        // (
        //     empty($options['categories']) ||
        //     in_category($options['categories'])
        // );
}

function cfwhiteboard_get_wods($post_id) {
    global $CFWHITEBOARD_WODS_META_KEY;

    return get_post_meta($post_id, $CFWHITEBOARD_WODS_META_KEY, true);
}

function cfwhiteboard_generate_placeholder($post_id, $options, $wods) {
    if (empty($options) || empty($options['affiliate_id'])) {
        $options = cfwhiteboard_get_options();
    }

    $affiliateId = !empty($options['affiliate_id']) ? $options['affiliate_id'] : 'testaffiliate';
    if (cfwhiteboard_is_preview_mode($options)) $affiliateId .= '_preview';
    
    if (!is_array($wods)) $wods = array();

    $data = array(
        "affiliateId" => $affiliateId,
        "postId" => $post_id,
        "postModified" => get_the_modified_time('Y-m-d H:i:s'),
        "wods" => $wods
    );

    $authorization = is_user_logged_in() ? 'data-authorization="admin"' : '';

    return '<div class="cfwhiteboard cleanslate" data-cfwhiteboard="'. esc_attr(json_encode($data)) .'" '. $authorization .'></div>';
}


// register_activation_hook(__FILE__, 'cfwhiteboard_activate');
// function cfwhiteboard_activate() {
//     $post = array(
//         'comment_status' => 'closed', // 'closed' means no comments.
//         'ping_status' => 'closed', // 'closed' means pingbacks or trackbacks turned off
//         'post_content' => [ <the text of the post> ], //The full text of the post.
//         'post_status' => [ 'draft' | 'publish' | 'pending'| 'future' | 'private' ], //Set the status of the new post. 
//         'post_title' => [ <the title> ], //The title of your post.
//         'post_type' => 'post' //You may want to insert a regular post, page, link, a menu item or some custom post type
//     );
// }


add_action('init', 'cfwhiteboard_bind_actions_and_filters');
function cfwhiteboard_bind_actions_and_filters() {
    $options = cfwhiteboard_get_options();

    // if (! (is_user_logged_in() && wp_get_current_user()->first_name == "Collin"))
    //     return;
    if (! cfwhiteboard_is_visible($options))
        return;
        
    add_action('wp_print_styles', 'cfwhiteboard_stylesheet', 999999);
    add_action('wp_enqueue_scripts', 'cfwhiteboard_scripts', 999999);
    add_action('wp_enqueue_scripts', 'cfwhiteboard_scripts_data', 1000000);
    add_action('wp_enqueue_scripts', 'cfwhiteboard_latest_jquery', 1);
    add_action('template_redirect', 'cfwhiteboard_json_meta');

    if ($options['position'] == cfwhiteboard_Position::CustomSelector) {
        add_filter('the_content', 'cfwhiteboard_add_to_post', 999999, 2);
        add_filter('the_excerpt', 'cfwhiteboard_add_to_post', 999999, 2);
    } else {
        add_filter('the_title', 'cfwhiteboard_add_to_post', 999999, 2);
    }
}

function cfwhiteboard_add_to_post($titleOrContent, $id = NULL) {
    $options = cfwhiteboard_get_options();

    if (empty($id)) $id = get_the_ID();

    // Workaround for bug in WP < 3.3 where title filters are passed the entire post object instead of just the id
    if (isset($id, $id->ID)) {
        $id = $id->ID;
    }
    
    // Debug info
    // if (cfwhiteboard_is_preview_mode($options)) {
    //     $titleOrContent .= '<span style="display:none !important;width:0;height:0;">' .
    //         '  title_for_id: ' . (isset($id->ID) ? $id->ID : $id) .
    //         ', current_id = ' . get_the_ID() .
    //         ', is_feed = ' . (is_feed() ? 'true' : 'false') .
    //         ', is_page = ' . (is_page() ? 'true' : 'false') .
    //         ', in_the_loop = ' . (in_the_loop() ? 'true' : 'false') .
    //         ', post_type = ' . get_post_type() .
    //         '</span>';
    // }


    if (! cfwhiteboard_is_proper_post($id))
        return $titleOrContent;

    $wods = cfwhiteboard_get_wods($id);
    if ((!is_array($wods)) || empty($wods))
        return $titleOrContent;
    
    $cfw_placeholder = cfwhiteboard_generate_placeholder(get_the_ID(), $options, $wods);
	return $cfw_placeholder . $titleOrContent;
}

function cfwhiteboard_stylesheet() {
    global $CFWHITEBOARD_VERSION;
    if (!isset($CFWHITEBOARD_VERSION)) $CFWHITEBOARD_VERSION = '0.0';
    
    wp_register_style('cfwhiteboard',
        plugins_url('cfwhiteboard.css', __FILE__),
        false,
        $CFWHITEBOARD_VERSION
    );
    wp_enqueue_style( 'cfwhiteboard');
}

function cfwhiteboard_scripts() {
    global $CFWHITEBOARD_VERSION;
    if (!isset($CFWHITEBOARD_VERSION)) $CFWHITEBOARD_VERSION = '0.0';
    
    wp_enqueue_script('cfwhiteboard',
        plugins_url('cfwhiteboard.js', __FILE__),
        array('jquery'),
        $CFWHITEBOARD_VERSION
    );
}

function cfwhiteboard_latest_jquery($version) {
    wp_deregister_script('jquery'); 
    wp_register_script('jquery',
        plugins_url('jquery.js', __FILE__),
        false,
        '1.7.1'
    );
    wp_enqueue_script('jquery');
}

function cfwhiteboard_scripts_data() {
    $options = cfwhiteboard_get_options();

    if ($options['position'] == cfwhiteboard_Position::CustomSelector) {
        $data = array();
        $data['insertion'] = $options['position_customselectorinsertion'];
        $data['target'] = $options['position_customselectortarget'];
        $data['parent'] = $options['position_customselectorparent'];
        $data['alignment'] = $options['position_customselectoralignment'];
        $data['margin'] = $options['position_customselectormargin'];

        wp_localize_script('cfwhiteboard', 'CFW_POSITION', $data);
    }
}


function cfwhiteboard_options_page() {
    global $CFWHITEBOARD_DEFAULT_OPTIONS;
    if (!isset($CFWHITEBOARD_DEFAULT_OPTIONS)) $CFWHITEBOARD_DEFAULT_OPTIONS = array();

	// Require admin privs
	if ( ! current_user_can('manage_options') )
		return false;
	

    $category_prefix = 'CFWHITEBOARD_category_';
  
    // Make available services extensible via plugins, themes (functions.php), etc.
    // $A2A_SHARE_SAVE_services = apply_filters('A2A_SHARE_SAVE_services', $A2A_SHARE_SAVE_services);

    if (isset($_POST['Submit'])) {
        
        // Nonce verification 
        check_admin_referer('cfwhiteboard-update-options');
    
        $new_options = array();
        
        // Affiliate ID
        if (!empty($_POST['CFWHITEBOARD_affiliate_id']))
            $new_options['affiliate_id'] = @$_POST['CFWHITEBOARD_affiliate_id'];

        // Visibility (Preview Mode)
        if (isset($_POST['CFWHITEBOARD_visibility']))
            $new_options['visibility'] = @$_POST['CFWHITEBOARD_visibility'];

        // Position
        if (isset($_POST['CFWHITEBOARD_position']))
            $new_options['position'] = @$_POST['CFWHITEBOARD_position'];

        // Custom Selector Fields
        if (!empty($_POST['CFWHITEBOARD_position_customselectorinsertion']))
            $new_options['position_customselectorinsertion'] = @$_POST['CFWHITEBOARD_position_customselectorinsertion'];
        if (!empty($_POST['CFWHITEBOARD_position_customselectortarget']))
            $new_options['position_customselectortarget'] = @$_POST['CFWHITEBOARD_position_customselectortarget'];
        if (!empty($_POST['CFWHITEBOARD_position_customselectorparent']))
            $new_options['position_customselectorparent'] = @$_POST['CFWHITEBOARD_position_customselectorparent'];
        if (!empty($_POST['CFWHITEBOARD_position_customselectoralignment']))
            $new_options['position_customselectoralignment'] = @$_POST['CFWHITEBOARD_position_customselectoralignment'];
        if (!empty($_POST['CFWHITEBOARD_position_customselectormargin']))
            $new_options['position_customselectormargin'] = @$_POST['CFWHITEBOARD_position_customselectormargin'];

        // Categories
        // $new_options['categories'] = array();
        // $categories = get_categories($category_args);
        // foreach($categories as $category) {
        //     if (!empty($_POST[$category_prefix . $category->cat_ID])) {
        //         array_push($new_options['categories'], $category->cat_ID);
        //     }
        // }

        // Preview Only (Whiteboard is only visible to logged-in WordPress users.  Workout entries will not be saved.)
        // Active (Whiteboard is visible to anyone who visits your website.  Workout entries will be saved.)

        // $new_options['position'] = ($_POST['A2A_SHARE_SAVE_position']) ? @$_POST['A2A_SHARE_SAVE_position'] : 'bottom';
        // $new_options['display_in_posts_on_front_page'] = (@$_POST['A2A_SHARE_SAVE_display_in_posts_on_front_page']=='1') ? '1':'-1';
        // $new_options['display_in_excerpts'] = (@$_POST['A2A_SHARE_SAVE_display_in_excerpts']=='1') ? '1':'-1';
        // $new_options['display_in_posts'] = (@$_POST['A2A_SHARE_SAVE_display_in_posts']=='1') ? '1':'-1';
        // $new_options['display_in_pages'] = (@$_POST['A2A_SHARE_SAVE_display_in_pages']=='1') ? '1':'-1';
        // $new_options['display_in_feed'] = (@$_POST['A2A_SHARE_SAVE_display_in_feed']=='1') ? '1':'-1';
        // $new_options['show_title'] = (@$_POST['A2A_SHARE_SAVE_show_title']=='1') ? '1':'-1';
        // $new_options['onclick'] = (@$_POST['A2A_SHARE_SAVE_onclick']=='1') ? '1':'-1';
        // $new_options['button'] = @$_POST['A2A_SHARE_SAVE_button'];
        // $new_options['button_custom'] = @$_POST['A2A_SHARE_SAVE_button_custom'];
        // $new_options['additional_js_variables'] = trim(@$_POST['A2A_SHARE_SAVE_additional_js_variables']);
        // $new_options['inline_css'] = (@$_POST['A2A_SHARE_SAVE_inline_css']=='1') ? '1':'-1';
        // $new_options['cache'] = (@$_POST['A2A_SHARE_SAVE_cache']=='1') ? '1':'-1';
        // 
        // // Schedule cache refresh?
        // if (@$_POST['A2A_SHARE_SAVE_cache']=='1') {
        //  A2A_SHARE_SAVE_schedule_cache();
        //  A2A_SHARE_SAVE_refresh_cache();
        // } else {
        //  A2A_SHARE_SAVE_unschedule_cache();
        // }
        // 
        // // Store desired text if 16 x 16px buttons or text-only is chosen:
        // if( $new_options['button'] == 'favicon.png|16|16' )
        //  $new_options['button_text'] = $_POST['A2A_SHARE_SAVE_button_favicon_16_16_text'];
        // elseif( $new_options['button'] == 'share_16_16.png|16|16' )
        //  $new_options['button_text'] = $_POST['A2A_SHARE_SAVE_button_share_16_16_text'];
        // else
        //  $new_options['button_text'] = ( trim($_POST['A2A_SHARE_SAVE_button_text']) != '' ) ? $_POST['A2A_SHARE_SAVE_button_text'] : __('Share/Bookmark','add-to-any');
        //  
        // // Store chosen individual services to make active
        // $active_services = Array();
        // if ( ! isset($_POST['A2A_SHARE_SAVE_active_services']))
        //  $_POST['A2A_SHARE_SAVE_active_services'] = Array();
        // foreach ( $_POST['A2A_SHARE_SAVE_active_services'] as $dummy=>$sitename )
        //  $active_services[] = substr($sitename, 7);
        // $new_options['active_services'] = $active_services;
        // 
        // // Store special service options
        // $new_options['special_facebook_like_options'] = array(
        //  'verb' => ((@$_POST['addtoany_facebook_like_verb'] == 'recommend') ? 'recommend' : 'like')
        // );
        // $new_options['special_twitter_tweet_options'] = array(
        //  'show_count' => ((@$_POST['addtoany_twitter_tweet_show_count'] == '1') ? '1' : '-1')
        // );
        // $new_options['special_google_plusone_options'] = array(
        //  'show_count' => ((@$_POST['addtoany_google_plusone_show_count'] == '1') ? '1' : '-1')
        // );
		
    	update_option('cfwhiteboard_options', array_merge($CFWHITEBOARD_DEFAULT_OPTIONS, $new_options));
    
		?>
    	<div class="updated fade">
            <p>
                <strong><?php _e('Settings saved.'); ?></strong>
                <?php _e('Please empty the page cache for new settings to take effect.', 'cf-whiteboard'); ?>
            </p>
        </div>
		<?php
		
    } else if (isset($_POST['Reset'])) {
         // Nonce verification 
          check_admin_referer('cfwhiteboard-update-options');
		  
		  delete_option('cfwhiteboard_options');
    }

    $options = cfwhiteboard_get_options();

    // Get info for the categories options
    // $category_args = array(
    //     'orderby' => 'name',
    //     'hide_empty' => 0
    // );
    // $categories = get_categories($category_args);
    // foreach($categories as $category) {
    //     $category->selected = empty($options['categories']) ? true : in_array($category->cat_ID, $options['categories']);
    // } 

    // function position_in_content($options, $option_box = FALSE) {
    //  
    //  if ( ! isset($options['position'])) {
    //      $options['position'] = 'bottom';
    //  }
    //  
    //  $positions = array(
    //      'bottom' => array(
    //          'selected' => ('bottom' == $options['position']) ? ' selected="selected"' : '',
    //          'string' => __('bottom', 'add-to-any')
    //      ),
    //      'top' => array(
    //          'selected' => ('top' == $options['position']) ? ' selected="selected"' : '',
    //          'string' => __('top', 'add-to-any')
    //      ),
    //      'both' => array(
    //          'selected' => ('both' == $options['position']) ? ' selected="selected"' : '',
    //          'string' => __('top &amp; bottom', 'add-to-any')
    //      )
    //  );
    //  
    //  if ($option_box) {
    //      $html = '</label>';
    //      $html .= '<label>'; // Label needed to prevent checkmark toggle on SELECT click 
    //      $html .= '<select name="A2A_SHARE_SAVE_position">';
    //      $html .= '<option value="bottom"' . $positions['bottom']['selected'] . '>' . $positions['bottom']['string'] . '</option>';
    //      $html .= '<option value="top"' . $positions['top']['selected'] . '>' . $positions['top']['string'] . '</option>';
    //      $html .= '<option value="both"' . $positions['both']['selected'] . '>' . $positions['both']['string'] . '</option>';
    //      $html .= '</select>';
    //      
    //      return $html;
    //  } else {
    //      $html = '<span class="A2A_SHARE_SAVE_position">';
    //      $html .= $positions[$options['position']]['string'];
    //      $html .= '</span>';
    //      
    //      return $html;
    //  }
    // }
	
    ?>
    
    <!-- <!php A2A_SHARE_SAVE_theme_hooks_check(); ?> -->
    <style>
        form > fieldset {
            border: 1px solid #ccc;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            border-radius: 3px;
            padding: 10px 15px;
        }
        fieldset + fieldset {
            margin-top: 15px;
        }
        fieldset ul {
            margin: 0;
        }
        form > fieldset > ul > li {
            padding-left: 24px;
            text-indent: -24px;
        }
        form > fieldset > ul > li > * {
            text-indent: 0;
        }
        fieldset h1 {
            font-size: 1em;
        }

        fieldset ul li fieldset {
            background: #f5f5f5;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            border-radius: 3px;
            margin-top: 7px;
            padding: 10px 12px;
            text-shadow: 0 1px 0 #fff;
        }
        fieldset ul li fieldset label {
            display: inline-block;
            width: 100px;
            *zoom: 1;
            *display: inline;
        }

        input[type="radio"],
        input[type="checkbox"] {
            font-size: 19px;
        }
        input[type="radio"] + label,
        input[type="checkbox"] + label {
            line-height: 19px;
            margin-left: 5px;
        }
    </style>

    <div class="wrap">
	
	<div id="icon-options-general" class="icon32"></div>
	
	<h2><?php _e('CF Whiteboard ', 'cf-whiteboard') . _e( 'Settings' ); ?></h2>

    <form method="post" action="">
        
	    <?php wp_nonce_field('cfwhiteboard-update-options'); ?>

        <p>
            <label for="CFWHITEBOARD_affiliate_id">
                <strong><?php _e('Affiliate ID:', 'cf-whiteboard') ?></strong>
            </label>
            <input type="text" id="CFWHITEBOARD_affiliate_id" name="CFWHITEBOARD_affiliate_id" value="<?php echo esc_attr( $options['affiliate_id'] ); ?>" />
            <label for="CFWHITEBOARD_affiliate_id">
                <?php _e('(Changing this value could result in data loss.)', 'cf-whiteboard') ?>
            </label>
        </p>
        
        <fieldset>
            <legend><?php _e('Visibility Mode', 'cf-whiteboard'); ?></legend>
            <ul>
                <li>
                    <input type="radio" id="CFWHITEBOARD_visibility_users" name="CFWHITEBOARD_visibility" value="<?php echo esc_attr( cfwhiteboard_Visibility::Users ); ?>" <?php echo $options['visibility'] == cfwhiteboard_Visibility::Users ? 'checked="checked"' : ''; ?> />
                    <label for="CFWHITEBOARD_visibility_users">
                        <strong><?php _e('Preview Mode.', 'cf-whiteboard') ?></strong> <?php _e('Athletes cannot see the whiteboard. Only logged-in WordPress users can see the whiteboard.  Whiteboard entries will be removed when you switch to Live Mode, so feel free to experiment.', 'cf-whiteboard') ?>
                    </label>
                </li>
                <li>
                    <input type="radio" id="CFWHITEBOARD_visibility_everyone" name="CFWHITEBOARD_visibility" value="<?php echo esc_attr( cfwhiteboard_Visibility::Everyone ); ?>" <?php echo $options['visibility'] == cfwhiteboard_Visibility::Everyone ? 'checked="checked"' : ''; ?> />
                    <label for="CFWHITEBOARD_visibility_everyone">
                        <strong><?php _e('Live Mode.', 'cf-whiteboard') ?></strong> <?php _e('Anyone can see the whiteboard. Whiteboard entries will be saved.', 'cf-whiteboard'); ?>
                    </label>
                </li>
            </ul>
        </fieldset>
        
        <fieldset>
            <legend><?php _e('Whiteboard Position', 'cf-whiteboard'); ?></legend>
            <ul>
                <script type="text/javascript">
                    jQuery(function($) {
                        $('input[name="CFWHITEBOARD_position"]').click(function() {
                            var $this = $(this);
                            if ($this.val() == '<?php echo cfwhiteboard_Position::CustomSelector; ?>') {
                                $this.closest('li').find('fieldset').css('opacity', 0).slideDown().queue(function() {
                                    $(this).animate({opacity: 1});
                                    $(this).dequeue();
                                });
                            } else {
                                $this.closest('ul').find('fieldset').animate({opacity: 0}).queue(function() {
                                    $(this).slideUp();
                                    $(this).dequeue();
                                });
                            }
                        });
                    });
                </script>
                <li>
                    <input type="radio" id="CFWHITEBOARD_position_titleright" name="CFWHITEBOARD_position" value="<?php echo esc_attr( cfwhiteboard_Position::TitleRight ); ?>" <?php echo $options['position'] == cfwhiteboard_Position::TitleRight ? 'checked="checked"' : ''; ?> />
                    <label for="CFWHITEBOARD_position_titleright">
                        <strong><?php _e('Default Position.', 'cf-whiteboard'); ?></strong> <?php _e('The whiteboard is positioned to the right of the post title.', 'cf-whiteboard') ?>
                    </label>
                </li>
                <li>
                    <input type="radio" id="CFWHITEBOARD_position_customselector" name="CFWHITEBOARD_position" value="<?php echo esc_attr( cfwhiteboard_Position::CustomSelector ); ?>" <?php echo $options['position'] == cfwhiteboard_Position::CustomSelector ? 'checked="checked"' : ''; ?> />
                    <label for="CFWHITEBOARD_position_customselector">
                        <strong><?php _e('Custom Position.', 'cf-whiteboard'); ?></strong> <?php _e('For advanced use only. <a href="mailto:affiliatesupport@cfwhiteboard.com">Affiliate Support</a> would be happy to help you with this.', 'cf-whiteboard'); ?>
                    </label>
                    <fieldset <?php echo $options['position'] == cfwhiteboard_Position::CustomSelector ? '' : 'style="display:none;"'; ?> >
                        <ul>
                            <li>
                                <em><?php _e('All fields are required.', 'cf-whiteboard'); ?></em>
                            </li>
                            <li>
                                <label for="CFWHITEBOARD_position_customselectorinsertion"><?php _e('Insertion Method', 'cf-whiteboard'); ?></label>
                                <select id="CFWHITEBOARD_position_customselectorinsertion" name="CFWHITEBOARD_position_customselectorinsertion">
                                    <option value="<?php echo esc_attr( cfwhiteboard_Position::CustomSelectorInsertionAppend ); ?>" <?php echo $options['position_customselectorinsertion'] == cfwhiteboard_Position::CustomSelectorInsertionAppend ? 'selected="selected"' : ''; ?> >Append To</option>
                                    <option value="<?php echo esc_attr( cfwhiteboard_Position::CustomSelectorInsertionPrepend ); ?>" <?php echo $options['position_customselectorinsertion'] == cfwhiteboard_Position::CustomSelectorInsertionPrepend ? 'selected="selected"' : ''; ?> >Prepend To</option>
                                    <option value="<?php echo esc_attr( cfwhiteboard_Position::CustomSelectorInsertionBefore ); ?>" <?php echo $options['position_customselectorinsertion'] == cfwhiteboard_Position::CustomSelectorInsertionBefore ? 'selected="selected"' : ''; ?> >Insert Before</option>
                                    <option value="<?php echo esc_attr( cfwhiteboard_Position::CustomSelectorInsertionAfter ); ?>" <?php echo $options['position_customselectorinsertion'] == cfwhiteboard_Position::CustomSelectorInsertionAfter ? 'selected="selected"' : ''; ?> >Insert After</option>
                                </select>
                            </li>
                            <li>
                                <label for="CFWHITEBOARD_position_customselectortarget"><?php _e('Target Selector', 'cf-whiteboard'); ?></label>
                                <input id="CFWHITEBOARD_position_customselectortarget" type="text" name="CFWHITEBOARD_position_customselectortarget" value="<?php echo esc_attr( $options['position_customselectortarget'] ); ?>" size="50" />
                            </li>
                            <li>
                                <label for="CFWHITEBOARD_position_customselectorparent"><?php _e('Parent Selector', 'cf-whiteboard'); ?></label>
                                <input id="CFWHITEBOARD_position_customselectorparent" type="text" name="CFWHITEBOARD_position_customselectorparent" value="<?php echo esc_attr( $options['position_customselectorparent'] ); ?>" size="50" />
                            </li>
                            <li>
                                <label for="CFWHITEBOARD_position_customselectoralignment"><?php _e('Alignment', 'cf-whiteboard'); ?></label>
                                <select id="CFWHITEBOARD_position_customselectoralignment" name="CFWHITEBOARD_position_customselectoralignment">
                                    <option value="<?php echo esc_attr( cfwhiteboard_Position::CustomSelectorAlignmentFloatLeft ); ?>" <?php echo $options['position_customselectoralignment'] == cfwhiteboard_Position::CustomSelectorAlignmentFloatLeft ? 'selected="selected"' : ''; ?> >Float Left</option>
                                    <option value="<?php echo esc_attr( cfwhiteboard_Position::CustomSelectorAlignmentFloatRight ); ?>" <?php echo $options['position_customselectoralignment'] == cfwhiteboard_Position::CustomSelectorAlignmentFloatRight ? 'selected="selected"' : ''; ?> >Float Right</option>
                                    <option value="<?php echo esc_attr( cfwhiteboard_Position::CustomSelectorAlignmentInline ); ?>" <?php echo $options['position_customselectoralignment'] == cfwhiteboard_Position::CustomSelectorAlignmentInline ? 'selected="selected"' : ''; ?> >Inline</option>
                                    <option value="<?php echo esc_attr( cfwhiteboard_Position::CustomSelectorAlignmentBlock ); ?>" <?php echo $options['position_customselectoralignment'] == cfwhiteboard_Position::CustomSelectorAlignmentBlock ? 'selected="selected"' : ''; ?> >Block</option>
                                </select>
                            </li>
                            <li>
                                <label for="CFWHITEBOARD_position_customselectormargin"><?php _e('Margin', 'cf-whiteboard'); ?></label>
                                <input id="CFWHITEBOARD_position_customselectormargin" type="text" name="CFWHITEBOARD_position_customselectormargin" value="<?php echo esc_attr( $options['position_customselectormargin'] ); ?>" size="50" />
                            </li>
                        </ul>
                    </fieldset>
                </li>
            </ul>
        </fieldset>

<!--
        <fieldset>
            <legend><?php _e('WOD Blog Category', 'cf-whiteboard'); ?></legend>
            <h1><?php _e('Which post categories should the whiteboard be added to?', 'cf-whiteboard'); ?></h1>
            <ul>
                <?php foreach($categories as $category) { ?>
                    <li>
                        <input type="checkbox" id="<?php echo $category_prefix . $category->cat_ID; ?>" name="<?php echo $category_prefix . $category->cat_ID; ?>" <?php echo $category->selected ? 'checked="checked"' : ''; ?> />
                        <label for="<?php echo $category_prefix . $category->cat_ID; ?>">
                            <?php echo $category->cat_name; ?>
                        </label>
                    </li>
                <?php } ?>
            </ul>
        </fieldset>
-->

        <p class="submit">
            <input class="button-primary" type="submit" name="Submit" value="<?php esc_attr_e('Save Changes', 'cf-whiteboard' ); ?>" />
            <!-- <input id="A2A_SHARE_SAVE_reset_options" type="submit" name="Reset" onclick="return confirm('<!php _e('Are you sure you want to delete all CF Whiteboard options?', 'add-to-any' ) ?>')" value="<!php _e('Reset', 'add-to-any' ) ?>" /> -->
        </p>
    
    </form>
        
    <p><strong><?php _e('Need support?','cf-whiteboard'); ?></strong> <a href="mailto:affiliatesupport@cfwhiteboard.com"><?php _e('Email Us', 'cf-whiteboard'); ?></a></p>
    
    </div>

<?php
 
}

function cfwhiteboard_add_menu_link() {
	if( current_user_can('manage_options') ) {
		$page = add_options_page(
			__('CF Whiteboard ', 'cf-whiteboard') . __("Settings"),
			'CF Whiteboard',
			'activate_plugins',
			basename(__FILE__),
			'cfwhiteboard_options_page'
		);
		
		/* Using registered $page handle to hook script load, to only load in AddToAny admin */
        // add_filter('admin_print_scripts-' . $page, 'A2A_SHARE_SAVE_scripts');
	}
}
add_filter('admin_menu', 'cfwhiteboard_add_menu_link');


// Place in Option List on Settings > Plugins page 
function cfwhiteboard_actlinks($links, $file){
    // Static so we don't call plugin_basename on every plugin row.
    static $this_plugin;
    if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
    
    if ( $file == $this_plugin ){
        $support_link = '<a href="mailto:affiliatesupport@cfwhiteboard.com">' . __('Support') . '</a>';
        $settings_link = '<a href="options-general.php?page=cf-whiteboard.php">' . __('Settings') . '</a>';

        array_unshift( $links, $support_link ); // before other links
        array_unshift( $links, $settings_link ); // before other links
    }
    return $links;
}
add_filter("plugin_action_links", 'cfwhiteboard_actlinks', 10, 2);



// Custom Meta Boxes on Add/Edit Post page
global $CFWHITEBOARD_WODS_META_KEY;
$CFWHITEBOARD_WODS_META_KEY = 'cfwhiteboard-wods';
add_action('load-post.php', 'cfwhiteboard_setup_post_meta_boxes');
add_action('load-post-new.php', 'cfwhiteboard_setup_post_meta_boxes');
/* Save post meta on the 'save_post' hook. */
add_action('save_post', 'cfwhiteboard_save_post_meta_boxes', 10, 2);
/* Clean post meta on the 'publish_post' hook. */
add_action('publish_post', 'cfwhiteboard_clean_post_meta', 10, 1);

/* Meta box setup function. */
function cfwhiteboard_setup_post_meta_boxes() {
    /* Add meta boxes on the 'add_meta_boxes' hook. */
    add_action('add_meta_boxes', 'cfwhiteboard_add_post_meta_boxes', 1);
}

/* Create one or more meta boxes to be displayed on the post editor screen. */
function cfwhiteboard_add_post_meta_boxes() {
    add_meta_box(
        'cfwhiteboard-wods-meta', // Unique ID
        esc_html__('CF Whiteboard', 'cf-whiteboard'),      // Title
        'cfwhiteboard_wods_meta_box',     // Callback function
        'post',                 // Admin page (or post type)
        'normal',                 // Context
        'core'                   // Priority
    );
}

/* Generate the fields for a "class" in the post meta box. */
function cfwhiteboard_generate_class_fields($class) {
    $class_id = esc_attr( $class['wp_id'] );
    $class_prefix = 'cfwhiteboard-wod-' . $class_id;
    $class_name_attr = $class_prefix . '-name';

    $component_fields = '';
    foreach ($class['components'] as $component) {
        $component_fields .= cfwhiteboard_generate_class_component_fields($class_prefix . '-cmp-', $component);
    }

    return '
    <table class="'. (count($class['components']) > 1 ? 'multi-component' : 'single-component') .'">
        <thead>
            <tr>
                <th>
                    <label for="'. $class_name_attr .'">
                        '. esc_html__("Class Name", 'cf-whiteboard') .'
                    </label>
                </th>
                <td>
                    <a href="javascript://" class="remove-class delete">
                        &ndash; '. esc_html__("Remove This Class", 'cf-whiteboard') .'
                    </a>
                    <input type="text" id="'. $class_name_attr .'" name="'. $class_name_attr .'"
                        value="'. esc_attr( $class['name'] ) .'" />
                </td>
            </tr>
        </thead>
    '. $component_fields .'
    </table>
    <div class="tools">
        <a href="javascript://" class="add-component" data-classid="'. $class_id .'">
            + '. esc_html__("Track Another Component", 'cf-whiteboard') .'
        </a>
    </div>
    ';
}
function cfwhiteboard_generate_class_component_fields($component_prefix, $component) {
    $cmp_id = esc_attr( $component['wp_id'] );
    $description_name = $component_prefix . $cmp_id . '-description';
    $label_name = $component_prefix . $cmp_id . '-label';

    $has_label = !empty($component['label']);

    return '
        <tbody>
            <tr>
                <th>
                    <label for="'. $description_name .'">
                        '. esc_html__("Component", 'cf-whiteboard') .' <span class="component-index"></span>
                    </label>
                    <p>
                        '. esc_html__("Include rep scheme & loading", 'cf-whiteboard') .'
                    </p>
                    <p>
                        '. esc_html__("Break strength/skill work into separate components if you want athletes to track them individually", 'cf-whiteboard') .'
                    </p>
                </th>
                <td>
                    <textarea id="'. $description_name .'" name="'. $description_name .'" class="widefat" rows="5">'.
                        esc_textarea( $component['description'] )
                    .'</textarea>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="'. $label_name .'" '. ($has_label ? '' : 'class="hidden"') .'>
                        '. esc_html__("Component Label", 'cf-whiteboard') .'
                    </label>
                </th>
                <td>
                    <span class="component-label-show '. ($has_label ? '' : 'hidden') .'">
                        <span>'. esc_html( $component['label'] ) .'</span>
                        <a href="javascript://" class="button edit">'. esc_html__("Edit") .'</a>
                    </span>
                    <span class="component-label-edit hidden">
                        <input type="text" id="'. $label_name .'" name="'. $label_name .'"
                            value="'. esc_attr( $component['label'] ) .'" />
                        <a href="javascript://" class="button save">'. esc_html__("OK") .'</a>
                        <a href="javascript://" class="cancel">'. esc_html__("Cancel") .'</a>
                    </span>
                    <a href="javascript://" class="multi-component remove-component delete">
                        &ndash; '. esc_html__("Remove This Component", 'cf-whiteboard') .'
                    </a>
                </td>
            </tr>
        </tbody>
    ';
}
function cfwhiteboard_wods_meta_box($object, $box) {
    global $CFWHITEBOARD_WODS_META_KEY; ?>

    <?php wp_nonce_field(basename( __FILE__ ), $CFWHITEBOARD_WODS_META_KEY); ?>
    <style type="text/css">
        #cfwhiteboard-wods-meta {
            margin-top: 30px;
            margin-bottom: 40px;
        }
        #cfwhiteboard-wods-meta .inside {
            background: #555;
            -webkit-border-bottom-left-radius: inherit;
            -moz-border-radius-bottomleft: inherit;
            border-radius-bottom-left: inherit;
            -webkit-border-bottom-right-radius: inherit;
            -moz-border-radius-bottomright: inherit;
            border-radius-bottom-right: inherit;
            -webkit-box-shadow: inset 0 1px 6px rgba(0,0,0,.6);
            -moz-box-shadow: inset 0 1px 6px rgba(0,0,0,.6);
            box-shadow: inset 0 1px 6px rgba(0,0,0,.6);
            margin: 0;
        }
        #cfwhiteboard-wods-meta p.help {
            color: #ddd;
            font-size: 11px;
            font-style: normal;
            line-height: 1.3;
            margin: 0;
            padding: 16px 20px 4px;
        }
        #cfwhiteboard-wods-meta p.help + p.help {
            padding-top: 4px;
            padding-bottom: 16px;
        }
        #cfwhiteboard-wods-meta p.help:hover {
            color: #fff;
        }
        #cfwhiteboard-wods-meta .hidden {
            display: none;
        }
        #cfwhiteboard-wods-meta ul {
            margin: 0;
            padding: 0 6px;
        }
        #cfwhiteboard-wods-meta ul li,
        #cfwhiteboard-wods-meta p.tools a {
            background: #f5f5f5;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            -webkit-box-shadow: 0 1px 6px rgba(0,0,0,.6);
            -moz-box-shadow: 0 1px 6px rgba(0,0,0,.6);
            box-shadow: 0 1px 6px rgba(0,0,0,.6);
        }
        #cfwhiteboard-wods-meta ul li {
            margin: 0 0 20px;
            padding: 0;
        }
        #cfwhiteboard-wods-meta ul li .tools {
            padding: 0;
            margin: 0;
            text-align: center;
        }
        #cfwhiteboard-wods-meta ul li .tools a {
            background: #f5f5f5;
            color: #333;
            display: block;
            font-weight: bold;
            line-height: 2;
            margin: 0;
            padding: 3px 0;
            text-align: center;
            text-decoration: none;
        }
        #cfwhiteboard-wods-meta ul li .tools a:hover,
        #cfwhiteboard-wods-meta ul li .tools a:focus {
            background: #fff;
            color: #0b4;
        }
        #cfwhiteboard-wods-meta ul li .tools a {
            -webkit-border-bottom-left-radius: 5px;
            -moz-border-radius-bottomleft: 5px;
            border-bottom-left-radius: 5px;
            -webkit-border-bottom-right-radius: 5px;
            -moz-border-radius-bottomright: 5px;
            border-bottom-right-radius: 5px;
        }
        #cfwhiteboard-wods-meta ul li table {
            border-collapse: separate;
            border-spacing: 0;
            margin: 0;
            width: 100%;
        }
        #cfwhiteboard-wods-meta ul li table thead th,
        #cfwhiteboard-wods-meta ul li table thead td,
        #cfwhiteboard-wods-meta ul li table tbody tr + tr th,
        #cfwhiteboard-wods-meta ul li table tbody tr + tr td {
            border-bottom: 1px solid #aaa;
            border-top: none;
        }
        #cfwhiteboard-wods-meta ul li table tbody tr th,
        #cfwhiteboard-wods-meta ul li table tbody tr td,
        #cfwhiteboard-wods-meta ul li .tools {
            border-top: 1px solid #fff;
        }
/*
        #cfwhiteboard-wods-meta ul li table.single-component tbody th,
        #cfwhiteboard-wods-meta ul li table.single-component tbody td {
            border-bottom: 1px solid #aaa;
        }
*/
        #cfwhiteboard-wods-meta ul li table tbody a.delete {
            font-size: 11px;
        }
        #cfwhiteboard-wods-meta ul li table tbody .component-label-show span {
            color: #666;
            font-family: sans-serif;
            line-height: 23px;
            font-size: 12px;
        }
        #cfwhiteboard-wods-meta ul li table tbody tr + tr th,
        #cfwhiteboard-wods-meta ul li table tbody tr + tr td {
            border-bottom: 1px solid #aaa;
            border-top: none;
            padding-top: 2px;
        }
        #cfwhiteboard-wods-meta ul li table th,
        #cfwhiteboard-wods-meta ul li table td {
            vertical-align: top;
        }
        #cfwhiteboard-wods-meta ul li table th {
            font-weight: normal;
            text-align: left;
            padding: 8px 10px;
            width: 30%;
        }
        #cfwhiteboard-wods-meta ul li table th label {
            display: block;
            font-weight: bold;
            line-height: 23px;
        }
        #cfwhiteboard-wods-meta ul li table tr:hover label {
            color: black;
        }
        #cfwhiteboard-wods-meta ul li table th p {
            color: #999;
            font-size: 11px;
            margin: 2px 0 0;
        }
        #cfwhiteboard-wods-meta ul li table th p + p {
            margin: 5px 0 0;
        }
        #cfwhiteboard-wods-meta ul li table td {
            padding: 8px 10px 8px 0;
            width: 70%;
        }
        #cfwhiteboard-wods-meta ul li table td input[type="text"] {
            margin: 0;
            width: 200px;
        }
        #cfwhiteboard-wods-meta ul li table td .component-label-edit input[type="text"] {
            width: 140px;
        }
        #cfwhiteboard-wods-meta ul li table td a.delete {
            color: #666;
            float: right;
            line-height: 23px;
            padding: 0 0 0 8px;
            text-decoration: none;
        }
        #cfwhiteboard-wods-meta ul li table td a.delete:hover,
        #cfwhiteboard-wods-meta ul li table td a.delete:focus {
            color: #d33;
        }
        #cfwhiteboard-wods-meta ul li table td textarea {
            display: block;
            margin: 0;
            outline: none;
            resize: vertical;
        }
        #cfwhiteboard-wods-meta ul li table.single-component .multi-component {
            display: none;
        }
        #cfwhiteboard-wods-meta ul li table.multi-component .single-component {
            display: none;
        }
        #cfwhiteboard-wods-meta p.tools {
            margin: 16px 0 0;
            padding: 0 6px 20px;
        }
        #cfwhiteboard-wods-meta p.tools a {
            display: inline-block;
            font-weight: bold;
            line-height: 36px;
            margin: 0;
            padding: 0 16px;
            text-decoration: none;
            *display: inline;
            *zoom: 1;
        }
        #cfwhiteboard-wods-meta p.tools a:hover,
        #cfwhiteboard-wods-meta p.tools a:focus {
            background: #fff;
            color: #0b4;
        }
        /* tinyMCE styling */
        a.mceAction.mce_cfwhiteboard_button {
            width: 93px !important;
        }
    </style>
    <p class="help">
        Add the classes that you want to track for today.  Each class will have a separate tab in the CF Whiteboard widget.
    </p>
    <p class="help">
        You can track multiple components for each class.  Each component will have a separate entry field so athletes can lookup their results separately for each component.
    </p>
    <ul>
        <?php
        $wods = get_post_meta($object->ID, $CFWHITEBOARD_WODS_META_KEY, true);

        $next_wod_id = 1;
        $next_component_id = 1;

        if (! is_array($wods)) {
            // no wods yet - instantiate empty array
            $wods = array();
            $wods[] = array(
                'name' => 'Workout of the Day',
                'components' => array(),
                'wp_id' => $next_wod_id++
            );
        }
        // else {
        //     // Gather the next_wod_id and next_component_id values
        //     $last_wod = end($new_wods);
        //     $next_wod_id = intval( $last_wod['wp_id'] ) + 10;

        //     $last_component = end( $last_wod['components'] );
        //     $next_component_id = intval( $last_component['wp_id'] ) + 10;
        // }

    
        foreach ($wods as $wod) {
            if ((!is_array( $wod['components'] )) || empty($wod['components'])) {
                $wod['components'] = array();
                $wod['components'][] = array(
                    'description' => '',
                    'label' => '',
                    'wp_id' => $next_component_id++
                );
            }

            ?>
            <li>
                <?php echo cfwhiteboard_generate_class_fields($wod); ?>
            </li>
            <?php

            $next_wod_id = max($next_wod_id, intval( $wod['wp_id'] ));
            $last_component = end($wod['components']);
            $next_component_id = max($next_component_id, intval( $last_component['wp_id'] ));
        }

        $next_wod_id += 10;
        $next_component_id += 10;
        ?>
    </ul>
    <p class="tools">
        <a href="javascript://" class="add-class">+ <?php esc_html_e("Track Another Class", 'cf-whiteboard'); ?></a>
    </p>
    <script type="text/javascript">
        typeof(jQuery) == 'function' && jQuery(function($) {
            // Don't let the script run more than once
            if (window.CFW) {
                return;
            }
            window.CFW = {};

            CFW.parseClassDescription = function($container) {
                var classDescription = {
                    name: $container.find('[name$="name"]').val(),
                    components: []
                }

                $container.find('tbody').each(function() {
                    var $tbody = $(this);
                    classDescription.components.push({
                        label: $tbody.find('[name$="label"]').val(),
                        description: $tbody.find('[name$="description"]').val()
                    });
                });

                return classDescription;
            };
            CFW.generateClassDescriptionMarkup = function(classDescription) {
                var linebreak = '\n';

                var markup = '';
                markup += '<strong>'+classDescription.name+'</strong>' + linebreak;
                var count = 0; // must count separately from index because components with blank descriptions are skipped
                for (var i = 0; i < classDescription.components.length; ++i) {
                    var trimmedDescription = classDescription.components[i].description.replace(/^\s+/,'').replace(/\s+$/,'');
                    if (!trimmedDescription) continue;

                    if (count++ > 0) markup += '<em>then</em>' + linebreak;
                    markup += classDescription.components[i].description + linebreak;
                }

                return markup + linebreak;
            };
            CFW.newlineToBr = function(str) {
                return str.replace(/\r\n/g, '<br />').replace(/\r/g, '<br />').replace(/\n/g, '<br />');
            };


            // Move the CF Whiteboard meta box just under the Post Title
            $('#titlediv').after( $('#cfwhiteboard-wods-meta') );


            // Update component numbers
            CFW.updateComponentNumbers = function($container) {
                $container.find('tbody label span').each(function(index) {
                    $(this).text(index + 1); // component numbers start at 1
                });
            };
            // intial numbering
            $('#cfwhiteboard-wods-meta li').each(function() {
                CFW.updateComponentNumbers( $(this) );
            });


            // Component labels logic:
            // Guess component labels
            CFW.guessComponentLabel = function(componentDescription) {
                var matches = componentDescription.replace(/^\s+/, '').replace(/\s+$/, '').match(/[^\r\n]+/);
                if (!matches) return '';

                return matches[0].replace(/^\s+/, '').replace(/:\s*$/, '');
            };
            $('#cfwhiteboard-wods-meta textarea').live('keyup blur paste input textInput', function() {
                var $textarea = $(this);
                var $labelInput = $textarea.closest('tbody').find('.component-label-edit input');

                var guess = CFW.guessComponentLabel( $textarea.val() );

                if (!guess) {
                    // Resume guessing component labels if the text was cleared
                    $labelInput.removeClass('owned-by-user');
                }

                $labelInput.not('.owned-by-user').val(guess).change();
            });
            // Mirror the label input, maybe show the component field & label
            $('#cfwhiteboard-wods-meta .component-label-edit input').live('change', function() {
                var $input = $(this);
                var val = $input.val();
                var $tr = $input.closest('tr');

                // Display field should mirror the text input
                $tr.find('.component-label-show span').text(val || '(not yet labeled)');

                // Logic for showing the component label field (& label) once it has a value
                if (val) {
                    $tr.find('label').removeClass('hidden');
                    $tr.find('.component-label-edit.hidden').siblings('.component-label-show').removeClass('hidden');
                }
            }).live('keypress', function(event) {
                // If they press enter,
                if (event.which == 13) {
                    // 1: Block the form from being submitted
                    event.preventDefault();
                    // 2: Save the label by clicking 'ok' for them
                    $(this).closest('.component-label-edit').find('.save').click();
                }
            });
            // Logic for edit/ok/cancel
            $('#cfwhiteboard-wods-meta .component-label-show .edit').live('click', function() {
                var $stuff = $(this).closest('tr').find('.component-label-show, .component-label-edit').toggleClass('hidden');

                // cache the current value.  if they click cancel, we want to be able to restore it.
                var $input = $stuff.find('input');
                $input.data('valuebeforeedit', $input.val());
                var input = $input[0];
                if (input.focus) input.focus();
                if (input.select) input.select();
            });
            $('#cfwhiteboard-wods-meta .component-label-edit .save').live('click', function() {
                var $this = $(this);

                var $input = $this.siblings('input');
                if (/^\s*$/.test($input.val())) {
                    $input.removeClass('owned-by-user').change();
                } else {
                    // Stop guessing component labels
                    // Trigger mirror to update (on 'change')
                    $input.addClass('owned-by-user').change();
                }

                $this.closest('tr').find('.component-label-show, .component-label-edit').toggleClass('hidden');
            });
            $('#cfwhiteboard-wods-meta .component-label-edit .cancel').live('click', function() {
                var $this = $(this);

                // restore the cached input value
                var $input = $this.siblings('input');
                $input.val($input.data('valuebeforeedit')).change();

                $this.closest('tr').find('.component-label-show, .component-label-edit').toggleClass('hidden');
            });


            // QuickTag Button for HTML editor
            if (QTags && QTags.addButton && !CFW.edButtonAdded) {
                QTags.addButton(
                    'cfw_wods', // id
                    'CF Whiteboard', // display
                    function(button){
                        var $button = $(button);

                        // Gather class names/descriptions
                        var classes = [];
                        var tempClass;
                        jQuery('#cfwhiteboard-wods-meta li').each(function() {
                            tempClass = CFW.parseClassDescription( jQuery(this) );

                            var hasDescription = false;
                            for (var i = 0; i < tempClass.components.length; i++) {
                                if (tempClass.components[i].description.replace(/^\s+/,'').replace(/\s+$/,'')) {
                                    hasDescription = true;
                                    break;
                                }
                            }
                            // only add classes that have descriptions
                            if (!hasDescription) return;

                            // Generate the markup for the class description
                            tempClass.markup = CFW.generateClassDescriptionMarkup(tempClass);

                            classes.push( tempClass );
                        });

                        var options = [];
                        if (!classes.length) {
                            // no classes entered, provide link to CFW meta box
                            options.push({
                                text: 'No workouts. Click to add one.',
                                handler: function() {
                                    window.location = window.location.origin + window.location.pathname + '#cfwhiteboard-wods-meta';
                                    var $metaBox = jQuery('#cfwhiteboard-wods-meta');
                                    if ($metaBox.find('ul:visible').length == 0) {
                                        $metaBox.find('.handlediv').click();
                                    }
                                }
                            });
                        } else if (classes.length > 1) {
                            // multiple classes entered, provide menu item for inserting all at once

                            var markupAll = '';
                            for (var i = 0; i < classes.length; i++) {
                                markupAll += classes[i].markup;
                            }

                            options.push({
                                text: 'Insert All Classes',
                                handler: function() {
                                    QTags.insertContent( markupAll );
                                }
                            });
                        }

                        var generateInsertMarkupFunc = function(markup) {
                            return function() {
                                QTags.insertContent( markup );
                            };
                        };
                        for (var i = 0; i < classes.length; i++) {
                            options.push({
                                text: 'Insert '+classes[i].name,
                                handler: generateInsertMarkupFunc( classes[i].markup )
                            });
                        }

                        var $select = $('<select />').css({
                            'margin': '2px 1px 0 0',
                            'line-height': '18px',
                            'min-width': '26px',
                            'padding': '2px 4px',
                            'font': '12px/18px Arial,Helvetica,sans-serif normal',
                            'color': '#464646',
                            '-webkit-border-radius': '3px',
                            '-moz-border-radius': '3px',
                            'background-color': '#EEE',
                            'background-image': '-webkit-linear-gradient(bottom,#e3e3e3,#fff)',
                            'background-image': '-moz-linear-gradient(bottom,#e3e3e3,#fff)',
                            'background-image': '-ms-linear-gradient(bottom,#e3e3e3,#fff)',
                            'background-image': '-o-linear-gradient(bottom,#e3e3e3,#fff)',
                            'background-image': 'linear-gradient(bottom,#e3e3e3,#fff)',
                            'border': '1px solid #BBB',
                            'border-radius': '3px',
                            'vertical-align': 'top'
                        }).change(function() {
                            var i = parseInt($select.val(), 10);
                            options[i] && options[i].handler && options[i].handler();
                            $select.remove();
                        });

                        $select.append(
                            $('<option selected="selected">Insert Workout(s)</option>')
                        );
                        $.each(options, function(i) {
                            $select.append(
                                $('<option value="'+ i +'">'+ this.text +'</option>')
                            );
                        });

                        $button.after($select);
                    }, // arg1
                    function(){}, // arg2
                    'w', // access_key
                    'Insert Workout Description(s)', // title
                    140 // priority
                );
                CFW.edButtonAdded = true;
            }


            // Add / Remove Classes
            var class_template = '<?php
                $empty_wod = array(
                    "name" => "",
                    "components" => array(
                        array(
                            "description" => "",
                            "label" => "",
                            "wp_id" => "#c#"
                        )
                    ),
                    "wp_id" => "#w#"
                );
                echo str_replace(array("\r\n", "\n", "\r"), "", cfwhiteboard_generate_class_fields($empty_wod) );
            ?>';
            var next_class_id = <?php esc_attr_e( $next_wod_id ); ?>;
            var next_component_id = <?php esc_attr_e( $next_component_id ); ?>;
            $('a.add-class').click(function() {
                var $ul = $('#cfwhiteboard-wods-meta ul');
                var html = class_template.replace(/#w#/g, next_class_id++).replace(/#c#/g, next_component_id++);
                var $new = $('<li />').html(html);
                $ul.append( $new );

                CFW.updateComponentNumbers( $new );
            });
            $('a.remove-class').live('click', function() {
                if (confirm('remove this class?')) {
                    $(this).closest('li').remove();
                }
            });


            // Add / Remove Components of Classes
            function updateSingleMultiComponent($table) {
                if ($table.find('tbody').length > 1) {
                    $table.removeClass('single-component').addClass('multi-component');
                } else {
                    $table.removeClass('multi-component').addClass('single-component');
                }
            }

            var component_template = '<?php
                $empty_component = array(
                    "description" => "",
                    "label" => "",
                    "wp_id" => "#c#"
                );
                echo str_replace(array("\r\n", "\n", "\r"), "", cfwhiteboard_generate_class_component_fields("cfwhiteboard-wod-#w#-cmp-", $empty_component));
            ?>';
            $('a.add-component').live('click', function() {
                var $this = $(this);
                var $table = $this.closest('li').find('table');
                var html = component_template.replace(/#w#/g, $this.data('classid')).replace(/#c#/g, next_component_id++);
                var $new = $(html);
                $table.append( $new );

                updateSingleMultiComponent($table);

                CFW.updateComponentNumbers($table);
            });
            $('a.remove-component').live('click', function() {
                if (confirm('remove this component?')) {
                    var $this = $(this);
                    var $table = $this.closest('table');
                    $this.closest('tbody').remove();

                    updateSingleMultiComponent($table);

                    CFW.updateComponentNumbers($table);
                }
            });
        });
    </script>

<?php

}

add_filter('mce_buttons_2', 'cfwhiteboard_mce_buttons');
add_filter('mce_external_plugins', 'cfwhiteboard_mce_external_plugins');
function cfwhiteboard_mce_buttons( $buttons ) {

    array_unshift( $buttons, 'cfwhiteboard_button', '|' );

    return $buttons;
}
function cfwhiteboard_mce_external_plugins( $plugins ) {
    
    $plugins['CfWhiteboard'] = plugins_url('cfwhiteboard-post-editor/mce-plugin.js' , __FILE__);
    
    return $plugins;
}

/* Save the meta box's post metadata. */
function cfwhiteboard_save_post_meta_boxes($post_id, $post) {
    global $CFWHITEBOARD_WODS_META_KEY;

    /* Verify the nonce before proceeding. */
    if ( !isset( $_POST[$CFWHITEBOARD_WODS_META_KEY] ) || !wp_verify_nonce( $_POST[$CFWHITEBOARD_WODS_META_KEY], basename( __FILE__ ) ) )
        return $post_id;

    /* Get the post type object. */
    $post_type = get_post_type_object( $post->post_type );

    /* Check if the current user has permission to edit the post. */
    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
        return $post_id;

    /* Get the posted data. */
    $param_prefix = 'cfwhiteboard-wod-';
    $param_prefix_len = strlen( $param_prefix );

    $new_wods = array();
    foreach ($_POST as $name => $value) {

        $name_prefix = substr($name, 0, $param_prefix_len);
        if (strcmp($name_prefix, $param_prefix) == 0) {
            $name = substr($name, $param_prefix_len);
            $name = explode('-', $name);

            $wod_id = $name[0];

            if (!is_array( $new_wods[$wod_id] )) {
                $new_wods[$wod_id] = array(
                    'name' => '',
                    'components' => array(),
                    'wp_id' => $wod_id
                );
            }

            if (strcmp($name[1], 'name') == 0) {
                // cfwhiteboard-wod-<wod_id>-name
                $new_wods[$wod_id]['name'] = $value;

            } elseif (strcmp($name[1], 'cmp') == 0) {
                // cfwhiteboard-wod-<wod_id>-cmp-<component_id>-...
                $component_id = $name[2];

                if (!is_array( $new_wods[$wod_id]['components'][$component_id] )) {
                    $new_wods[$wod_id]['components'][$component_id] = array(
                        'description' => '',
                        'label' => '',
                        'wp_id' => $component_id
                    );
                }

                if (strcmp($name[3], 'description') == 0) {
                    // cfwhiteboard-wod-<wod_id>-cmp-<component_id>-description
                    $new_wods[$wod_id]['components'][$component_id]['description'] = $value;

                } elseif (strcmp($name[3], 'label') == 0) {
                    // cfwhiteboard-wod-<wod_id>-cmp-<component_id>-label
                    $new_wods[$wod_id]['components'][$component_id]['label'] = $value;

                }
            }
        }

    }

    if (get_post_status($post_id) == "publish") {
        $new_wods = cfwhiteboard_clean_post_meta($new_wods);
    } else {
        // reindex wods and their components
        $temp_wods = array();
        foreach ($new_wods as $wod) {

            $temp_cmps = array();
            foreach ($wod['components'] as $component) {
                $temp_cmps[] = $component;
            }

            $wod['components'] = $temp_cmps;
            $temp_wods[] = $wod;
        }    
        $new_wods = $temp_wods;
    }

    /* Update or Delete the meta value of the custom field key. */
    if (! empty($new_wods)) {
        update_post_meta($post_id, $CFWHITEBOARD_WODS_META_KEY, $new_wods);
    } else {
        delete_post_meta($post_id, $CFWHITEBOARD_WODS_META_KEY);
    }
}
function cfwhiteboard_clean_post_meta( $wods ) {
    // Clean the meta values (no blank fields)
    $next_id = 10000;
    $temp_wods = array();
    foreach ($wods as $wod) {

        $temp_cmps = array();
        foreach ($wod['components'] as $component) {
            if (empty($component['label']) || empty($component['description'])) continue;
            if (empty($component['wp_id'])) $component['wp_id'] = $next_id++;
            $temp_cmps[] = $component;
        }
        $wod['components'] = $temp_cmps;

        if (empty($wod['name']) || empty($wod['components'])) continue;
        if (empty($wod['wp_id'])) $wod['wp_id'] = $next_id++;
        $temp_wods[] = $wod;
    }

    return $temp_wods;
}


function cfwhiteboard_json_meta() {
    global $CFWHITEBOARD_WODS_META_KEY;
    
    $query_var = cfwhiteboard_get_query_var('cfwhiteboard_post_id');
    if (!$query_var) return;

    $post = get_post($query_var);

    $response = array(
        'post_id' => $post->ID,
        'post_modified' => $post->post_modified,
        'meta' => get_post_meta($query_var, $CFWHITEBOARD_WODS_META_KEY, true)
    );

    header('HTTP/1.1 200 OK', true);
    header('Content-Type: application/json; charset=UTF-8', true);
    // echo cfwhiteboard_strip_magic_quotes($response);
    echo json_encode($response);
    
    exit;
}
function cfwhiteboard_get_query_var($key) {
    $wp_query_var = get_query_var($key);
    if ($wp_query_var) {
      return $wp_query_var;
    }
    
    return isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
}
function cfwhiteboard_strip_magic_quotes($value) {
    if (get_magic_quotes_gpc()) {
        return stripslashes($value);
    } else {
        return $value;
    }
}
if (!function_exists('json_encode'))
{
  function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}


?>
