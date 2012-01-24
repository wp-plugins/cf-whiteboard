<?php
/*
Plugin Name: CF Whiteboard
Plugin URI: http://cfwhiteboard.com
Description: Connects CF Whiteboard to your blog.
Version: 1.8
Author: CF Whiteboard
*/
$CFWHITEBOARD_VERSION = '1.8';

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

$CFWHITEBOARD_DEFAULT_OPTIONS = array();
$CFWHITEBOARD_DEFAULT_OPTIONS['affiliate_id'] = str_replace(array('http://', 'https://', 'www.', '.com/', '.com'), '', home_url());
$CFWHITEBOARD_DEFAULT_OPTIONS['visibility'] = cfwhiteboard_Visibility::Users;
$CFWHITEBOARD_DEFAULT_OPTIONS['position'] = cfwhiteboard_Position::TitleRight;
$CFWHITEBOARD_DEFAULT_OPTIONS['position_customselectorinsertion'] = cfwhiteboard_Position::CustomSelectorInsertionAppend;
$CFWHITEBOARD_DEFAULT_OPTIONS['position_customselectortarget'] = '';
$CFWHITEBOARD_DEFAULT_OPTIONS['position_customselectorparent'] = '';
$CFWHITEBOARD_DEFAULT_OPTIONS['position_customselectoralignment'] = cfwhiteboard_Position::CustomSelectorAlignmentFloatLeft;
$CFWHITEBOARD_DEFAULT_OPTIONS['position_customselectormargin'] = '0';
$CFWHITEBOARD_DEFAULT_OPTIONS['categories'] = array();

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

    // assume $option['visibility'] == cfwhiteboard_Visibility::Everyone
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

function cfwhiteboard_is_proper_post($id = -1, $options) {
    $currentId = get_the_ID();
    

    return ($id == $currentId) &&
        !is_feed() &&
        !is_page() &&
        in_the_loop() &&
        (get_post_type() == 'post') &&
        (
            empty($options['categories']) ||
            in_category($options['categories'])
        );
}

function cfwhiteboard_generate_placeholder($post_id, $options) {
    if (empty($options) || empty($options['affiliate_id'])) {
        $options = cfwhiteboard_get_options();
    }

    $affiliateId = !empty($options['affiliate_id']) ? $options['affiliate_id'] : 'testaffiliate';
    if (cfwhiteboard_is_preview_mode($options)) $affiliateId .= '_preview';
    
    $authorization = is_user_logged_in() ? 'data-authorization="admin"' : '';

    return '<div class="cfwhiteboard cleanslate" data-affiliate-id="'. $affiliateId .'" data-post-id="'. $post_id .'" '. $authorization .'></div>';
}


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


    if (! cfwhiteboard_is_proper_post($id, $options))
        return $titleOrContent;
    
    $cfw_placeholder = cfwhiteboard_generate_placeholder(get_the_ID(), $options);
	return $cfw_placeholder . $titleOrContent;
}

function cfwhiteboard_stylesheet() {
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
        $new_options['categories'] = array();
        $categories = get_categories($category_args);
        foreach($categories as $category) {
            if (!empty($_POST[$category_prefix . $category->cat_ID])) {
                array_push($new_options['categories'], $category->cat_ID);
            }
        } 

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
    $category_args = array(
        'orderby' => 'name',
        'hide_empty' => 0
    );
    $categories = get_categories($category_args);
    foreach($categories as $category) {
        $category->selected = empty($options['categories']) ? true : in_array($category->cat_ID, $options['categories']);
    } 

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
            <input type="text" id="CFWHITEBOARD_affiliate_id" name="CFWHITEBOARD_affiliate_id" value="<?php echo $options['affiliate_id']; ?>" />
            <label for="CFWHITEBOARD_affiliate_id">
                <?php _e('(Changing this value could result in data loss.)', 'cf-whiteboard') ?>
            </label>
        </p>
        
        <fieldset>
            <legend><?php _e('Visibility Mode', 'cf-whiteboard'); ?></legend>
            <ul>
                <li>
                    <input type="radio" id="CFWHITEBOARD_visibility_users" name="CFWHITEBOARD_visibility" value="<?php echo cfwhiteboard_Visibility::Users; ?>" <?php echo $options['visibility'] == cfwhiteboard_Visibility::Users ? 'checked="checked"' : ''; ?> />
                    <label for="CFWHITEBOARD_visibility_users">
                        <strong><?php _e('Preview Mode.', 'cf-whiteboard') ?></strong> <?php _e('Athletes cannot see the whiteboard. Only logged-in WordPress users can see the whiteboard.  Whiteboard entries will be removed when you switch to Live Mode, so feel free to experiment.', 'cf-whiteboard') ?>
                    </label>
                </li>
                <li>
                    <input type="radio" id="CFWHITEBOARD_visibility_everyone" name="CFWHITEBOARD_visibility" value="<?php echo cfwhiteboard_Visibility::Everyone; ?>" <?php echo $options['visibility'] == cfwhiteboard_Visibility::Everyone ? 'checked="checked"' : ''; ?> />
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
                    <input type="radio" id="CFWHITEBOARD_position_titleright" name="CFWHITEBOARD_position" value="<?php echo cfwhiteboard_Position::TitleRight; ?>" <?php echo $options['position'] == cfwhiteboard_Position::TitleRight ? 'checked="checked"' : ''; ?> />
                    <label for="CFWHITEBOARD_position_titleright">
                        <strong><?php _e('Default Position.', 'cf-whiteboard'); ?></strong> <?php _e('The whiteboard is positioned to the right of the post title.', 'cf-whiteboard') ?>
                    </label>
                </li>
                <li>
                    <input type="radio" id="CFWHITEBOARD_position_customselector" name="CFWHITEBOARD_position" value="<?php echo cfwhiteboard_Position::CustomSelector; ?>" <?php echo $options['position'] == cfwhiteboard_Position::CustomSelector ? 'checked="checked"' : ''; ?> />
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
                                    <option value="<?php echo cfwhiteboard_Position::CustomSelectorInsertionAppend; ?>" <?php echo $options['position_customselectorinsertion'] == cfwhiteboard_Position::CustomSelectorInsertionAppend ? 'selected="selected"' : ''; ?> >Append To</option>
                                    <option value="<?php echo cfwhiteboard_Position::CustomSelectorInsertionPrepend; ?>" <?php echo $options['position_customselectorinsertion'] == cfwhiteboard_Position::CustomSelectorInsertionPrepend ? 'selected="selected"' : ''; ?> >Prepend To</option>
                                    <option value="<?php echo cfwhiteboard_Position::CustomSelectorInsertionBefore; ?>" <?php echo $options['position_customselectorinsertion'] == cfwhiteboard_Position::CustomSelectorInsertionBefore ? 'selected="selected"' : ''; ?> >Insert Before</option>
                                    <option value="<?php echo cfwhiteboard_Position::CustomSelectorInsertionAfter; ?>" <?php echo $options['position_customselectorinsertion'] == cfwhiteboard_Position::CustomSelectorInsertionAfter ? 'selected="selected"' : ''; ?> >Insert After</option>
                                </select>
                            </li>
                            <li>
                                <label for="CFWHITEBOARD_position_customselectortarget"><?php _e('Target Selector', 'cf-whiteboard'); ?></label>
                                <input id="CFWHITEBOARD_position_customselectortarget" type="text" name="CFWHITEBOARD_position_customselectortarget" value="<?php echo $options['position_customselectortarget']; ?>" size="50" />
                            </li>
                            <li>
                                <label for="CFWHITEBOARD_position_customselectorparent"><?php _e('Parent Selector', 'cf-whiteboard'); ?></label>
                                <input id="CFWHITEBOARD_position_customselectorparent" type="text" name="CFWHITEBOARD_position_customselectorparent" value="<?php echo $options['position_customselectorparent']; ?>" size="50" />
                            </li>
                            <li>
                                <label for="CFWHITEBOARD_position_customselectoralignment"><?php _e('Alignment', 'cf-whiteboard'); ?></label>
                                <select id="CFWHITEBOARD_position_customselectoralignment" name="CFWHITEBOARD_position_customselectoralignment">
                                    <option value="<?php echo cfwhiteboard_Position::CustomSelectorAlignmentFloatLeft; ?>" <?php echo $options['position_customselectoralignment'] == cfwhiteboard_Position::CustomSelectorAlignmentFloatLeft ? 'selected="selected"' : ''; ?> >Float Left</option>
                                    <option value="<?php echo cfwhiteboard_Position::CustomSelectorAlignmentFloatRight; ?>" <?php echo $options['position_customselectoralignment'] == cfwhiteboard_Position::CustomSelectorAlignmentFloatRight ? 'selected="selected"' : ''; ?> >Float Right</option>
                                    <option value="<?php echo cfwhiteboard_Position::CustomSelectorAlignmentInline; ?>" <?php echo $options['position_customselectoralignment'] == cfwhiteboard_Position::CustomSelectorAlignmentInline ? 'selected="selected"' : ''; ?> >Inline</option>
                                    <option value="<?php echo cfwhiteboard_Position::CustomSelectorAlignmentBlock; ?>" <?php echo $options['position_customselectoralignment'] == cfwhiteboard_Position::CustomSelectorAlignmentBlock ? 'selected="selected"' : ''; ?> >Block</option>
                                </select>
                            </li>
                            <li>
                                <label for="CFWHITEBOARD_position_customselectormargin"><?php _e('Margin', 'cf-whiteboard'); ?></label>
                                <input id="CFWHITEBOARD_position_customselectormargin" type="text" name="CFWHITEBOARD_position_customselectormargin" value="<?php echo $options['position_customselectormargin']; ?>" size="50" />
                            </li>
                        </ul>
                    </fieldset>
                </li>
            </ul>
        </fieldset>

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
                
        <p class="submit">
            <input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Changes', 'cf-whiteboard' ); ?>" />
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

?>
