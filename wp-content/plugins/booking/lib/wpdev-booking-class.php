<?php
if ( (! isset( $_GET['merchant_return_link'] ) ) && (! isset( $_GET['payed_booking'] ) ) && ( (! isset($_GET['pay_sys']) ) || ($_GET['pay_sys'] != 'authorizenet') ) && (!function_exists ('get_option'))   ) { die('You do not have permission to direct access to this file !!!'); }

if (!class_exists('wpdev_booking')) {
class wpdev_booking {

    // <editor-fold defaultstate="collapsed" desc="  C O N S T R U C T O R  &  P r o p e r t i e s ">

    var $icon_button_url;
    var $prefix;
    var $settings;
    var $wpdev_bk_personal;
    var $captcha_instance;

    function wpdev_booking() {

        // Add settings top line before all other menu items.
        add_bk_action('wpdev_booking_settings_top_menu', array($this, 'settings_menu_top_line'));
        add_bk_action('wpdev_booking_settings_show_content', array(&$this, 'settings_menu_content'));

        $this->captcha_instance = new wpdevReallySimpleCaptcha();
        $this->prefix = 'wpdev_bk';
        $this->settings = array(  'custom_buttons' =>array(),
                'custom_buttons_func_name_from_js_file' => 'set_bk_buttons', //Edit this name at the JS file of custom buttons
                'custom_editor_button_row'=>1 );
        $this->icon_button_url = WPDEV_BK_PLUGIN_URL . '/img/calendar-16x16.png';

        if ( class_exists('wpdev_bk_personal'))  $this->wpdev_bk_personal = new wpdev_bk_personal();
        else                                     $this->wpdev_bk_personal = false;

        // Create admin menu
        add_action('admin_menu', array(&$this, 'add_new_admin_menu'));

        // Client side print JSS
        add_action('wp_head',array(&$this, 'client_side_print_booking_head'));

        // Add custom buttons
        add_action( 'init',        'wpdev_bk_add_custom_buttons') ;
        add_action( 'admin_head',  'wpdev_bk_insert_wpdev_button');
                                    
        // Remove the scripts, which generated conflicts
        add_action('admin_init', array(&$this, 'wpdevbk_remove_conflict_scripts'), 999);

        // Set loading translation
        add_action('init', 'load_bk_Translation',1000);

        // Load footer data
        add_action( 'wp_footer', array(&$this,'wp_footer') );

        // User defined - hooks
        add_action( 'wpdev_bk_add_calendar', array(&$this,'add_calendar_action') ,10 , 2);
        add_action( 'wpdev_bk_add_form',     array(&$this,'add_booking_form_action') ,10 , 2);


        add_filter( 'wpdev_bk_get_form',     array(&$this,'get_booking_form_action') ,10 , 2);
        add_bk_filter( 'wpdevbk_get_booking_form', array(&$this, 'get_booking_form_action'));

        add_filter( 'wpdev_bk_get_showing_date_format',     array(&$this,'get_showing_date_format') ,10 , 1);

        add_filter( 'wpdev_bk_is_next_day',     array(&$this,'is_next_day') ,10 , 2);


        add_bk_filter( 'wpdev_booking_table', array(&$this, 'booking_table'));
        
        // Show dashboard widget for the settings
        add_bk_action( 'dashboard_bk_widget_show',    array(&$this, 'dashboard_bk_widget_show'));
        
        // Get script for calendar activation
        add_bk_filter( 'get_script_for_calendar', array(&$this, 'get_script_for_calendar'));  
        add_bk_filter( 'pre_get_calendar_html',   array(&$this, 'pre_get_calendar_html'));  

        add_bk_filter(  'wpdev_check_for_active_language', 'wpdev_check_for_active_language');   // Check content according language shortcodes

        // S H O R T C O D E s - Booking
        add_shortcode('booking', array(&$this, 'booking_shortcode'));
        add_shortcode('bookingcalendar', array(&$this, 'booking_calendar_only_shortcode'));
        add_shortcode('bookingform', array(&$this, 'bookingform_shortcode'));
        add_shortcode('bookingedit', array(&$this, 'bookingedit_shortcode'));
        add_shortcode('bookingsearch', array(&$this, 'bookingsearch_shortcode'));
        add_shortcode('bookingsearchresults', array(&$this, 'bookingsearchresults_shortcode'));
        add_shortcode('bookingselect', array(&$this, 'bookingselect_shortcode'));


        // Add settings link at the plugin page
        add_filter('plugin_action_links', array(&$this, 'plugin_links'), 10, 2 );
        add_filter('plugin_row_meta', array(&$this, 'plugin_row_meta_bk'), 10, 4 );


        add_action('wp_dashboard_setup', array($this, 'dashboard_bk_widget_setup'));
        add_bk_action( 'wpdev_booking_technical_booking_section', array(&$this, 'wpdev_booking_technical_booking_section'));

        // register widget - New, since WordPress - 2.8
        add_action('widgets_init', create_function('', 'return register_widget("BookingWidget");'));
        

        // Install / Uninstall
        register_activation_hook( WPDEV_BK_FILE, array(&$this,'wpdev_booking_activate_initial' ));
        register_deactivation_hook( WPDEV_BK_FILE, array(&$this,'wpdev_booking_deactivate' ));
        add_filter('upgrader_post_install', array(&$this, 'install_in_bulk_upgrade'), 10, 2); //Todo: fix Upgrade during bulk upgrade of plugins


        add_bk_action('wpdev_booking_activate_user', array(&$this, 'wpdev_booking_activate'));

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////


        add_action( 'admin_head', array(&$this,'wpdevbk_scripts_enqueue'));
        add_action('wp_enqueue_scripts', array(&$this, 'wpdevbk_scripts_enqueue'));

        /*
         // Force for checking plugins updates //////////
         $current = get_site_transient( 'update_plugins' );
         $current->last_checked = 0;
         
         $plugin_key_name = WPDEV_BK_PLUGIN_DIRNAME . '/' . WPDEV_BK_PLUGIN_FILENAME ;
         if (isset($current->checked[$plugin_key_name])) {
            //   $current->checked[$plugin_key_name] = '0.' . $current->checked[$plugin_key_name];
         }
         set_site_transient( 'update_plugins', $current );
        /**/

         add_filter( 'site_transient_update_plugins', array(&$this, 'plugin_non_update'), 10, 1 ); // donot show update for Personal active plugin
        // pre_set_site_transient_update_plugins
        // add_action( 'after_plugin_row', array(&$this,'after_plugin_row'),10,3 );
        // add_filter( 'plugin_row_meta', array(&$this, 'plugin_row_meta'), 10, 4 ); // donot show update for Personal active plugin

        // Load the jQuery in the client side
        if( !is_admin() ) add_action('wp_enqueue_scripts', array(&$this, 'bc_enqueue_scripts'),100000); 
        
        if(defined('WP_ADMIN')){
            $booking_version_num = get_option( 'booking_version_num');        
            if ($booking_version_num === false ) $booking_version_num = '0';
            if ( version_compare(WP_BK_VERSION_NUM, $booking_version_num) > 0 ){
                add_action('plugins_loaded', array(&$this,'wpdev_booking_activate_initial'));
            }
        }
        
    }
    
    
         
    function wpdevbk_scripts_enqueue() {
        wp_enqueue_script('jquery');
        if ( ( strpos($_SERVER['REQUEST_URI'],'wpdev-booking.phpwpdev-booking')!==false) &&
                ( strpos($_SERVER['REQUEST_URI'],'wpdev-booking.phpwpdev-booking-reservation')===false )
        ) {
            if (defined('WP_ADMIN')) if (WP_ADMIN === true) { wp_enqueue_script( 'jquery-ui-dialog' ); }
            wp_enqueue_style(  'wpdev-bk-jquery-ui', WPDEV_BK_PLUGIN_URL. '/css/jquery-ui.css', array(), 'wpdev-bk', 'screen' );
        }
    }
    // </editor-fold>



    // <editor-fold defaultstate="collapsed" desc="    Dashboard Widget Setup   ">

     // Setup Booking widget for dashboard
    function dashboard_bk_widget_setup(){
        $user_role = get_bk_option( 'booking_user_role_booking' );
        if ( $user_role == 'administrator' )  $user_role = 'activate_plugins';
        if ( $user_role == 'editor' )         $user_role = 'publish_pages';
        if ( $user_role == 'author' )         $user_role = 'publish_posts';
        if ( $user_role == 'contributor' )    $user_role = 'edit_posts';
        if ( $user_role == 'subscriber')      $user_role = 'read';
        if ( ! current_user_can( $user_role ) ) 
            return;
        
       // if (current_user_can('manage_options')) {
        $bk_dashboard_widget_id = 'booking_dashboard_widget';
        wp_add_dashboard_widget( $bk_dashboard_widget_id,
                                 sprintf(__('Booking Calendar', 'wpdev-booking') ),
                                 array($this, 'dashboard_bk_widget_show'),
                                null);

        //$dashboard_widgets_order = (array)get_user_option( "meta-box-order_dashboard" );
        //debuge($dashboard_widgets_order);
        //$dashboard_widgets_order['normal']='';
        //$dashboard_widgets_order['side']='';
        //$user = wp_get_current_user();
        //update_user_option($user->ID, 'meta-box-order_dashboard', $dashboard_widgets_order);
        global $wp_meta_boxes;
        $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

        if (isset($normal_dashboard[$bk_dashboard_widget_id])){
            // Backup and delete our new dashbaord widget from the end of the array
            $example_widget_backup = array($bk_dashboard_widget_id => $normal_dashboard[$bk_dashboard_widget_id]);
            unset($normal_dashboard[$bk_dashboard_widget_id]);
        } else $example_widget_backup = array();

        if ( is_array($normal_dashboard) ) {                        // Sometimes, some other plugins can modify this item, so its can be not a array
            // Merge the two arrays together so our widget is at the beginning
            if (is_array($normal_dashboard))
                $sorted_dashboard = array_merge($example_widget_backup, $normal_dashboard);
            else $sorted_dashboard = $example_widget_backup;
            // Save the sorted array back into the original metaboxes
            $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
        }
        //}
    }


    // Show Booking Dashboard Widget content
    function dashboard_bk_widget_show() {
        
        wp_nonce_field('wpbc_ajax_admin_nonce',  "wpbc_admin_panel_nonce_dashboard" ,  true , true );

        global $wpdb;
        $bk_admin_url = 'admin.php?page='. WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking&wh_approved=' ;

        if  ($this->is_field_in_table_exists('booking','is_new') == 0)  $update_count = 0;  // do not created this field, so do not use this method
        else                                                            $update_count = getNumOfNewBookings();

        if ($update_count > 0) {
            $update_count_title = "<span class='update-plugins count-$update_count' title=''><span class='update-count bk-update-count'>" . number_format_i18n($update_count) . "</span></span>" ;
        } else 
            $update_count_title = '0';

        
        $my_resources = '';
        if ( class_exists('wpdev_bk_multiuser')) {  // If MultiUser so
            $is_superadmin = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
            $user = wp_get_current_user();
            $user_bk_id = $user->ID;
            if (! $is_superadmin) { // User not superadmin
                $bk_ids = apply_bk_filter('get_bk_resources_of_user',false);
                if ($bk_ids !== false) {                      
                  foreach ($bk_ids as $bk_id) { $my_resources .= $bk_id->ID . ','; }
                  $my_resources = substr($my_resources,0,-1);
                }
            }
        }


        $sql_req = "SELECT DISTINCT bk.booking_id as id, dt.approved, dt.booking_date, bk.modification_date as m_date , bk.is_new as new

             FROM ".$wpdb->prefix ."bookingdates as dt

             INNER JOIN ".$wpdb->prefix ."booking as bk

             ON    bk.booking_id = dt.booking_id " .

             //" WHERE  dt.booking_date >= CURDATE()" .
             ""   ;

        if ($my_resources!='') $sql_req .=     " WHERE  bk.booking_type IN ($my_resources)";

        $sql_req .=     "ORDER BY dt.booking_date" ;

        $sql_results =  $wpdb->get_results(  wpdevbk_db_prepare($sql_req) ) ;

        $bk_array = array();
        if (! empty($sql_results))
            foreach ($sql_results as $v) {
                if (! isset($bk_array[$v->id]) ) $bk_array[$v->id] = array( 'dates'=>array() , 'bk_today'=>0, 'm_today'=>0 );

                $bk_array[$v->id]['id'] = $v->id ;
                $bk_array[$v->id]['approved'] = $v->approved ;
                $bk_array[$v->id]['dates'][] = $v->booking_date ;
                $bk_array[$v->id]['m_date'] = $v->m_date ;
                $bk_array[$v->id]['new'] = $v->new ;
                if ( is_today_date($v->booking_date) ) $bk_array[$v->id]['bk_today'] = 1 ;
                if ( is_today_date($v->m_date) ) $bk_array[$v->id]['m_today'] = 1 ;
            }

        $counter_new = $counter_pending = $counter_all = $counter_approved = 0;
        $counter_bk_today = $counter_m_today = 0;

        if (! empty($bk_array))
            foreach ($bk_array as $k=>$v) {
                $counter_all++;
                if ($v['approved']) $counter_approved++;
                else                $counter_pending++;
                if ($v['new'])      $counter_new++;

                if ($v['m_today'])  $counter_m_today++;
                if ($v['bk_today']) $counter_bk_today++;
            }

        ?>
        <style type="text/css">

            #dashboard_bk {
                width:100%;
            }
            #dashboard_bk .bk_dashboard_section {
                float:left;
                margin:0px;
                padding:0px;
                width:100%;
            }
            #dashboard-widgets-wrap #dashboard_bk .bk_dashboard_section {
               width:49%;
            }
            #dashboard-widgets-wrap #dashboard_bk .bk_right {
                float:right
            }
            #dashboard_bk .bk_header {
                color: #555555;
                font-size: 13px;
                font-weight: 600;
                line-height: 1em;
            }
            #dashboard_bk .bk_table {
                background:none repeat scroll 0 0 #FFFBFB;
                border-bottom:1px solid #ECECEC;
                border-top:1px solid #ECECEC;
                margin:6px 0 0 6px;
                padding:2px 10px;
                width:95%;
                -border-radius:4px;
                -moz-border-radius:4px;
                -webkit-border-radius:4px;
                -moz-box-shadow:0 0 2px #C5C3C3;
                -webkit-box-shadow:0 0 2px #C5C3C3;
                -box-shadow:0 0 2px #C5C3C3;
            }
            #dashboard_bk table.bk_table td{
                border-top:1px solid #DDDDDD;
                line-height:19px;
                padding:4px 0px 4px 10px;
                font-size:13px;
            }
            #dashboard_bk table.bk_table tr.first td{
                border:none;

            }
            #dashboard_bk table.bk_table tr td.first{
               text-align:center;
               padding:4px 0px;
            }
            #dashboard_bk table.bk_table tr td a {
                text-decoration: none;
            }
            #dashboard_bk table.bk_table tr td a span{
                font-size:18px;
                font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif;
            }
            #dashboard_bk table.bk_table td.bk_spec_font a{
                font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif;
                font-size:14px;
            }
            #dashboard_bk table.bk_table td.bk_spec_font {
                font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif;
                font-size:13px;
            }
            #dashboard_bk table.bk_table td.pending a{
                color:#E66F00;
            }
            #dashboard_bk table.bk_table td.new-bookings a{
                color:red;
            }
            #dashboard_bk table.bk_table td.actual-bookings a{
                color:green;
            }
            #dashboard-widgets-wrap #dashboard_bk .border_orrange, #dashboard_bk .border_orrange {
                border:1px solid #EEAB26;
                background: #FFFBCC;
                padding:0px;
                width:98%;  clear:both;
                margin:5px 5px 20px;
                border-radius:10px;
                -webkit-border-radius:10px;
                -moz-border-radius:10px;
            }
            #dashboard_bk .bk_dashboard_section h4 {
                font-size:13px;
                margin:10px 4px;
            }
            #bk_errror_loading {
                 text-align: center;
                 font-style: italic;
                 font-size:11px;
            }
        </style>
        
        <div id="dashboard_bk" >
            <div class="bk_dashboard_section bk_right">
                <span class="bk_header"><?php _e('Statistic','wpdev-booking');?>:</span>
                <table class="bk_table">
                    <tr class="first">
                        <td class="first"> <a href="<?php echo $bk_admin_url,'&wh_is_new=1&wh_booking_date=3&view_mode=vm_listing'; ?>"><span class=""><?php echo $counter_new; ?></span></a> </td>
                        <td class=""> <a href="<?php echo $bk_admin_url,'&wh_is_new=1&wh_booking_date=3&view_mode=vm_listing'; ?>"><?php _e('New (unverified) booking(s)','wpdev-booking');?></a></td>
                    </tr>
                    <tr>
                        <td class="first"> <a href="<?php echo $bk_admin_url,'&wh_approved=0&wh_booking_date=3&view_mode=vm_listing'; ?>"><span class=""><?php echo $counter_pending; ?></span></a></td>
                        <td class="pending"><a href="<?php echo $bk_admin_url,'&wh_approved=0&wh_booking_date=3&view_mode=vm_listing'; ?>" class=""><?php _e('Pending booking(s)','wpdev-booking');?></a></td>
                    </tr>
                </table>
            </div>

            <div class="bk_dashboard_section" >
                <span class="bk_header"><?php _e('Agenda','wpdev-booking');?>:</span>
                <table class="bk_table">
                    <tr class="first">
                        <td class="first"> <a href="<?php echo $bk_admin_url,'&wh_modification_date=1&wh_booking_date=3&view_mode=vm_listing'; ?>"><span><?php echo $counter_m_today; ?></span></a> </td>
                        <td class="new-bookings"><a href="<?php echo $bk_admin_url,'&wh_modification_date=1&wh_booking_date=3&view_mode=vm_listing'; ?>" class=""><?php _e('New booking(s) made today','wpdev-booking');?></a> </td>
                    </tr>
                    <tr>
                        <td class="first"> <a href="<?php echo $bk_admin_url,'&wh_booking_date=1&view_mode=vm_listing'; ?>"><span><?php echo $counter_bk_today; ?></span></a> </td>
                        <td class="actual-bookings"> <a href="<?php echo $bk_admin_url,'&wh_booking_date=1&view_mode=vm_listing'; ?>" class=""><?php _e('Bookings for today','wpdev-booking');?></a> </td>
                    </tr>
                </table>
            </div>
            <div style="clear:both;margin-bottom:20px;"></div>
            <?php
            $version = 'free';
            $version = get_bk_version();
            if ( wpdev_bk_is_this_demo() ) $version = 'free';

            if( ( strpos( strtolower(WPDEV_BK_VERSION) , 'multisite') !== false  ) || ($version == 'free' ) )  $multiv = '-multi';
            else                                                                  $multiv = '';

            $upgrade_lnk = '';
            if ( ($version == 'personal') ) $upgrade_lnk = "http://wpbookingcalendar.com/upgrade-pro" .$multiv;
            if ( ($version == 'biz_s') )    $upgrade_lnk = "http://wpbookingcalendar.com/upgrade-premium" .$multiv;
            if ( ($version == 'biz_m') )    $upgrade_lnk = "http://wpbookingcalendar.com/upgrade-premium-plus" .$multiv;

            if ( ($version != 'free') && ($upgrade_lnk != '') ) { 
            ?>
                <div class="bk_dashboard_section border_orrange" id="bk_upgrade_section"> <!-- Section 2 -->
                    <div style="padding:0px 10px;width:96%;">
                        <h4><?php if ($upgrade_lnk != '') _e('Upgrade to higher versions', 'wpdev-booking'); else _e('Commercial versions', 'wpdev-booking'); ?>:</h4>
                        <p> Check additional advanced functionality, which is exist in higher versions and can be interesting for you <a href="http://wpbookingcalendar.com/features/" target="_blank">here &raquo;</a></p>
                        <p>
                            <?php if ($upgrade_lnk != '') { ?>
                                <a href="<?php echo $upgrade_lnk; ?>" target="_blank" class="button-primary" style="float:left;"><?php _e('Upgrade now', 'wpdev-booking'); ?></a> &nbsp;
                            <?php } if ($version == 'free') { ?>
                                <a href="http://wpbookingcalendar.com/purchase/" target="_blank" class="button-primary" style="float:left;"><?php _e('Buy now', 'wpdev-booking'); ?></a> &nbsp; <?php _e('or', 'wpdev-booking'); ?> &nbsp;
                            <?php } ?>
                            <a class="button secondary" href="http://wpbookingcalendar.com/demo/" target="_blank" style="line-height:22px;"><?php _e('Test online Demo of each version', 'wpdev-booking'); ?></a>
                        </p>
                    </div>
                </div>
                <div style="clear:both;"></div>
                <?php if ($upgrade_lnk != '') { ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function(){
                            jQuery('#bk_upgrade_section').animate({opacity:1},5000).fadeOut(2000);
                        });
                    </script>
                <?php } ?>
            <?php } ?>


            <div class="bk_dashboard_section" >
                <span class="bk_header"><?php _e('Current version','wpdev-booking');?>:</span>
                <table class="bk_table">
                    <tr class="first">
                        <td style="width:35%;text-align: right;;" class=""><?php _e('Version','wpdev-booking');?>:</td>
                        <td style="text-align: left;color: red; font-weight: bold;" class="bk_spec_font"><?php echo WPDEV_BK_VERSION; ?></td>
                    </tr>
                    <?php if ($version != 'free') { ?>
                    <tr>
                        <td style="width:35%;text-align: right;" class="first b"><?php _e('Type','wpdev-booking');?>:</td>
                        <td style="text-align: left;  font-weight: bold;" class="bk_spec_font"><?php $ver = get_bk_version();if (class_exists('wpdev_bk_multiuser')) $ver = 'multiUser';$ver = str_replace('_m', ' Medium',$ver);$ver = str_replace('_l', ' Large',$ver);$ver = str_replace('_s', ' Small',$ver);$ver = str_replace('biz', 'Business',$ver); echo ucwords($ver);  ?></td>
                    </tr>
                    <tr>
                        <td style="width:35%;text-align: right;" class="first b"><?php _e('Used for','wpdev-booking');?>:</td>
                        <td style="text-align: left;  font-weight: bold;" class="bk_spec_font"><?php if (! empty($multiv)) echo ' Multiple Sites'; else echo ' 1 Site'; ?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td style="width:35%;text-align: right;" class="first b"><?php _e('Release date','wpdev-booking');?>:</td>
                        <td style="text-align: left;  font-weight: bold;" class="bk_spec_font"><?php echo date ("d.m.Y", filemtime(WPDEV_BK_FILE)); ?></td>
                    </tr>
                </table>
            </div>

            <div class="bk_dashboard_section bk_right">
                <span class="bk_header"><?php _e('Support','wpdev-booking');?>:</span>
                <table class="bk_table">
                    <tr class="first">
                        <td style="text-align:center;" class="bk_spec_font"><a href="mailto:support@wpbookingcalendar.com"><?php _e('Contact email','wpdev-booking');?></a></td>
                    </tr>
                    <tr>
                        <td style="text-align:center;" class="bk_spec_font"><a target="_blank" href="http://wpbookingcalendar.com/faq/"><?php _e('FAQ','wpdev-booking');?></a></td>
                    </tr>
                    <tr>
                        <td style="text-align:center;" class="bk_spec_font"><a target="_blank" href="http://wpbookingcalendar.com/help/"><?php _e('Have a question','wpdev-booking');?>?</a></td>
                    </tr>
                    <tr>
                        <td style="text-align:center;" class="bk_spec_font"><a target="_blank" href="http://wordpress.org/extend/plugins/booking"><?php _e('Rate this plugin (thanks:)','wpdev-booking');?></a></td>
                    </tr>
                    <tr>
                        <td style="text-align:center;" class="bk_spec_font wpdevbk"><?php 
                            if ($version == 'free') { 
                                ?><a class="button-primary button" target="_blank" href="http://wpbookingcalendar.com/features/"><?php _e('Explore Premium Features','wpdev-booking');?></a><?php                             
                            } elseif  ($upgrade_lnk != '')  { 
                                ?><a class="btn-primary btn"  href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-option&tab=upgrade"><?php _e('Upgrade','wpdev-booking');?></a><?php                                                             
                            } else {
                                ?><a target="_blank" href="http://wpbookingcalendar.com/features/"><?php _e('Explore Premium Features','wpdev-booking');?></a><?php                                                             
                            }
                      ?></td>
                    </tr>
                </table>
            </div>

            <div style="clear:both;"></div>

            
            <div style="width:95%;border:none; clear:both;margin:10px 0px;" id="bk_news_section"> <!-- Section 4 -->
                <div style="width: 96%; margin-right: 0px;; " >
                    <span class="bk_header">Booking Calendar News:</span><br/>
                    <div id="bk_news" class="rssSummary"> <span style="font-size:13px;text-align:center;">Loading...</span></div>
                    <div id="ajax_bk_respond" class="rssSummary"></div>
                    <script type="text/javascript">

                        jQuery.ajax({                                           // Start Ajax Sending
                            url: '<?php echo WPDEV_BK_PLUGIN_URL , '/' ,  WPDEV_BK_PLUGIN_FILENAME ; ?>' ,
                            type:'POST',
                            success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_bk_respond').html( data );},
                            error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;
                                //alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);
                                //if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#faq-13');}
                            },
                            // beforeSend: someFunction,
                            data:{
                                ajax_action : 'CHECK_BK_NEWS',
                                wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce_dashboard').value
                            }
                        });

                    </script>                           
                </div>
            </div>
            
            <div style="clear:both;"></div>            
        </div>

        <div style="clear:both;"></div>
        <!--div id="modal_content1" style="display:block;width:100%;height:100px;" class="modal_content_text" >
          <iframe src="http://wpbookingcalendar.com/purchase/#content" style="border:1px solid red; width:100%;height:100px;padding:0px;margin:0px;"></iframe>
        </div-->
        <?php
    }
    // </editor-fold>



    // <editor-fold defaultstate="collapsed" desc="    Update info of plugin at the plugins section   ">
    // Functions for using at future versions: update info of plugin at the plugins section.
    function plugin_row_meta_bk($plugin_meta, $plugin_file, $plugin_data, $context) {

        $this_plugin = plugin_basename(WPDEV_BK_FILE);

        if ($plugin_file == $this_plugin ) {

            $is_delete_if_deactive =  get_bk_option( 'booking_is_delete_if_deactive' ); // check

            if ($is_delete_if_deactive == 'On') { ?>
                <div class="plugin-update-tr">
                <div class="update-message">
                    <strong><?php _e('Warning !!!', 'wpdev-booking'); ?> </strong>
                    <?php _e('All booking data will be deleted when the plugin is deactivated.', 'wpdev-booking'); ?><br />
                    <?php printf(__('If you want to save your booking data, please uncheck the %s"Delete booking data"%s at the', 'wpdev-booking'), '<strong>','</strong>'); ?>
                    <a href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME; ?>wpdev-booking-option"> <?php _e('settings page', 'wpdev-booking'); ?> </a>
                </div>
                </div>
                <?php
            }

            /*
            [$plugin_meta] => Array
                (
                    [0] => Version 2.8.35
                    [1] => By wpdevelop
                    [2] => Visit plugin site
                )

            [$plugin_file] => booking/wpdev-booking.php
            [$plugin_data] => Array
                (
                    [Name] => Booking Calendar
                    [PluginURI] => http://wpbookingcalendar.com/demo/
                    [Version] => 2.8.35
                    [Description] => Online booking and availability checking service for your site.
                    [Author] => wpdevelop
                    [AuthorURI] => http://wpbookingcalendar.com/
                    [TextDomain] =>
                    [DomainPath] =>
                    [Network] =>
                    [Title] => Booking Calendar
                    [AuthorName] => wpdevelop
                )

            [$context] => all
            /**/

            // Echo plugin description here
                   return $plugin_meta;
        } else     return $plugin_meta;
    }



    function after_plugin_row($plugin_file, $plugin_data, $context) {
        if ($plugin_file =='booking/wpdev-booking.php') { ?>
            <tr class="plugin-update-tr">
                <td class="plugin-update" colspan="3">
                    <div class="update-message">
                        Please check updates of Paid versions
                        <a title="Booking Calendar" class="thickbox" href="http://wpbookingcalendar.com/changelog?width=1240&amp;TB_iframe=true" onclick="javascript:jQuery('.entry').scrollTop(100);">here</a>.
                    </div>
                </td>
            </tr>
        <?php
        }
    }


    function plugin_non_update($value) {
        if (!class_exists('wpdev_bk_personal')) return $value;

         /*$plugin_key_name = WPDEV_BK_PLUGIN_DIRNAME . '/0' . WPDEV_BK_PLUGIN_FILENAME ;
         if (isset($value->checked[$plugin_key_name])) {
            $value->checked[$plugin_key_name] = '0.' . $value->checked[$plugin_key_name];

            $value->response[$plugin_key_name] =  new StdClass;
            $value->response[$plugin_key_name]->id =  '999999999';
            $value->response[$plugin_key_name]->slug = 'booking';
            $value->response[$plugin_key_name]->new_version = '10';
            $value->response[$plugin_key_name]->url = 'http://wordpress.org/extend/plugins/booking/';
            $value->response[$plugin_key_name]->package = 'http://downloads.wordpress.org/plugin/booking.zip';
         }/**/

        //debuge($value, mktime());
         /*
        foreach ($value->response as $key=>$version_value) {
            //debuge($key, $version_value->new_version   );
            if( strpos($key, 'wpdev-booking.php') !== false ) {
        //      unset($value->response[$key]);
                break;
            }
        }/**/
        //debuge($value);
        return $value;
    }


    // Adds Settings link to plugins settings
    function plugin_links($links, $file) {

        $this_plugin = plugin_basename(WPDEV_BK_FILE);

        if ($file == $this_plugin) {
            
            //if ( ! class_exists('wpdev_bk_personal')) {
            //    $settings_link2 = '<a href="http://wpbookingcalendar.com/purchase/">'.__("Buy", 'wpdev-booking').' Pro</a>';
            //    array_unshift($links, $settings_link2);
            //}
            //$settings_link  .= '<script type="text/javascript" > var wpdev_mes = document.getElementById("message"); if (wpdev_mes != null) { wpdev_mes.innerHTML = "'.
            //'Booking Calndar Is activated successfully' .
            //                   '"; } </script>';

            $settings_link = '<a href="admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking-option">'.__("Settings", 'wpdev-booking').'</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }
    // </editor-fold>



    // <editor-fold defaultstate="collapsed" desc="  ADMIN MENU SECTIONS   ">
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // ADMIN MENU SECTIONS  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function add_new_admin_menu() {
        $users_roles = array(
            get_bk_option( 'booking_user_role_booking' ),
            get_bk_option( 'booking_user_role_addbooking' ),
            get_bk_option( 'booking_user_role_settings' ) ,
            get_bk_option( 'booking_user_role_resources' )
            );

        for ($i = 0 ; $i < count($users_roles) ; $i++) {

            if ( $users_roles[$i] == 'administrator' )  $users_roles[$i] = 'activate_plugins';
            if ( $users_roles[$i] == 'editor' )         $users_roles[$i] = 'publish_pages';
            if ( $users_roles[$i] == 'author' )         $users_roles[$i] = 'publish_posts';
            if ( $users_roles[$i] == 'contributor' )    $users_roles[$i] = 'edit_posts';
            if ( $users_roles[$i] == 'subscriber')      $users_roles[$i] = 'read';
        }
        

        if  ($this->is_field_in_table_exists('booking','is_new') == 0)  $update_count = 0;  // do not created this field, so do not use this method
        else                                                            $update_count = getNumOfNewBookings();

        $title = __('Booking', 'wpdev-booking');
        $update_title = $title;
        if ($update_count > 0) {
            $update_count_title = "<span class='update-plugins count-$update_count' title='$update_title'><span class='update-count bk-update-count'>" . number_format_i18n($update_count) . "</span></span>" ;
            $update_title .= $update_count_title;
        }

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // M A I N     B O O K I N G
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook1 = add_menu_page( __('Booking calendar', 'wpdev-booking'),  $update_title , $users_roles[0],
                WPDEV_BK_FILE . 'wpdev-booking', array(&$this, 'on_show_booking_page_main'),  WPDEV_BK_PLUGIN_URL . '/img/calendar-16x16.png'  );
        add_action("admin_print_scripts-" . $pagehook1 , array( &$this, 'on_add_admin_js_files'));

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // A D D     R E S E R V A T I O N
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook2 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Add booking', 'wpdev-booking'), __('Add booking', 'wpdev-booking'), $users_roles[1],
                WPDEV_BK_FILE .'wpdev-booking-reservation', array(&$this, 'on_show_booking_page_addbooking')  );
        add_action("admin_print_scripts-" . $pagehook2 , array( &$this, 'client_side_print_booking_head'));

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // A D D     R E S O U R C E S     Management
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $version = get_bk_version();
        //$is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'not_low_level_user'); //Anxo customizarion
        if ($version != 'free') { //Anxo customizarion

            $pagehook4 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Resources', 'wpdev-booking'), __('Resources', 'wpdev-booking'), $users_roles[3],
                    WPDEV_BK_FILE .'wpdev-booking-resources', array(&$this, 'on_show_booking_page_resources')  );
            add_action("admin_print_scripts-" . $pagehook4 , array( &$this, 'on_add_admin_js_files'));
        }

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // S E T T I N G S
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook3 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Booking settings customization', 'wpdev-booking'), __('Settings', 'wpdev-booking'), $users_roles[2],
                WPDEV_BK_FILE .'wpdev-booking-option', array(&$this, 'on_show_booking_page_settings')  );
        add_action("admin_print_scripts-" . $pagehook3 , array( &$this, 'on_add_admin_js_files'));

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

         global $submenu, $menu;               // Change Title of the Main menu inside of submenu
         if (isset($submenu[plugin_basename( WPDEV_BK_FILE ) . 'wpdev-booking']))
            $submenu[plugin_basename( WPDEV_BK_FILE ) . 'wpdev-booking'][0][0] = __('Bookings', 'wpdev-booking');
    }

    //Booking page
    function on_show_booking_page_main() {

        $this->on_show_page_adminmenu('wpdev-booking','/img/calendar-48x48.png', __('Bookings listing', 'wpdev-booking'),1);
    }

    //Add resrvation page
    function on_show_booking_page_addbooking() {

        $this->on_show_page_adminmenu('wpdev-booking-reservation','/img/add-1-48x48.png', __('Add booking', 'wpdev-booking'),2);
    }

    //Settings page
    function on_show_booking_page_settings() {

        $this->on_show_page_adminmenu('wpdev-booking-option','/img/General-setting-64x64.png', __('Booking settings customization', 'wpdev-booking'),3);
    }

    // Resources page
    function on_show_booking_page_resources() {

        $this->on_show_page_adminmenu('wpdev-booking-resources','/img/Resources-64x64.png', __('Booking resources management', 'wpdev-booking'),4);
    }

    //Show content
    function on_show_page_adminmenu($html_id, $icon, $title, $content_type) {
        ?>
        <div id="<?php echo $html_id; ?>-general" class="wrap bookingpage">
            <?php
            if ($content_type > 2 )
                echo '<div class="icon32" style="margin:5px 40px 10px 10px;"><img src="'. WPDEV_BK_PLUGIN_URL . $icon .'"><br /></div>' ;
            else
                echo '<div class="icon32" style="margin:10px 25px 10px 10px;"><img src="'. WPDEV_BK_PLUGIN_URL . $icon .'"><br /></div>' ; ?>

            <h2><?php echo $title; ?></h2>
            <?php
            switch ($content_type) {
                case 1: $this->content_of_booking_page();
                    break;
                case 2: $this->content_of_reservation_page();
                    break;
                case 3: $this->content_of_settings_page();
                    break;
                case 4: $this->content_of_resource_page();
                    break;
                default: break;
            } ?>
        </div>
        <?php
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // </editor-fold>


    
    // <editor-fold defaultstate="collapsed" desc="   S U P P O R T     F U N C T I O N S     ">
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  S U P P O R T     F U N C T I O N S        ///////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Check if table exist
    function is_table_exists( $tablename ) {
        global $wpdb;
        if (strpos($tablename, $wpdb->prefix) ===false) $tablename = $wpdb->prefix . $tablename ;
        $sql_check_table = "
            SELECT COUNT(*) AS count
            FROM information_schema.tables
            WHERE table_schema = '". DB_NAME ."'
            AND table_name = '" . $tablename . "'";

        $res = $wpdb->get_results(wpdevbk_db_prepare($sql_check_table));
        return $res[0]->count;
    }

    // Check if table exist
    function is_field_in_table_exists( $tablename , $fieldname) {
        global $wpdb;
        if (strpos($tablename, $wpdb->prefix) ===false) $tablename = $wpdb->prefix . $tablename ;
        $sql_check_table = "SHOW COLUMNS FROM " . $tablename ;

        $res = $wpdb->get_results(wpdevbk_db_prepare($sql_check_table));

        foreach ($res as $fld) {
            if ($fld->Field == $fieldname) return 1;
        }

        return 0;
    }

    // Check if index exist
    function is_index_in_table_exists( $tablename , $fieldindex) {
        global $wpdb;
        if (strpos($tablename, $wpdb->prefix) ===false) $tablename = $wpdb->prefix . $tablename ;
        $sql_check_table = "SHOW INDEX FROM ". $tablename ." WHERE Key_name = '".$fieldindex."'; ";
        $res = $wpdb->get_results(wpdevbk_db_prepare($sql_check_table));
        if (count($res)>0) return 1;
        else               return 0;
    }


    // Check if nowday is tommorow from previosday
    function is_next_day($nowday, $previosday) {

        if ( empty($previosday) ) return false;

        $nowday_d = (date('m.d.Y',  mysql2date('U', $nowday ))  );
        $prior_day = (date('m.d.Y',  mysql2date('U', $previosday ))  );
        if ($prior_day == $nowday_d)    return true;                // if its the same date


        $previos_array = (date('m.d.Y',  mysql2date('U', $previosday ))  );
        $previos_array = explode('.',$previos_array);
        $prior_day =  date('m.d.Y' , mktime(0, 0, 0, $previos_array[0], ($previos_array[1]+1), $previos_array[2] ));


        if ($prior_day == $nowday_d)    return true;                // zavtra
        else                            return false;               // net
    }

    // Change date format
    function get_showing_date_format($mydate ) {
        $date_format = get_bk_option( 'booking_date_format');
        if ($date_format == '') $date_format = "d.m.Y";

        $time_format = get_bk_option( 'booking_time_format');
        if ( $time_format !== false  ) {
            $time_format = ' ' . $time_format;
            $my_time = date('H:i:s' , $mydate);
            if ($my_time == '00:00:00')     $time_format='';
        }
        else  $time_format='';

        // return date($date_format . $time_format , $mydate);
        return date_i18n($date_format,$mydate) .'<sup class="booking-table-time">' . date_i18n($time_format  , $mydate).'</sup>';
    }
    // </editor-fold>



    // <editor-fold defaultstate="collapsed" desc="   B O O K I N G s       A D M I N       F U N C T I O N s   ">
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  B  O O K I N G s       A D M I N       F U N C T I O N s       ///////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Get dates
    function get_dates ($approved = 'all', $bk_type = 1, $additional_bk_types= array(),  $skip_booking_id = ''  ) {
/*        $bk_type_1 = explode(',', $bk_type); $bk_type = '';
        foreach ($bk_type_1 as $bkt) {
            if (!empty($bkt)) { $bk_type .= $bkt . ','; }
        }
        $bk_type = substr($bk_type, 0, -1);

        $additional_bk_types_1= array();
        foreach ($additional_bk_types as $bkt) {
            if (!empty($bkt)) { $additional_bk_types_1[] = $bkt; }
        }
        $additional_bk_types =$additional_bk_types_1;*/

        // if ( ! defined('WP_ADMIN') ) if ($approved == 0)  return array(array(),array());

        make_bk_action('check_pending_not_paid_auto_cancell_bookings', $bk_type );

        if ( count($additional_bk_types)>0 ) $bk_type_additional = $bk_type .',' . implode(',', $additional_bk_types);
        else                                 $bk_type_additional = $bk_type;

        global $wpdb;
        $dates_array = $time_array = array();
        if ($approved == 'admin_blank') {
            $sql_req = "SELECT DISTINCT dt.booking_date

                     FROM ".$wpdb->prefix ."bookingdates as dt

                     INNER JOIN ".$wpdb->prefix ."booking as bk

                     ON    bk.booking_id = dt.booking_id

                     WHERE  dt.booking_date >= CURDATE()  AND bk.booking_type IN ($bk_type_additional) AND bk.form like '%admin@blank.com%'

                     ORDER BY dt.booking_date" ;
            $dates_approve = $wpdb->get_results(  $sql_req  );
        }else {
            if ($approved == 'all')
                $sql_req = apply_bk_filter('get_bk_dates_sql', "SELECT DISTINCT dt.booking_date

                     FROM ".$wpdb->prefix ."bookingdates as dt

                     INNER JOIN ".$wpdb->prefix ."booking as bk

                     ON    bk.booking_id = dt.booking_id

                     WHERE  dt.booking_date >= CURDATE()  AND bk.booking_type IN ($bk_type_additional)
                         
                     ". (($skip_booking_id != '') ? " AND dt.booking_id NOT IN ( ".$skip_booking_id." ) ":"") ."
                         
                     ORDER BY dt.booking_date", $bk_type_additional, 'all' , $skip_booking_id);

            else
                $sql_req = apply_bk_filter('get_bk_dates_sql', "SELECT DISTINCT dt.booking_date

                     FROM ".$wpdb->prefix ."bookingdates as dt

                     INNER JOIN ".$wpdb->prefix ."booking as bk

                     ON    bk.booking_id = dt.booking_id

                     WHERE  dt.approved = $approved AND dt.booking_date >= CURDATE() AND bk.booking_type IN ($bk_type_additional)
                         
                     ". (($skip_booking_id != '') ? " AND dt.booking_id NOT IN ( ".$skip_booking_id." ) ":"") ."

                     ORDER BY dt.booking_date", $bk_type_additional, $approved, $skip_booking_id );
//if ($approved=='0') debuge($sql_req);
            $dates_approve = apply_bk_filter('get_bk_dates', $wpdb->get_results(wpdevbk_db_prepare( $sql_req )), $approved, 0,$bk_type );
        }


        // loop with all dates which is selected by someone
        if (! empty($dates_approve))
            foreach ($dates_approve as $my_date) {
                $my_date = explode(' ',$my_date->booking_date);

                $my_dt = explode('-',$my_date[0]);
                $my_tm = explode(':',$my_date[1]);

                array_push( $dates_array , $my_dt );
                array_push( $time_array , $my_tm );
            }
//debuge(array($dates_array,$time_array));            
        return    array($dates_array,$time_array);  // $dates_array;
    }

    // Generate booking CAPTCHA fields  for booking form
    function createCapthaContent($bk_tp) {
        if (  get_bk_option( 'booking_is_use_captcha' ) !== 'On'  ) return '';
        else {
            $this->captcha_instance->cleanup(1);

            $word = $this->captcha_instance->generate_random_word();
            $prefix = mt_rand();
            $this->captcha_instance->generate_image($prefix, $word);

            $filename = $prefix . '.png';
            $captcha_url = WPDEV_BK_PLUGIN_URL . '/js/captcha/tmp/' .$filename;
            $html  = '<input  autocomplete="off" type="text" class="captachinput" value="" name="captcha_input'.$bk_tp.'" id="captcha_input'.$bk_tp.'" />';
            $html .= '<img class="captcha_img"  id="captcha_img' . $bk_tp . '" alt="captcha" src="' . $captcha_url . '" />';
            $ref = substr($filename, 0, strrpos($filename, '.'));
            $html = '<input  autocomplete="off" type="hidden" name="wpdev_captcha_challenge_' . $bk_tp . '"  id="wpdev_captcha_challenge_' . $bk_tp . '" value="' . $ref . '" />'
                    . $html
                    . '<span id="captcha_msg'.$bk_tp.'" class="wpdev-help-message" ></span>';
            return $html;
        }
    }

    // Get default Booking resource
    function get_default_type() {
        if( $this->wpdev_bk_personal !== false ) {
            if (( isset( $_GET['booking_type'] )  )  && ($_GET['booking_type'] != '')) $bk_type = $_GET['booking_type'];
            else $bk_type = $this->wpdev_bk_personal->get_default_booking_resource_id();
        } else $bk_type =1;
        return $bk_type;
    }
    // </editor-fold>


    // <editor-fold defaultstate="collapsed" desc="   A D M I N    M E N U    P A G E S    ">
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  A D M I N    M E N U    P A G E S
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function content_of_booking_page() {

        wp_nonce_field('wpbc_ajax_admin_nonce',  "wpbc_admin_panel_nonce" ,  true , true );
        
        // Check if this user ACTIVE and can be at this page in MultiUser version
        $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'check_for_active_users');
        if (! $is_can) return false;

        // Get default Booking Resource, if not its not set in GET parameter
        if ( ! isset($_GET['booking_type']) ) {
            $default_booking_resource = get_bk_option( 'booking_default_booking_resource');
            if ((isset($default_booking_resource)) && ($default_booking_resource !== false)) {
                $_GET['booking_type']=  $default_booking_resource;
                make_bk_action('check_if_bk_res_parent_with_childs_set_parent_res', $default_booking_resource  );  // Check if this resource parent and has some additional childs if so then assign to parent_res=1
            }
        }

        // Check if User can be here in MultiUser version for this booking resource (is this user owner of this resource or not)
        if (  isset($_GET['booking_type']) ) {
            $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, $_GET['booking_type']);
            if ( !$is_can) { return ; }
        } else {
            if ( class_exists('wpdev_bk_multiuser')) {  // If MultiUser so
                $bk_multiuser = apply_bk_filter('get_default_bk_resource_for_user',false);
                if ($bk_multiuser == false) return;
            }
        }

        // Booking listing
        ?> <div class="wpdevbk">
            <div id="ajax_working"></div>
            <div class="clear" style="height:1px;"></div>
            <div id="ajax_respond"></div>
            <?php
                make_bk_action('write_content_for_popups' );
                //debugq();
                wpdevbk_show_booking_page();
                wpdevbk_show_booking_footer();
                //debugq(); ?>
           </div><?php
//debugq();
    }

    //Content of the Add reservation page
    function content_of_reservation_page() {

        wp_nonce_field('wpbc_ajax_admin_nonce',  "wpbc_admin_panel_nonce" ,  true , true );
        
        $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'check_for_active_users');

        if (! $is_can) return false;

        if ( ! isset($_GET['booking_type']) ) {

            $default_booking_resource = get_bk_option( 'booking_default_booking_resource');
            if ((isset($default_booking_resource)) && (! empty($default_booking_resource) )) {
            } else {
                if( $this->wpdev_bk_personal !== false ) {
                    $default_booking_resource = $this->wpdev_bk_personal->get_default_booking_resource_id();
                } else $default_booking_resource = 1;
            }
            $_GET['booking_type'] =  $default_booking_resource;

            make_bk_action('check_if_bk_res_parent_with_childs_set_parent_res', $default_booking_resource  );

            if ( class_exists('wpdev_bk_multiuser')) {  // If MultiUser so
                $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
                if (! $is_can) { // User not superadmin
                    $bk_multiuser = apply_bk_filter('get_default_bk_resource_for_user',false);
                    if ($bk_multiuser == false) return;
                    else $default_booking_resource = $bk_multiuser;
                }
            }

        }

        echo '<div id="ajax_working"></div>';
        echo '<div class="clear" style="margin:20px;"></div>';
        echo '<style type="text/css"> a.bktypetitlenew { margin-right:0px; } </style>';
        if( $this->wpdev_bk_personal !== false )  $this->wpdev_bk_personal->booking_types_pages('noedit');
        echo '<div class="clear" style="margin:20px;"></div>';

        $bk_type = $this->get_default_type();

        if ($bk_type < 1) {
            if( $this->wpdev_bk_personal !== false ) {
                   $bk_type = $this->wpdev_bk_personal->get_default_booking_resource_id();
            } else $bk_type =1;
            $_GET['booking_type'] = $bk_type;
        }
        if (isset($_GET['booking_type'])) {
            $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, $_GET['booking_type'] ); if ( !$is_can) { return ; }
        }
        echo '<div style="width:100%;">';
        do_action('wpdev_bk_add_form',$bk_type, get_bk_option( 'booking_client_cal_count'));
        ?>
        <div style="float:left;border:none;margin:0px 0 10px 1px; color:#777;">            
            <fieldset>
            <label for="is_send_email_for_new_booking" class="description">   
              <input name="is_send_email_for_new_booking" id="is_send_email_for_new_booking" type="checkbox"
                  checked="CHECKED" 
                  value="On" 
                   />
              <?php _e('Send email notification to customer about this operation', 'wpdev-booking');?>
            </label>
            </fieldset>
            
        </div>
        <?php
        echo '</div>';
        wpdevbk_booking_listing_write_js();
    }

    //content of    S E T T I N G S     page  - actions runs
    function content_of_settings_page () {

        wp_nonce_field('wpbc_ajax_admin_nonce',  "wpbc_admin_panel_nonce" ,  true , true );
        
        $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'check_for_active_users');
        if (! $is_can) return false;

        make_bk_action('wpdev_booking_settings_top_menu');            
        ?> <div id="ajax_respond"></div>
        <div class="clear" ></div>
        <div id="ajax_working"></div>
        <div id="poststuff0" class="metabox-holder" style="margin-top:0px;">
        <?php
        $is_can = apply_bk_filter('recheck_version', true); if (! $is_can) return;
        make_bk_action('wpdev_booking_settings_show_content'); ?>
        </div> <?php
        wpdevbk_booking_listing_write_js();
    }

    //content of resources management page
    function content_of_resource_page(){

        wp_nonce_field('wpbc_ajax_admin_nonce',  "wpbc_admin_panel_nonce" ,  true , true );
        
        $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'check_for_active_users');
        if (! $is_can) return false;

        /* ?> <div style="margin-top:10px;height:1px;clear:both;border-top:1px solid #bbc;"></div>  <?php /**/
        make_bk_action('wpdev_booking_resources_top_menu');
        ?> <div id="ajax_respond"></div>
        <div class="clear" ></div>
        <div id="ajax_working"></div>
        <div id="poststuff0" class="metabox-holder" style="margin-top:0px;">
        <?php make_bk_action('wpdev_booking_resources_show_content'); ?>            
        </div> <?php
        wpdevbk_booking_listing_write_js();
    }
    // </editor-fold>



    // <editor-fold defaultstate="collapsed" desc="  S E T T I N G S     S U P P O R T   F U N C T I O N S    ">
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // S E T T I N G S     S U P P O R T   F U N C T I O N S
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Show top line menu
    function settings_menu_top_line() {
        
        $selected_icon = 'General-setting-64x64.png';
        $version = get_bk_version();
        
        if (! isset($_GET['tab'])) $_GET['tab'] = '';
        $selected_title = $_GET['tab'];
        $is_only_icons = ! true;
        if  (! isset($_GET['tab'])) $_GET['tab'] = 'form';
        if ($is_only_icons) echo '<style type="text/css"> #menu-wpdevplugin .nav-tab { padding:4px 2px 6px 32px !important; } </style>';
        ?>
        <div style="height:1px;clear:both;margin-top:20px;"></div>
        <div id="menu-wpdevplugin">
            <div class="nav-tabs-wrapper">
            <div class="nav-tabs" style="width:100%;">
                <?php $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
                if ($is_can) { ?>

                    <?php $title = __('General', 'wpdev-booking');
                    $my_icon = 'General-setting-64x64.png'; $my_tab = 'main';  ?>
                    <?php if ( ($_GET['tab'] == 'main') ||($_GET['tab'] == '') || (! isset($_GET['tab'])) ) {  $slct_a = 'selected'; } else {  $slct_a = ''; } ?>
                    <?php if ($slct_a == 'selected') {  $selected_title = $title; $selected_icon = $my_icon;  ?><span class="nav-tab nav-tab-active"><?php } else { ?><a 
                            title="<?php echo __('Customization of','wpdev-booking') .' '.strtolower($title). ' '.__('settings','wpdev-booking'); ?>" rel="tooltip" class="nav-tab tooltip_bottom"  href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-option&tab=<?php echo $my_tab; ?>"><?php } ?><img class="menuicons" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/<?php echo $my_icon; ?>"><span class="nav-tab-text"><?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?><?php if ($slct_a == 'selected') { ?></span></span><?php } else { ?></span></a><?php } ?>
                <?php } else {
                         if ( (! isset($_GET['tab'])) || ($_GET['tab']=='') ) $_GET['tab'] = 'form'; // For multiuser - common user set firt selected tab -> Form
                } ?>


                    <?php $title = __('Fields', 'wpdev-booking');
                    $my_icon = 'Form-fields-64x64.png'; $my_tab = 'form';  ?>
                    <?php if ($_GET['tab'] == 'form') {  $slct_a = 'selected'; } else {  $slct_a = ''; } ?>
                    <?php if ($slct_a == 'selected') {  $selected_title = $title; $selected_icon = $my_icon;  ?><span class="nav-tab nav-tab-active"><?php } else { ?><a rel="tooltip" class="nav-tab tooltip_bottom" title="<?php echo __('Customization of booking form fields','wpdev-booking');  ?>" href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-option&tab=<?php echo $my_tab; ?>"><?php } ?><img class="menuicons" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/<?php echo $my_icon; ?>"><span class="nav-tab-text"><?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?><?php if ($slct_a == 'selected') { ?></span></span><?php } else { ?></span></a><?php } ?>

                <?php $is_can_be_here = true; //Reduction version 3.0
                if ($version == 'free') $is_can_be_here = false;
                if ($is_can_be_here) { //Reduction version 3.0 ?>
                        
                    <?php $title = __('Emails', 'wpdev-booking');
                    $my_icon = 'E-mail-64x64.png'; $my_tab = 'email';  ?>
                    <?php if ($_GET['tab'] == 'email') {  $slct_a = 'selected'; } else {  $slct_a = ''; } ?>
                    <?php if ($slct_a == 'selected') {  $selected_title = $title; $selected_icon = $my_icon;  ?><span class="nav-tab nav-tab-active"><?php } else { ?><a rel="tooltip" class="nav-tab tooltip_bottom" title="<?php echo __('Customization of email templates','wpdev-booking');  ?>" href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-option&tab=<?php echo $my_tab; ?>"><?php } ?><img class="menuicons" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/<?php echo $my_icon; ?>"><span class="nav-tab-text"><?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?><?php if ($slct_a == 'selected') { ?></span></span><?php } else { ?></span></a><?php } ?>

                    <?php $is_can = true;
                            //$is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin'); ?>    
                    <?php if ( ( ($version == 'free') || ($version == 'biz_s') || ($version == 'biz_l') || ($version == 'biz_m') ) && ($is_can) ){ ?>

                        <?php $title = __('Payments', 'wpdev-booking');
                        $my_icon = 'Paypal-cost-64x64.png'; $my_tab = 'payment';  ?>
                        <?php if ($_GET['tab'] == 'payment') {  $slct_a = 'selected'; } else {  $slct_a = ''; } ?>
                        <?php if ($slct_a == 'selected') {  $selected_title = $title; $selected_icon = $my_icon;  ?><span class="nav-tab nav-tab-active"><?php } else { ?><a rel="tooltip" class="nav-tab tooltip_bottom" title="<?php echo __('Integration of payment systems','wpdev-booking') ; ?>" href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-option&tab=<?php echo $my_tab; ?>"><?php } ?><img class="menuicons" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/<?php echo $my_icon; ?>"><span class="nav-tab-text"><?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?><?php if ($slct_a == 'selected') { ?></span></span><?php } else { ?></span></a><?php } ?>
                    <?php } ?>

                    <?php if ( ($version == 'free') || ($version == 'biz_l') || ($version == 'biz_m') ) { ?>
                        
                        <?php if ( ($version == 'free') || ($version == 'biz_l') ) { ?>
                            

                            <?php $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin'); ?>
                            <?php if ($is_can) { ?>
                                <?php $title = __('Search', 'wpdev-booking');
                                $my_icon = 'Booking-search-64x64.png'; $my_tab = 'search';  ?>
                                <?php if ($_GET['tab'] == 'search') {  $slct_a = 'selected'; } else { $slct_a = ''; } ?>
                                <?php if ($slct_a == 'selected') {  $selected_title = $title; $selected_icon = $my_icon;  ?><span class="nav-tab nav-tab-active"><?php } else { ?><a rel="tooltip" class="nav-tab tooltip_bottom" title="<?php echo __('Customization of search form','wpdev-booking') ; ?>" href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-option&tab=<?php echo $my_tab; ?>"><?php } ?><img class="menuicons" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/<?php echo $my_icon; ?>"><span class="nav-tab-text"><?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?><?php if ($slct_a == 'selected') { ?></span></span><?php } else { ?></span></a><?php } ?>
                            <?php } ?>
                        <?php } ?>
                    

                    <?php if ( ($version == 'free')   ) { ?>
                        <?php $title = __('Users', 'wpdev-booking');
                        $my_icon = 'users-48x48.png'; $my_tab = 'users';  ?>
                        <?php if ($_GET['tab'] == 'users') {  $slct_a = 'selected'; } else { $slct_a = ''; } ?>
                        <?php if ($slct_a == 'selected') {  $selected_title = $title; $selected_icon = $my_icon;  ?><span class="nav-tab nav-tab-active"><?php } else { ?><a rel="tooltip" class="nav-tab tooltip_bottom" title="<?php echo __('Manage users','wpdev-booking') . ' '.__('settings','wpdev-booking'); ?>" href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-option&tab=<?php echo $my_tab; ?>"><?php } ?><img class="menuicons" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/<?php echo $my_icon; ?>"><span class="nav-tab-text"><?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?><?php if ($slct_a == 'selected') { ?></span></span><?php } else { ?></span></a><?php } ?>
                    <?php } ?>
                    <?php } ?>

                    <?php $selected_icon2 = '';
                    $selected_icon2 .= apply_bk_filter('wpdev_show_top_menu_line' );
                    if ( ($selected_icon2 !='^') && ($selected_icon2 !='') ) {
                        $selected_icon2 = explode('^', $selected_icon2);
                        $selected_icon  = $selected_icon2[0];
                        $selected_title = $selected_icon2[1];
                    }
                    ?>

                    <?php if ( ($version == 'free')  ) { ?>

                        <?php $title = __('Buy now', 'wpdev-booking');
                        $my_icon = 'shopping_trolley.png'; $my_tab = 'buy';  ?>
                        <?php if ( ($_GET['tab'] == $my_tab)  ) {  $slct_a = 'selected'; } else {  $slct_a = ''; } ?>
                        <?php if ($slct_a == 'selected') { $selected_title = $title; $selected_icon = $my_icon;  ?><span class="nav-tab nav-tab-active"><?php } else { ?><a class="nav-tab" href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-option&tab=<?php echo $my_tab; ?>"><?php } ?><img class="menuicons" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/<?php echo $my_icon; ?>"><span class="nav-tab-text"><?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?><?php if ($slct_a == 'selected') { ?></span></span><?php } else { ?></span></a><?php } ?>

                    <?php } elseif (   ( ($version !== 'biz_l') && ( ! wpdev_bk_is_this_demo() ) ) ||
                                       (($version === 'biz_l') && (class_exists('wpdev_bk_biz_l') === false)   )
                    ) { ?>
                        <?php $title = __('Upgrade', 'wpdev-booking');
                        $my_icon = 'shopping_trolley.png'; $my_tab = 'upgrade';  ?>
                        <?php if ( ($_GET['tab'] == $my_tab)  ) {  $slct_a = 'selected'; } else {  $slct_a = ''; } ?>
                        <?php if ($slct_a == 'selected') {  $selected_title = $title; $selected_icon = $my_icon;  ?><span class="nav-tab nav-tab-active" style="float:right;"><?php } else { ?><a class="nav-tab tooltip_bottom" style="float:right;" title="<?php echo __('Upgrade to higher versions.','wpdev-booking'); ?>" href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-option&tab=<?php echo $my_tab; ?>"><?php } ?><img class="menuicons" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/<?php echo $my_icon; ?>"><span class="nav-tab-text"><?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?><?php if ($slct_a == 'selected') { ?></span></span><?php } else { ?></span></a><?php } ?>
                    <?php }  ?>
                <?php } //Reduction version 3.0 ?>
            </div>
            </div>
        </div>

        <?php if (($_GET['tab'] == 'upgrade') || ($_GET['tab'] == 'buy')) { $settings_text = '' ;
        } else {                                                            $settings_text = ' ' . __('settings'); }

        if ($version == 'free') {
            $support_links = '<div id="support_links">\n\
                    <a href="http://wpbookingcalendar.com/features/" target="_blank">'.__('Features','wpdev-booking').'</a> |\n\
                    <a href="http://wpbookingcalendar.com/demo/" target="_blank">'.__('Live Demos','wpdev-booking').'</a> |\n\                        <a href="http://wpbookingcalendar.com/faq/" target="_blank">'.__('FAQ','wpdev-booking').'</a> |\n\
                    <a href="mailto:info@wpbookingcalendar.com" target="_blank">'.__('Contact','wpdev-booking').'</a> |\n\
                    <a href="http://wpbookingcalendar.com/purchase/" class="button" target="_blank">'.__('Buy','wpdev-booking').'</a>\n\
                                  </div>';
            $support_links = '';
        } else
            $support_links = '<div id="support_links">\n\
                    <a class="live-tipsy" original-title="" href="http://wpbookingcalendar.com/faq/" target="_blank">'.__('FAQ','wpdev-booking').'</a> |\n\
                    <a href="mailto:info@wpbookingcalendar.com" target="_blank">'.__('Contact','wpdev-booking').'</a>\n\
                                  </div>';
        ?>
        <script type="text/javascript">
                var val1 = '<img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/<?php echo $selected_icon; ?>"><br />';
                jQuery('div.wrap div.icon32').html(val1);
                jQuery('div.bookingpage h2').after('<?php echo $support_links; ?>');
                jQuery('div.bookingpage h2').html( '<?php echo $selected_title . $settings_text ?>');
        </script><?php
        ?> <div style="height:1px;clear:both;border-top:1px solid #bbc;"></div>  <?php

        make_bk_action('wpdev_booking_settings_top_menu_submenu_line');   //Submenu in top line  
                        
    }

    // Show content of settings page 
    function settings_menu_content() {

        $version = get_bk_version();
        if ( wpdev_bk_is_this_demo() ) 
            $version = 'free';

        if   ( ! isset($_GET['tab']) )  {
             $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
             if ($is_can) {
                $_GET['tab'] = 'main';
             } else {                           // Multiuser first page for common user page
                $_GET['tab'] = 'form';
             }
        }

        switch ($_GET['tab']) {
        case 'main':                
            wpdev_bk_settings_general();
            break;
        case '':
            wpdev_bk_settings_general();
            break;
        case 'form':
            if (! class_exists('wpdev_bk_personal')) 
                wpdev_bk_settings_form_labels();
            break;
        case 'upgrade':
            if ( ( ($version !== 'free') && ($version !== 'biz_l') ) || (($version === 'biz_l') && (class_exists('wpdev_bk_biz_l') === false) ) )
                 wpdev_bk_upgrade_window($version);
            break;
        }
    }

    // </editor-fold>



    // <editor-fold defaultstate="collapsed" desc="    J S    &   C S S     F I L E S     &     V a r i a b l e s ">
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //    J S    &   C S S     F I L E S     &     V a r i a b l e s
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Deregister scripts, which  is generate conflicts - only at the Booking Admin  menu pages
    function wpdevbk_remove_conflict_scripts(){
        if (strpos($_SERVER['REQUEST_URI'], 'wpdev-booking.phpwpdev-booking') !== false) {
            if (function_exists('wp_dequeue_script'))
               wp_dequeue_script( 'cgmp-jquery-tools-tooltip' );                               // Remove this script jquery.tools.tooltip.min.js, which is load by the "Comprehensive Google Map Plugin"
        }
    }


    // add hook for printing scripts only at this plugin page
    function on_add_admin_js_files() {
        // Write inline scripts and CSS at HEAD
        add_action('admin_head', array(&$this, 'head_print_js_css_admin' ), 1);
    }

    // HEAD for ADMIN page
    function head_print_js_css_admin() {

        $this->print_js_css(1);
    }

    // Head of Client side - including JS Ajax function
    function client_side_print_booking_head() {

        // Write calendars script
        load_bk_Translation();
        $this->print_js_css(0);
    }

    // Write copyright notice if its saved
    function wp_footer() {
    }

    // Print     J a v a S cr i p t   &    C S S    scripts for admin and client side.
    function bc_enqueue_scripts() {
        wp_enqueue_script('jquery');                                    
        // enqueue the jQuery by Default
        // if (class_exists('wpdev_bk_biz_s')) {
            // Load the jQuery 1.7.1 if the them load the older jQuery and version of booking Calendar is BS or higher
            global $wp_scripts;
            if (  is_a( $wp_scripts, 'WP_Scripts' ) ) {
                if (isset( $wp_scripts->registered['jquery'] )) {
                    $version = $wp_scripts->registered['jquery']->ver;
                    if ( version_compare( $version, '1.7.1', '<' ) ) {
                        wp_deregister_script('jquery');
                        wp_register_script('jquery', ("http://code.jquery.com/jquery-1.7.1.min.js"), false, '1.7.1');
                        //wp_register_script('jquery', ("http://code.jquery.com/jquery-latest.min.js"), false, false);
                        wp_enqueue_script('jquery');
                    }
                    if ( version_compare( $version, '1.9', '>=' ) ) {
                        wp_register_script('jquery-migrate', ("http://code.jquery.com/jquery-migrate-1.0.0.js"), false, '1.0.0');
                        wp_enqueue_script('jquery-migrate');
                    }
                }
            }
        // }
    }

    function print_js_css($is_admin =1 ) {

        if (! ( $is_admin))  {
            wp_print_scripts('jquery');               
            // if(!is_admin()) add_action('wp_enqueue_scripts', array(&$this, 'bc_enqueue_scripts'),100000);
        }
        // wp_print_scripts('jquery-ui-core');
        //   J a v a S c r i pt
        ?>
        <!-- Booking Calendar Scripts -->
        <script  type="text/javascript">
            var wpdev_bk_plugin_url     = '<?php            
            echo   plugins_url( '' , WPDEV_BK_FILE );                        
//            if (WP_BK_SSL) {   // Activate SSL
//                $bk_url_for_js = site_url( '/wp-content/plugins/'.WPDEV_BK_PLUGIN_DIRNAME, 'https' );
//                $my_parsed_url = parse_url($bk_url_for_js);
//                echo $my_parsed_url['scheme'] . '://'. $_SERVER['SERVER_NAME'] . $my_parsed_url['path'] ;
//            } else {
//                echo site_url( '/wp-content/plugins/'.WPDEV_BK_PLUGIN_DIRNAME );
//            }
            ?>';
            var wpdev_bk_today          = new Array( parseInt(<?php echo  intval(date_i18n('Y')) .'),  parseInt('. intval(date_i18n('m')).'),  parseInt('. intval(date_i18n('d')).'),  parseInt('. intval(date_i18n('H')).'),  parseInt('. intval(date_i18n('i')) ; ?>)  );
            var visible_booking_id_on_page      = [];
            var booking_max_monthes_in_calendar = '<?php echo get_bk_option( 'booking_max_monthes_in_calendar'); ?>';
            var user_unavilable_days    = [];
<?php       if ( get_bk_option( 'booking_unavailable_day0') == 'On' ) echo ' user_unavilable_days[user_unavilable_days.length] = 0; ';
            if ( get_bk_option( 'booking_unavailable_day1') == 'On' ) echo ' user_unavilable_days[user_unavilable_days.length] = 1; ';
            if ( get_bk_option( 'booking_unavailable_day2') == 'On' ) echo ' user_unavilable_days[user_unavilable_days.length] = 2; ';
            if ( get_bk_option( 'booking_unavailable_day3') == 'On' ) echo ' user_unavilable_days[user_unavilable_days.length] = 3; ';
            if ( get_bk_option( 'booking_unavailable_day4') == 'On' ) echo ' user_unavilable_days[user_unavilable_days.length] = 4; ';
            if ( get_bk_option( 'booking_unavailable_day5') == 'On' ) echo ' user_unavilable_days[user_unavilable_days.length] = 5; ';
            if ( get_bk_option( 'booking_unavailable_day6') == 'On' ) echo ' user_unavilable_days[user_unavilable_days.length] = 6; '; ?>
            var wpdev_bk_edit_id_hash   = '<?php if ( isset( $_GET['booking_hash']  ) ) { echo $_GET['booking_hash']; } ?>';
            var wpdev_bk_plugin_filename= '<?php echo WPDEV_BK_PLUGIN_FILENAME; ?>';
            var bk_days_selection_mode     = '<?php
                $booking_type_of_day_selections = get_bk_option( 'booking_type_of_day_selections');
                if ($booking_type_of_day_selections == 'range') {
                    $booking_type_of_day_selections = get_bk_option('booking_range_selection_type');
                }
                echo $booking_type_of_day_selections;
            ?>';  // {'single', 'multiple', 'fixed', 'dynamic'}
            var wpdev_bk_personal       = <?php if(  $this->wpdev_bk_personal !== false  ) { echo '1'; } else { echo '0'; } ?>;
            var block_some_dates_from_today = <?php
            $booking_unavailable_days_num_from_today = get_bk_option( 'booking_unavailable_days_num_from_today' );
            if (! empty($booking_unavailable_days_num_from_today))  echo $booking_unavailable_days_num_from_today;
            else                                                    echo '0';
            ?>;
            var message_verif_requred = '<?php echo esc_js(__('This field is required', 'wpdev-booking')); ?>';
            var message_verif_requred_for_check_box = '<?php echo esc_js(__('This checkbox must be checked', 'wpdev-booking')); ?>';
            var message_verif_emeil = '<?php echo esc_js(__('Incorrect email field', 'wpdev-booking')); ?>';
            var message_verif_selectdts = '<?php echo esc_js(__('Please, select booking date(s) at Calendar.', 'wpdev-booking')); ?>';
            var parent_booking_resources = [];
            var new_booking_title= '<?php
                    $thank_you_mess =  get_bk_option( 'booking_title_after_reservation' ) ;
                    $thank_you_mess =  apply_bk_filter('wpdev_check_for_active_language', $thank_you_mess );
                    echo esc_js(   $thank_you_mess   ); ?>';
            var new_booking_title_time= <?php echo esc_js(__(get_bk_option( 'booking_title_after_reservation_time' ))); ?>;
            var type_of_thank_you_message = '<?php echo esc_js(__(get_bk_option( 'booking_type_of_thank_you_message' ))); ?>';
            var thank_you_page_URL = '<?php
                    $thank_you_URL =  get_bk_option( 'booking_thank_you_page_URL' ) ;
                    $thank_you_URL =  apply_bk_filter('wpdev_check_for_active_language', $thank_you_URL );
                    echo wpbc_make_link_absolute( $thank_you_URL ); ?>';
            var is_am_pm_inside_time = <?php
                $my_booking_time_format = get_bk_option( 'booking_time_format'   );
                if (  (strpos($my_booking_time_format, 'a')!== false) || (strpos($my_booking_time_format, 'A')!== false) )
                     echo 'true';
                else
                    echo 'false'
                ?>;
            var is_booking_used_check_in_out_time = false;
            var wpbc_active_locale = '<?php echo getBookingLocale(); ?>'; 
            <?php do_action('wpdev_bk_js_define_variables'); ?>
        </script><script type="text/javascript" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/js/datepick/jquery.datepick.js"></script>  <?php
        $locale = getBookingLocale();  //$locale = 'fr_FR'; // Load translation for calendar
        if ( ( !empty( $locale ) ) && ( substr($locale,0,2) !== 'en')  )
            if (file_exists(WPDEV_BK_PLUGIN_DIR. '/js/datepick/jquery.datepick-'. substr($locale,0,2) .'.js')) {
                ?> <script type="text/javascript" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/js/datepick/jquery.datepick-<?php echo substr($locale,0,2); ?>.js"></script>  <?php
            }
        ?> <script type="text/javascript" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/js/wpdev.bk.js"></script>  <?php

        do_action('wpdev_bk_js_write_files')
                ?> <!-- End Booking Calendar Scripts --> <?php

        //    C S S
        //   Admin and Client
        if($is_admin) {
                $is_not_load_bs_script_in_admin = get_bk_option( 'booking_is_not_load_bs_script_in_admin'  );
                ?> <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/interface/bs/css/bs.min.css" rel="stylesheet" type="text/css" /> <?php
                ?> <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/interface/chosen/chosen.css" rel="stylesheet" type="text/css" /> <?php
                ?> <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/css/admin.css" rel="stylesheet" type="text/css" />  <?php
                ?> <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/css/admin-booking-listing.css" rel="stylesheet" type="text/css" />  <?php
                ?> <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/css/admin-mobile.css" rel="stylesheet" type="text/css" />  <?php
                if ($is_not_load_bs_script_in_admin !== 'On') {
                    ?> <script type="text/javascript" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/interface/bs/js/bs.min.js"></script>  <?php /**/
                }
                ?> <script type="text/javascript" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/interface/chosen/chosen.jquery.min.js"></script>  <?php /**/

        } else {
            $is_not_load_bs_script_in_client = get_bk_option( 'booking_is_not_load_bs_script_in_client'  );
            ?> <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/interface/bs/css/bs.min.css" rel="stylesheet" type="text/css" />  <?php
            ?> <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/css/client.css" rel="stylesheet" type="text/css" /> <?php
            if ( strpos($_SERVER['REQUEST_URI'],'wp-admin/admin.php?') !==false ) {
                ?> <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/css/admin.css" rel="stylesheet" type="text/css" />  <?php
                ?> <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/css/admin-booking-listing.css" rel="stylesheet" type="text/css" />  <?php
                ?> <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/css/admin-mobile.css" rel="stylesheet" type="text/css" />  <?php                
            }                        
            /*?> <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/js/datepick/jquery.datepick.css" rel="stylesheet" type="text/css" /> <?php /**/
            if ($is_not_load_bs_script_in_client !== 'On') 
            if (class_exists('wpdev_bk_biz_s'))
            {
                ?> <script type="text/javascript" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/interface/bs/js/bs.min.js"></script>  <?php
            }
        }
        ?> <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/css/calendar.css" rel="stylesheet" type="text/css" /> <?php /**/
        ?> <link href="<?php echo WPDEV_BK_PLUGIN_URL . str_replace(WPDEV_BK_PLUGIN_URL, '', get_bk_option( 'booking_skin')); ?>" rel="stylesheet" type="text/css" /> <?php
    }
    // </editor-fold>



    // <editor-fold defaultstate="collapsed" desc="   C L I E N T   S I D E     &    H O O K S ">
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   C L I E N T   S I D E     &    H O O K S
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Get scripts for calendar activation
    function get_script_for_calendar($bk_type, $additional_bk_types, $my_selected_dates_without_calendar, $my_boook_count, $start_month_calendar = false ){

        $my_boook_type = $bk_type;
        $start_script_code = "<script type='text/javascript'>";
        $start_script_code .= "  jQuery(document).ready( function(){";

        
        $skip_booking_id = '';  // Id of booking to skip in calendar
        if (isset($_GET['booking_hash'])) {
            $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
            if ($my_booking_id_type !== false) {
                $skip_booking_id = $my_booking_id_type[0];  
            }
        }
        
        
        // Blank days //////////////////////////////////////////////////////////////////
        $start_script_code .= "  date_admin_blank[". $bk_type. "] = [];";
        $dates_and_time_for_admin_blank = $this->get_dates('admin_blank', $bk_type, $additional_bk_types);
        $dates_blank = $dates_and_time_for_admin_blank[0];
        $times_blank = $dates_and_time_for_admin_blank[1];
        $i=-1;
        foreach ($dates_blank as $date_blank) {
            $i++;

            $td_class =   ($date_blank[1]+0). "-" . ($date_blank[2]+0). "-". $date_blank[0];

            $start_script_code .= " if (typeof( date_admin_blank[". $bk_type. "][ '". $td_class . "' ] ) == 'undefined'){ ";
            $start_script_code .= " date_admin_blank[". $bk_type. "][ '". $td_class . "' ] = [];} ";

            $start_script_code .= "  date_admin_blank[". $bk_type. "][ '". $td_class . "' ][  date_admin_blank[".$bk_type."]['".$td_class."'].length  ] = [".
                    ($date_blank[1]+0).", ". ($date_blank[2]+0).", ". ($date_blank[0]+0).", ".
                    ($times_blank[$i][0]+0).", ". ($times_blank[$i][1]+0).", ". ($times_blank[$i][2]+0).
                    "];";
        }
        ////////////////////////////////////////////////////////////////////////////////

        $start_script_code .= "  date2approve[". $bk_type. "] = [];";
        if ( (class_exists('wpdev_bk_biz_l')) && (get_bk_option( 'booking_is_show_pending_days_as_available') == 'On') ){
            $dates_to_approve = array();
            $times_to_approve = array();            
        } else {
            $dates_and_time_to_approve = $this->get_dates('0', $bk_type, $additional_bk_types, $skip_booking_id);
            $dates_to_approve = $dates_and_time_to_approve[0];
            $times_to_approve = $dates_and_time_to_approve[1];
        }
        $i=-1;
        foreach ($dates_to_approve as $date_to_approve) {
            $i++;

            $td_class =   ($date_to_approve[1]+0). "-" . ($date_to_approve[2]+0). "-". $date_to_approve[0];

            $start_script_code .= " if (typeof( date2approve[". $bk_type. "][ '". $td_class . "' ] ) == 'undefined'){ ";
            $start_script_code .= " date2approve[". $bk_type. "][ '". $td_class . "' ] = [];} ";

            $start_script_code .= "  date2approve[". $bk_type. "][ '". $td_class . "' ][  date2approve[".$bk_type."]['".$td_class."'].length  ] = [".
                    ($date_to_approve[1]+0).", ". ($date_to_approve[2]+0).", ". ($date_to_approve[0]+0).", ".
                    ($times_to_approve[$i][0]+0).", ". ($times_to_approve[$i][1]+0).", ". ($times_to_approve[$i][2]+0).
                    "];";
        }

        $start_script_code .= "  var date_approved_par = [];";
        $start_script_code .= apply_filters('wpdev_booking_availability_filter', '', $bk_type);
        //$dates_approved = $this->get_dates('1',$my_boook_type);// [ Year, Month,Day ]...
        $dates_and_time_to_approve = $this->get_dates('1', $my_boook_type, $additional_bk_types, $skip_booking_id);
        //$dates_and_time_to_approve =  array(array(),array());
        $dates_approved =   $dates_and_time_to_approve[0];
        $times_to_approve = $dates_and_time_to_approve[1];
        $i=-1;
//debuge($dates_to_approve);        
        foreach ($dates_approved as $date_to_approve) {
            $i++;

            $td_class =   ($date_to_approve[1]+0)."-".($date_to_approve[2]+0)."-".($date_to_approve[0]);

            $start_script_code .= " if (typeof( date_approved_par[ '". $td_class . "' ] ) == 'undefined'){ ";
            $start_script_code .= " date_approved_par[ '". $td_class . "' ] = [];} ";

            $start_script_code.=" date_approved_par[ '".$td_class."' ][  date_approved_par['".$td_class."'].length  ] = [".
                    ($date_to_approve[1]+0).",".($date_to_approve[2]+0).",".($date_to_approve[0]+0).", ".
                    ($times_to_approve[$i][0]+0).", ". ($times_to_approve[$i][1]+0).", ". ($times_to_approve[$i][2]+0).
                    "];";
        }
        
        // TODO: This code section have the impact to the performace in  BM / BL / MU versions ////////////////
        if ($my_selected_dates_without_calendar == '')
            $start_script_code .= apply_filters('wpdev_booking_show_rates_at_calendar', '', $bk_type);
        $start_script_code .= apply_filters('wpdev_booking_show_availability_at_calendar', '', $bk_type);
        ///////////////////////////////////////////////////////////////////////////////////////////////////////
        
        if ($my_selected_dates_without_calendar == '') {
            $start_script_code .= apply_filters('wpdev_booking_get_additional_info_to_dates', '', $bk_type);
            $start_script_code .= "  init_datepick_cal('". $my_boook_type ."', date_approved_par, ".
                                        $my_boook_count ." , ". get_bk_option( 'booking_start_day_weeek' ) ;
            $start_js_month = ", false " ;
            if ($start_month_calendar !== false)
                if (is_array($start_month_calendar))
                    $start_js_month = ", [" . ($start_month_calendar[0]+0) . "," . ($start_month_calendar[1]+0) . "] ";

            $start_script_code .= $start_js_month .  "  );  ";
        }
        $start_script_code .= "}); </script>";

        return $start_script_code;
    }

    // Get code of the legend here
    function get_legend(){
        $my_result = '';
        if (get_bk_option( 'booking_is_show_legend' ) == 'On') {  

            $booking_legend_is_show_item_available    = get_bk_option( 'booking_legend_is_show_item_available');
            $booking_legend_text_for_item_available   = get_bk_option( 'booking_legend_text_for_item_available');

            $booking_legend_is_show_item_pending    = get_bk_option( 'booking_legend_is_show_item_pending');
            $booking_legend_text_for_item_pending   = get_bk_option( 'booking_legend_text_for_item_pending');

            $booking_legend_is_show_item_approved    = get_bk_option( 'booking_legend_is_show_item_approved');
            $booking_legend_text_for_item_approved   = get_bk_option( 'booking_legend_text_for_item_approved');

            $booking_legend_text_for_item_available = apply_bk_filter('wpdev_check_for_active_language',  $booking_legend_text_for_item_available );
            $booking_legend_text_for_item_pending   = apply_bk_filter('wpdev_check_for_active_language',  $booking_legend_text_for_item_pending );
            $booking_legend_text_for_item_approved  =  apply_bk_filter('wpdev_check_for_active_language', $booking_legend_text_for_item_approved );


            $my_result .= '<div class="block_hints datepick">';
            if ($booking_legend_is_show_item_available  == 'On') // __('Available','wpdev-booking')
                $my_result .= '<div class="wpdev_hint_with_text"><div class="block_free datepick-days-cell"><a>'.date('d').'</a></div><div class="block_text">- '. $booking_legend_text_for_item_available.'</div></div>';
            if ($booking_legend_is_show_item_approved  == 'On') // __('Booked','wpdev-booking') 
                $my_result .= '<div class="wpdev_hint_with_text"><div class="block_booked date_approved">'.date('d').'</div><div class="block_text">- '.$booking_legend_text_for_item_approved.'</div></div>';
            if ($booking_legend_is_show_item_pending  == 'On') // __('Pending','wpdev-booking') 
                $my_result .= '<div class="wpdev_hint_with_text"><div class="block_pending date2approve">'.date('d').'</div><div class="block_text">- '.$booking_legend_text_for_item_pending.'</div></div>';

            if ( class_exists('wpdev_bk_biz_s') ) {

                $booking_legend_is_show_item_partially    = get_bk_option( 'booking_legend_is_show_item_partially');
                $booking_legend_text_for_item_partially   = get_bk_option( 'booking_legend_text_for_item_partially');
                $booking_legend_text_for_item_partially  =  apply_bk_filter('wpdev_check_for_active_language', $booking_legend_text_for_item_partially );
                
                if ($booking_legend_is_show_item_partially  == 'On') { // __('Partially booked','wpdev-booking')                    
                    if ( get_bk_option( 'booking_range_selection_time_is_active' ) === 'On') {                        
                        $my_result .=  '<div class="wpdev_hint_with_text">' . 
                                                '<div class="block_check_in_out date_available date_approved check_in_time"  >
                                                    <div class="check-in-div"><div></div></div>
                                                    <div class="check-out-div"><div></div></div>
                                                    '.date('d').'
                                                </div>'.
                                                '<div class="block_text">- '. $booking_legend_text_for_item_partially .'</div>'.
                                        '</div>';                        
                    } else {
                        $my_result .= '<div class="wpdev_hint_with_text"><div class="block_time timespartly">'.date('d').'</div><div class="block_text">- '. $booking_legend_text_for_item_partially .'</div></div>';
                    }                        
                }
                
            }
            $my_result .= '</div><div class="wpdev_clear_hint"></div>';
        }
        return $my_result;
    }

    
    // Get HTML for the initilizing inline calendars
    function pre_get_calendar_html( $bk_type=1, $cal_count=1, $bk_otions=array() ){
        //SHORTCODE:
        /*
         * [booking type=56 form_type='standard' nummonths=4 
         *          options='{calendar months_num_in_row=2 width=568px cell_height=30px}']
         */
        
        $bk_otions = parse_calendar_options($bk_otions);
        /*  options:
            [months_num_in_row] => 2
            [width] => 284px
            [cell_height] => 40px
         */
        $width = $months_num_in_row = $cell_height = '';
        
        if (!empty($bk_otions)){
            
             if (isset($bk_otions['months_num_in_row'])) 
                 $months_num_in_row = $bk_otions['months_num_in_row'];
             
             if (isset($bk_otions['width'])) 
                 $width = 'width:'.$bk_otions['width'].';';
             
             if (isset($bk_otions['cell_height'])) 
                 $cell_height = $bk_otions['cell_height'];             
        }
        
        if (empty($width)){
            if (!empty($months_num_in_row))
                $width = 'width:'.($months_num_in_row*284).'px;';
            else
                $width = 'width:'.($cal_count*284).'px;';
        }
        
        if (!empty($cell_height))
             $style= '<style type="text/css" rel="stylesheet" >'.
                        '.hasDatepick .datepick-inline .datepick-title-row th,'.
                        '.hasDatepick .datepick-inline .datepick-days-cell{'.
                            ' height: '.$cell_height.' !important; '.
                        '}'.
                     '</style>';
        else $style= '';
        
        $calendar  = $style. 
                     '<div class="bk_calendar_frame months_num_in_row_'.$months_num_in_row.' cal_month_num_'.$cal_count.'" style="'.$width.'">'.
                        '<div id="calendar_booking'.$bk_type.'">'.
                            __('Calendar is loading...','wpdev-booking').
                        '</div>'.
                     '</div>'.
                     '';
        
        $booking_is_show_powered_by_notice = get_bk_option( 'booking_is_show_powered_by_notice' );          
        if ( (!class_exists('wpdev_bk_personal')) && ($booking_is_show_powered_by_notice == 'On') )
            $calendar .= '<div style="font-size:9px;text-align:left;margin-top:3px;">Powered by <a style="font-size:9px;" href="http://wpbookingcalendar.com" target="_blank">WP Booking Calendar</a></div>';
                
        $calendar .= '<textarea id="date_booking'.$bk_type.'" name="date_booking'.$bk_type.'" autocomplete="off" style="display:none;"></textarea>';   // Calendar code
        
        return $calendar;
    }
    
    
    // Get form
    function get_booking_form($my_boook_type) {
        
        $booking_form_field_active1     = get_bk_option( 'booking_form_field_active1');
        $booking_form_field_required1   = get_bk_option( 'booking_form_field_required1');
        $booking_form_field_label1      = get_bk_option( 'booking_form_field_label1');
        $booking_form_field_label1 = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label1 );

        $booking_form_field_active2     = get_bk_option( 'booking_form_field_active2');
        $booking_form_field_required2   = get_bk_option( 'booking_form_field_required2');
        $booking_form_field_label2      = get_bk_option( 'booking_form_field_label2');
        $booking_form_field_label2 = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label2 );
        
        $booking_form_field_active3     = get_bk_option( 'booking_form_field_active3');
        $booking_form_field_required3   = get_bk_option( 'booking_form_field_required3');
        $booking_form_field_label3      = get_bk_option( 'booking_form_field_label3');
        $booking_form_field_label3 = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label3 );
        
        $booking_form_field_active4     = get_bk_option( 'booking_form_field_active4');
        $booking_form_field_required4   = get_bk_option( 'booking_form_field_required4');
        $booking_form_field_label4      = get_bk_option( 'booking_form_field_label4');
        $booking_form_field_label4 = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label4 );
        
        $booking_form_field_active5     = get_bk_option( 'booking_form_field_active5');
        $booking_form_field_required5   = get_bk_option( 'booking_form_field_required5');
        $booking_form_field_label5      = get_bk_option( 'booking_form_field_label5');
        $booking_form_field_label5 = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label5 );
        
        $my_form =  '[calendar]';
                //'<div style="text-align:left;">'.
                //'<p>'.__('First Name (required)', 'wpdev-booking').':<br />  <span class="wpdev-form-control-wrap name'.$my_boook_type.'"><input type="text" name="name'.$my_boook_type.'" value="" class="wpdev-validates-as-required" size="40" /></span> </p>'.
                    
        if ($booking_form_field_active1  != 'Off')
        $my_form.='  <div class="control-group">
                      <label for="name'.$my_boook_type.'" class="control-label">'.$booking_form_field_label1.(($booking_form_field_required1=='On')?'*':'').':</label>
                      <div class="controls">
                        <input type="text" name="name'.$my_boook_type.'" id="name'.$my_boook_type.'" class="input-xlarge'.(($booking_form_field_required1=='On')?' wpdev-validates-as-required ':'').'">
                      </div>
                    </div>';
        
        if ($booking_form_field_active2  != 'Off')
        $my_form.='  <div class="control-group">
                      <label for="secondname'.$my_boook_type.'" class="control-label">'.$booking_form_field_label2.(($booking_form_field_required2=='On')?'*':'').':</label>
                      <div class="controls">
                        <input type="text" name="secondname'.$my_boook_type.'" id="secondname'.$my_boook_type.'" class="input-xlarge'.(($booking_form_field_required2=='On')?' wpdev-validates-as-required ':'').'">
                      </div>
                    </div>';                    
                  
        if ($booking_form_field_active3  != 'Off')
        $my_form.='  <div class="control-group">
                      <label for="email'.$my_boook_type.'" class="control-label">'.$booking_form_field_label3.(($booking_form_field_required3=='On')?'*':'').':</label>
                      <div class="controls">
                        <input type="text" name="email'.$my_boook_type.'" id="email'.$my_boook_type.'" class="input-xlarge wpdev-validates-as-email'.(($booking_form_field_required3=='On')?' wpdev-validates-as-required ':'').'">
                      </div>
                    </div>';
             
        if ($booking_form_field_active4  != 'Off')
        $my_form.='  <div class="control-group">
                      <label for="phone'.$my_boook_type.'" class="control-label">'.$booking_form_field_label4.(($booking_form_field_required4=='On')?'*':'').':</label>
                      <div class="controls">
                        <input type="text" name="phone'.$my_boook_type.'" id="phone'.$my_boook_type.'" class="input-xlarge'.(($booking_form_field_required4=='On')?' wpdev-validates-as-required ':'').'">
                        <p class="help-block"></p>
                      </div>
                    </div>';                    
        
        if ($booking_form_field_active5  != 'Off')
        $my_form.='  <div class="control-group">
                      <label for="details" class="control-label">'.$booking_form_field_label5.(($booking_form_field_required5=='On')?'*':'').':</label>
                      <div class="controls">
                        <textarea rows="3" name="details'.$my_boook_type.'" id="details'.$my_boook_type.'" class="input-xlarge'.(($booking_form_field_required5=='On')?' wpdev-validates-as-required ':'').'"></textarea>
                      </div>
                    </div>';
        
        $my_form.='  <div class="control-group">[captcha]</div>';
                    
        $my_form.='  <button class="btn btn-primary" type="button" onclick="mybooking_submit(this.form,'.$my_boook_type.',\''.getBookingLocale().'\');" >'.__('Send', 'wpdev-booking').'</button> ';
                  
                //.'<p>'.__('Last Name (required)', 'wpdev-booking').':<br />  <span class="wpdev-form-control-wrap secondname'.$my_boook_type.'"><input type="text" name="secondname'.$my_boook_type.'" value="" class="wpdev-validates-as-required" size="40" /></span> </p>'.
                //'<p>'.__('Email (required)', 'wpdev-booking').':<br /> <span class="wpdev-form-control-wrap email'.$my_boook_type.'"><input type="text" name="email'.$my_boook_type.'" value="" class="wpdev-validates-as-email wpdev-validates-as-required" size="40" /></span> </p>'.
                //'<p>'.__('Phone', 'wpdev-booking').':<br />            <span class="wpdev-form-control-wrap phone'.$my_boook_type.'"><input type="text" name="phone'.$my_boook_type.'" value="" size="40" /></span> </p>'.
                //'<p>'.__('Details', 'wpdev-booking').':<br />          <span class="wpdev-form-control-wrap details'.$my_boook_type.'"><textarea name="details'.$my_boook_type.'" cols="40" rows="10"></textarea></span> </p>';
                
                //$my_form .=  '<p>[captcha]</p>';
                //$my_form .=  '<p><input type="button" value="'.__('Send', 'wpdev-booking').'" onclick="mybooking_submit(this.form,'.$my_boook_type.',\''.getBookingLocale().'\');" /></p>
                //        </div>';

        return $my_form;
    }

    // Get booking form
    function get_booking_form_action($my_boook_type=1,$my_boook_count=1, $my_booking_form = 'standard',  $my_selected_dates_without_calendar = '', $start_month_calendar = false, $bk_otions=array()) {

        $res = $this->add_booking_form_action($my_boook_type,$my_boook_count, 0, $my_booking_form , $my_selected_dates_without_calendar, $start_month_calendar, $bk_otions );
        return $res;
    }

    //Show booking form from action call - wpdev_bk_add_form
    function add_booking_form_action($bk_type =1, $cal_count =1, $is_echo = 1, $my_booking_form = 'standard', $my_selected_dates_without_calendar = '', $start_month_calendar = false, $bk_otions=array() ) {
        
        $additional_bk_types = array();
        if ( strpos($bk_type,';') !== false ) {
            $additional_bk_types = explode(';',$bk_type);
            $bk_type = $additional_bk_types[0];
        }

        $is_booking_resource_exist = apply_bk_filter('wpdev_is_booking_resource_exist',true, $bk_type, $is_echo );
        if (! $is_booking_resource_exist) {
            if ( $is_echo )     echo '';
            return '';
        }

        make_bk_action('check_multiuser_params_for_client_side', $bk_type );


        if (isset($_GET['booking_hash'])) {
            $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
            if ($my_booking_id_type != false)
                if ($my_booking_id_type[1]=='') {
                    $my_result = __('Wrong booking hash in URL (probably expired)','wpdev-booking');
                    if ( $is_echo )            echo $my_result;
                    else                       return $my_result;
                    return;
                }
        }

        if ($bk_type == '') {
            $my_result = __('Booking resource type is not defined. Its can be, when at the URL is wrong booking hash.','wpdev-booking');
            if ( $is_echo )            echo $my_result;
            else                       return $my_result;
            return;
        }

        
        
              
        $start_script_code = $this->get_script_for_calendar($bk_type, $additional_bk_types, $my_selected_dates_without_calendar, $cal_count, $start_month_calendar );
        
        // Apply scripts for the conditions in the rnage days selections
        $start_script_code = apply_bk_filter('wpdev_bk_define_additional_js_options_for_bk_shortcode', $start_script_code, $bk_type, $bk_otions);  
        
        $my_result =  ' ' . $this->get__client_side_booking_content($bk_type, $my_booking_form, $my_selected_dates_without_calendar, $cal_count, $bk_otions ) . ' ' . $start_script_code ;

        $my_result = apply_filters('wpdev_booking_form', $my_result , $bk_type);

        make_bk_action('finish_check_multiuser_params_for_client_side', $bk_type );

        if ( $is_echo )            echo $my_result;
        else                       return $my_result;
    }

    //Show only calendar from action call - wpdev_bk_add_calendar
    function add_calendar_action($bk_type =1, $cal_count =1, $is_echo = 1, $start_month_calendar = false, $bk_otions=array()) {

        $additional_bk_types = array();
        if ( strpos($bk_type,';') !== false ) {
            $additional_bk_types = explode(';',$bk_type);
            $bk_type = $additional_bk_types[0];
        }

        make_bk_action('check_multiuser_params_for_client_side', $bk_type );

        if (isset($_GET['booking_hash'])) {
            $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
            if ($my_booking_id_type != false)
                if ($my_booking_id_type[1]=='') {
                    $my_result = __('Wrong booking hash in URL (probably expired)','wpdev-booking');
                    if ( $is_echo )            echo $my_result;
                    else                       return $my_result;
                    return;
                }
        }

        $start_script_code = $this->get_script_for_calendar($bk_type, $additional_bk_types, '' , $cal_count, $start_month_calendar );

        $my_result = '<div style="clear:both;height:10px;"></div>' . $this->pre_get_calendar_html( $bk_type, $cal_count, $bk_otions );

        $my_result .= $this->get_legend();                                  // Get Legend code here

        $my_result .=   ' ' . $start_script_code ;

        $my_result = apply_filters('wpdev_booking_calendar', $my_result , $bk_type);

        make_bk_action('finish_check_multiuser_params_for_client_side', $bk_type );

        if ( $is_echo )            echo $my_result;
        else                       return $my_result;
    }

    // Get content at client side of  C A L E N D A R
    function get__client_side_booking_content($my_boook_type = 1 , $my_booking_form = 'standard', $my_selected_dates_without_calendar = '', $cal_count = 1, $bk_otions = array() ) {

        $nl = '<div style="clear:both;height:10px;"></div>';                                                            // New line
        if ($my_selected_dates_without_calendar=='') {
            $calendar = $this->pre_get_calendar_html( $my_boook_type, $cal_count, $bk_otions );
        } else {
            $calendar = '<textarea rows="3" cols="50" id="date_booking'.$my_boook_type.'" name="date_booking'.$my_boook_type.'"  autocomplete="off" style="display:none;">'.$my_selected_dates_without_calendar.'</textarea>';   // Calendar code
        }
        $calendar  .= $this->get_legend();                                  // Get Legend code here


        $form = '<a name="bklnk'.$my_boook_type.'"></a><div id="booking_form_div'.$my_boook_type.'" class="booking_form_div">';

        if(  $this->wpdev_bk_personal !== false  )   $form .= $this->wpdev_bk_personal->get_booking_form($my_boook_type, $my_booking_form);         // Get booking form
        else                                    $form .= $this->get_booking_form($my_boook_type);

        // Insert calendar into form
        if ( strpos($form, '[calendar]') !== false )  $form = str_replace('[calendar]', $calendar ,$form);
        else                                          $form = '<div class="booking_form_div">' . $calendar . '</div>' . $nl . $form ;

        $form = apply_bk_filter('wpdev_check_for_additional_calendars_in_form', $form, $my_boook_type , array( 
                                                                                    'booking_form' => $my_booking_form , 
                                                                                    'selected_dates' => $my_selected_dates_without_calendar , 
                                                                                    'cal_count'=>$cal_count , 
                                                                                    'otions'=>$bk_otions     )  
                               );

        if ( strpos($form, '[captcha]') !== false ) {
            $captcha = $this->createCapthaContent($my_boook_type);
            $form =str_replace('[captcha]', $captcha ,$form);
        }

        $form = apply_filters('wpdev_booking_form_content', $form , $my_boook_type);
        // Add booking type field
        $form      .= '<input id="bk_type'.$my_boook_type.'" name="bk_type'.$my_boook_type.'" class="" type="hidden" value="'.$my_boook_type.'" /></div>';        
        $submitting = '<div id="submiting'.$my_boook_type.'"></div><div class="form_bk_messages" id="form_bk_messages'.$my_boook_type.'" ></div>';
        
        //Params: $action = -1, $name = "_wpnonce", $referer = true , $echo = true
        $wpbc_nonce  = wp_nonce_field('INSERT_INTO_TABLE',  ("wpbc_nonce" . $my_boook_type) ,  true , false );
        $wpbc_nonce .= wp_nonce_field('CALCULATE_THE_COST', ("wpbc_nonceCALCULATE_THE_COST" . $my_boook_type) ,  true , false );
        
        $res = $form . $submitting . $wpbc_nonce;

        $my_random_id = time() * rand(0,1000);
        $my_random_id = 'form_id'. $my_random_id;        
        
        $booking_form_is_using_bs_css = get_bk_option( 'booking_form_is_using_bs_css');
        $booking_form_format_type     = get_bk_option( 'booking_form_format_type');
        
        $return_form = '<div id="'.$my_random_id.'" '.(($booking_form_is_using_bs_css=='On')?'class="wpdevbk"':'').'>'.
                         '<form  id="booking_form'.$my_boook_type.'"   class="booking_form '.$booking_form_format_type.'" method="post" action="">'.
                           '<div id="ajax_respond_insert'.$my_boook_type.'"></div>'.
                           $res.
                         '</form></div>';
        
        $return_form .= '<div id="booking_form_garbage'.$my_boook_type.'" class="booking_form_garbage"></div>';
        
        if ($my_selected_dates_without_calendar == '' ) {
            // Check according already shown Booking Calendar  and set do not visible of it
            $return_form .= '<script type="text/javascript">
                                jQuery(document).ready( function(){
                                    jQuery(".widget_wpdev_booking .booking_form.form-horizontal").removeClass("form-horizontal");
                                    var visible_booking_id_on_page_num = visible_booking_id_on_page.length;
                                    if (visible_booking_id_on_page_num !== null ) {
                                        for (var i=0;i< visible_booking_id_on_page_num ;i++){
                                          if ( visible_booking_id_on_page[i]=="booking_form_div'.$my_boook_type.'" ) {
                                              document.getElementById("'.$my_random_id.'").innerHTML = "'.sprintf(__('%sWarning! Booking calendar for this booking resource are already at the page, please check more about this issue at %sthis page%s','wpdev-booking'),"<span style='color:#A00;font-size:10px;'>","<a target='_blank' href='http://wpbookingcalendar.com/faq/why-the-booking-calendar-widget-not-show-on-page/'>",'</a></span>').'";
                                              jQuery("#'.$my_random_id.'").animate( {opacity: 1}, 10000 ).fadeOut(5000);
                                              return;
                                          }
                                        }
                                        visible_booking_id_on_page[ visible_booking_id_on_page_num ]="booking_form_div'.$my_boook_type.'";
                                    }
                                });
                            </script>';
        } else {
            $return_form .= '<script type="text/javascript">
                                jQuery(document).ready( function(){            
                                    if(typeof( showCostHintInsideBkForm ) == "function") {
                                        showCostHintInsideBkForm('.$my_boook_type.');
                                    }
                                });
                            </script>';
        }

        $is_use_auto_fill_for_logged = get_bk_option( 'booking_is_use_autofill_4_logged_user' ) ;


        if (! isset($_GET['booking_hash']))
            if ($is_use_auto_fill_for_logged == 'On') {

                $curr_user = wp_get_current_user();
                if ( $curr_user->ID > 0 ) {

                    $return_form .= '<script type="text/javascript">
                                jQuery(document).ready( function(){
                                    var bk_af_submit_form = document.getElementById( "booking_form'.$my_boook_type.'" );
                                    var bk_af_count = bk_af_submit_form.elements.length;
                                    var bk_af_element;
                                    var bk_af_reg;
                                    for (var bk_af_i=0; bk_af_i<bk_af_count; bk_af_i++)   {
                                        bk_af_element = bk_af_submit_form.elements[bk_af_i];
                                        if (
                                            (bk_af_element.type == "text") &&
                                            (bk_af_element.type !=="button") &&
                                            (bk_af_element.type !=="hidden") &&
                                            (bk_af_element.name !== ("date_booking'.$my_boook_type.'" ) )
                                           ) {
                                                // Second Name
                                                bk_af_reg = /^([A-Za-z0-9_\-\.])*(last|second){1}([_\-\.])?name([A-Za-z0-9_\-\.])*$/;
                                                if(bk_af_reg.test(bk_af_element.name) != false)
                                                    if (bk_af_element.value == "" )
                                                        bk_af_element.value  = "'.str_replace("'",'',$curr_user->last_name).'";
                                                // First Name
                                                bk_af_reg = /^name([0-9_\-\.])*$/;
                                                if(bk_af_reg.test(bk_af_element.name) != false)
                                                    if (bk_af_element.value == "" )
                                                        bk_af_element.value  = "'.str_replace("'",'',$curr_user->first_name).'";
                                                bk_af_reg = /^([A-Za-z0-9_\-\.])*(first|my){1}([_\-\.])?name([A-Za-z0-9_\-\.])*$/;
                                                if(bk_af_reg.test(bk_af_element.name) != false)
                                                    if (bk_af_element.value == "" )
                                                        bk_af_element.value  = "'.str_replace("'",'',$curr_user->first_name).'";
                                                // Email
                                                bk_af_reg = /^(e)?([_\-\.])?mail([0-9_\-\.])*$/;
                                                if(bk_af_reg.test(bk_af_element.name) != false)
                                                    if (bk_af_element.value == "" )
                                                        bk_af_element.value  = "'.str_replace("'",'',$curr_user->user_email).'";
                                                // URL
                                                bk_af_reg = /^([A-Za-z0-9_\-\.])*(URL|site|web|WEB){1}([A-Za-z0-9_\-\.])*$/;
                                                if(bk_af_reg.test(bk_af_element.name) != false)
                                                    if (bk_af_element.value == "" )
                                                        bk_af_element.value  = "'.str_replace("'",'',$curr_user->user_url).'";
                                           }
                                    }
                                });
                                </script>';
                }
             }

        return $return_form;
    }
    // </editor-fold>



    // <editor-fold defaultstate="collapsed" desc="   S H O R T    C O D E S ">
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   S H O R T    C O D E S
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Replace MARK at post with content at client side   -----    [booking nummonths='1' type='1']
    function booking_shortcode($attr) {
//debuge($attr);

        if (isset($_GET['booking_hash'])) return __('You need to use special shortcode [bookingedit] for booking editing.','wpdev-booking');

        $my_boook_count = get_bk_option( 'booking_client_cal_count' );
        $my_boook_type = 1;
        $my_booking_form = 'standard';
        $start_month_calendar = false;
        $bk_otions = array();

        if ( isset( $attr['nummonths'] ) ) { $my_boook_count = $attr['nummonths'];  }
        if ( isset( $attr['type'] ) )      { $my_boook_type = $attr['type'];        }
        if ( isset( $attr['form_type'] ) ) { $my_booking_form = $attr['form_type']; }

        if ( isset( $attr['agregate'] )  && (! empty( $attr['agregate'] )) ) {
            $additional_bk_types = $attr['agregate'];
            $my_boook_type .= ';'.$additional_bk_types;
        }


        if ( isset( $attr['startmonth'] ) ) { // Set start month of calendar, fomrat: '2011-1'

            $start_month_calendar = explode( '-', $attr['startmonth'] );
            if ( (is_array($start_month_calendar))  && ( count($start_month_calendar) > 1) ) { }
            else $start_month_calendar = false;

        }

        if ( isset( $attr['options'] ) ) { $bk_otions = $attr['options']; }
        
        $res = $this->add_booking_form_action($my_boook_type,$my_boook_count, 0 , $my_booking_form , '', $start_month_calendar, $bk_otions );

        return $res;
    }

    // Replace MARK at post with content at client side   -----    [booking nummonths='1' type='1']
    function booking_calendar_only_shortcode($attr) {
        $my_boook_count = get_bk_option( 'booking_client_cal_count' );
        $my_boook_type = 1;
        $start_month_calendar = false;
        $bk_otions = array();
        if ( isset( $attr['nummonths'] ) ) { $my_boook_count = $attr['nummonths']; }
        if ( isset( $attr['type'] ) )      { $my_boook_type = $attr['type'];       }
        if ( isset( $attr['agregate'] )  && (! empty( $attr['agregate'] )) ) {
            $additional_bk_types = $attr['agregate'];
            $my_boook_type .= ';'.$additional_bk_types;
        }

        if ( isset( $attr['startmonth'] ) ) { // Set start month of calendar, fomrat: '2011-1'
            $start_month_calendar = explode( '-', $attr['startmonth'] );
            if ( (is_array($start_month_calendar))  && ( count($start_month_calendar) > 1) ) { }
            else $start_month_calendar = false;
        }
        
        if ( isset( $attr['options'] ) ) { $bk_otions = $attr['options']; }
        $res = $this->add_calendar_action($my_boook_type,$my_boook_count, 0, $start_month_calendar, $bk_otions  );


        $start_script_code = "<div id='calendar_booking_unselectable".$my_boook_type."'></div>";
        return $start_script_code. $res ;
    }

    // Show only booking form, with already selected dates
    function bookingform_shortcode($attr) {

        $my_boook_type = 1;
        $my_booking_form = 'standard';
        $my_boook_count = 1;
        $my_selected_dates_without_calendar = '';

        if ( isset( $attr['type'] ) )           { $my_boook_type = $attr['type'];                                }
        if ( isset( $attr['form_type'] ) )      { $my_booking_form = $attr['form_type'];                         }
        if ( isset( $attr['selected_dates'] ) ) { $my_selected_dates_without_calendar = $attr['selected_dates']; }  //$my_selected_dates_without_calendar = '20.08.2010, 29.08.2010';

        $res = $this->add_booking_form_action($my_boook_type,$my_boook_count, 0 , $my_booking_form, $my_selected_dates_without_calendar, false );
        return $res;
    }

    // Show booking form for editing
    function bookingedit_shortcode($attr) {
        $my_boook_count = get_bk_option( 'booking_client_cal_count' );
        $my_boook_type = 1;
        $my_booking_form = 'standard';
        $bk_otions = array();
        if ( isset( $attr['nummonths'] ) )   { $my_boook_count = $attr['nummonths'];  }
        if ( isset( $attr['type'] ) )        { $my_boook_type = $attr['type'];        }
        if ( isset( $attr['form_type'] ) )   { $my_booking_form = $attr['form_type']; }

        if (isset($_GET['booking_hash'])) {
            $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
            if ($my_booking_id_type !== false) {
                $my_edited_bk_id = $my_booking_id_type[0];
                $my_boook_type        = $my_booking_id_type[1];
                if ($my_boook_type == '') return __('Wrong booking hash in URL. Probably hash is expired.','wpdev-booking');
            } else {
                return __('Wrong booking hash in URL. Probably hash is expired.','wpdev-booking');
            }

        } else {
            return __('You do not set any parameters for booking editing','wpdev-booking');
        }
        if ( isset( $attr['options'] ) ) { $bk_otions = $attr['options']; }

        $res = $this->add_booking_form_action($my_boook_type,$my_boook_count, 0 , $my_booking_form, '', false, $bk_otions );

        if (isset($_GET['booking_pay'])) {
            // Payment form
            $res .= apply_bk_filter('wpdev_get_payment_form',$my_edited_bk_id, $my_boook_type );

        }

        return $res;
    }

    // Search form
    function bookingsearch_shortcode($attr) {

        $search_form = apply_bk_filter('wpdev_get_booking_search_form','', $attr );

        return $search_form ;
    }

    // Search Results form
    function bookingsearchresults_shortcode($attr) {

        $search_results = apply_bk_filter('wpdev_get_booking_search_results','', $attr );

        return $search_results ;
    }

    // Select Booking form using the selectbox
    function bookingselect_shortcode($attr) {

        $search_form = apply_bk_filter('wpdev_get_booking_select_form','', $attr );

        return $search_form ;
    }


    // </editor-fold>



    // <editor-fold defaultstate="collapsed" desc="  A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N  ">
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///   A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N  ////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    // Activation  of the plugin, when the use is clicked on the "Active" link at  the Plugins WordPress menu.
    function wpdev_booking_activate_initial(){
        
        // Activate the plugin
        $this->wpdev_booking_activate();
        
        // Bail if this demo or activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) || wpdev_bk_is_this_demo() )
		return;
        
        // Add the transient to redirect - Showing Welcome screen
	set_transient( '_wpbc_activation_redirect', true, 30 );        
    }
    
    // Activate
    function wpdev_booking_activate() {
        
        update_bk_option( 'booking_version_num',WP_BK_VERSION_NUM);
        
        $version = get_bk_version();
        $is_demo = wpdev_bk_is_this_demo();
        
        load_bk_Translation();
        // set execution time to 15 minutes, its not worked if we have SAFE MODE ON at PHP
        if (function_exists('set_time_limit')) 		if( !in_array(ini_get('safe_mode'),array('1', 'On')) ) set_time_limit(900);

        add_bk_option( 'booking_admin_cal_count' , ($is_demo)?'3':'2');
        
        add_bk_option( 'booking_skin',  '/css/skins/traditional.css');

        add_bk_option( 'bookings_num_per_page','10');
        add_bk_option( 'booking_sort_order','');
        add_bk_option( 'booking_default_toolbar_tab','filter');
        add_bk_option( 'bookings_listing_default_view_mode','vm_calendar');//,'vm_listing');
        
        
        if ($version=='free')   add_bk_option( 'booking_view_days_num','90');   // 3 Month - for one resource
        else                    add_bk_option( 'booking_view_days_num','30');   // Month view for several resources
        //add_bk_option( 'booking_sort_order_direction', 'ASC');

        add_bk_option( 'booking_max_monthes_in_calendar', '1y');
        add_bk_option( 'booking_client_cal_count', '1' );
        add_bk_option( 'booking_start_day_weeek' ,'0');
        add_bk_option( 'booking_title_after_reservation' , sprintf(__('Thank you for your online booking. %s We will send confirmation of your booking as soon as possible.', 'wpdev-booking'), '') );
        add_bk_option( 'booking_title_after_reservation_time' , '7000' );
        add_bk_option( 'booking_type_of_thank_you_message' , 'message' );
        add_bk_option( 'booking_thank_you_page_URL' ,  '/thank-you' );
        add_bk_option( 'booking_is_use_autofill_4_logged_user' , ($is_demo)?'On':'Off' );


        add_bk_option( 'booking_date_format' , get_option('date_format') );
        add_bk_option( 'booking_date_view_type', 'short');    // short / wide
        add_bk_option( 'booking_is_delete_if_deactive' , ($is_demo)?'On':'Off' ); // check
        
        add_bk_option( 'booking_dif_colors_approval_pending' , 'On' );
        add_bk_option( 'booking_is_use_hints_at_admin_panel' , 'On' );
        add_bk_option( 'booking_is_not_load_bs_script_in_client' , 'Off' );
        add_bk_option( 'booking_is_not_load_bs_script_in_admin' , 'Off' );

        // Set the type of days selections based on the previous saved data ....
        $booking_type_of_day_selections = 'multiple'; //'single';
        
        if ( get_bk_option( 'booking_multiple_day_selections' ) == 'On')    $booking_type_of_day_selections = 'multiple';                     
        if ( get_bk_option( 'booking_range_selection_is_active') == 'On' )  $booking_type_of_day_selections = 'range';
        if ( $is_demo )                                                     $booking_type_of_day_selections = 'multiple';
        
        add_bk_option( 'booking_type_of_day_selections' , $booking_type_of_day_selections );

        add_bk_option( 'booking_form_is_using_bs_css' ,'On');
        add_bk_option( 'booking_form_format_type' ,'vertical');

        add_bk_option( 'booking_form_field_active1' ,'On');
        add_bk_option( 'booking_form_field_required1' ,'On');
        add_bk_option( 'booking_form_field_label1' ,'First Name');
        add_bk_option( 'booking_form_field_active2' ,'On');
        add_bk_option( 'booking_form_field_required2' ,'On');
        add_bk_option( 'booking_form_field_label2' ,'Last Name');
        add_bk_option( 'booking_form_field_active3' ,'On');
        add_bk_option( 'booking_form_field_required3' ,'On');
        add_bk_option( 'booking_form_field_label3' ,'Email');
        add_bk_option( 'booking_form_field_active4' ,'On');
        add_bk_option( 'booking_form_field_required4' ,'Off');
        add_bk_option( 'booking_form_field_label4' ,'Phone');
        add_bk_option( 'booking_form_field_active5' ,'On');
        add_bk_option( 'booking_form_field_required5' ,'Off');
        add_bk_option( 'booking_form_field_label5' ,'Details');

        add_bk_option( 'booking_unavailable_days_num_from_today' , '0' );
        add_bk_option( 'booking_unavailable_day0' ,'Off');
        add_bk_option( 'booking_unavailable_day1' ,'Off');
        add_bk_option( 'booking_unavailable_day2' ,'Off');
        add_bk_option( 'booking_unavailable_day3' ,'Off');
        add_bk_option( 'booking_unavailable_day4' ,'Off');
        add_bk_option( 'booking_unavailable_day5' ,'Off');
        add_bk_option( 'booking_unavailable_day6' ,'Off');

        if ( $is_demo ) {
            add_bk_option( 'booking_user_role_booking', 'subscriber' );
            add_bk_option( 'booking_user_role_addbooking', 'subscriber' );
            add_bk_option( 'booking_user_role_resources', 'subscriber' );
            add_bk_option( 'booking_user_role_settings', 'subscriber' );
        } else {
            add_bk_option( 'booking_user_role_booking', 'editor' );
            add_bk_option( 'booking_user_role_addbooking', 'editor' );
            add_bk_option( 'booking_user_role_resources', 'editor' );
            add_bk_option( 'booking_user_role_settings', 'administrator' );
        }
        $blg_title = get_option('blogname');
        $blg_title = str_replace('"', '', $blg_title);
        $blg_title = str_replace("'", '', $blg_title);

        add_bk_option( 'booking_email_reservation_adress', htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
        add_bk_option( 'booking_email_reservation_from_adress', '[visitoremail]'); //htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
        add_bk_option( 'booking_email_reservation_subject',__('New booking', 'wpdev-booking'));
        add_bk_option( 'booking_email_reservation_content',htmlspecialchars(sprintf(__('You need to approve a new booking %s for: %s Person detail information:%s Currently a new booking is waiting for approval. Please visit the moderation panel%sThank you, %s', 'wpdev-booking'),'[bookingtype]','[dates]<br/><br/>','<br/> [content]<br/><br/>',' [moderatelink]<br/><br/>',$blg_title.'<br/>[siteurl]')));

        add_bk_option( 'booking_email_approval_adress',htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
        add_bk_option( 'booking_email_approval_subject',__('Your booking has been approved', 'wpdev-booking'));
        if( $this->wpdev_bk_personal !== false )
            add_bk_option( 'booking_email_approval_content',htmlspecialchars(sprintf(__('Your reservation %s for: %s has been approved.%sYou can edit the booking on this page: %s Thank you, %s', 'wpdev-booking'),'[bookingtype]','[dates]','<br/><br/>[content]<br/><br/>', '[visitorbookingediturl]<br/><br/>' , $blg_title.'<br/>[siteurl]')));
        else add_bk_option( 'booking_email_approval_content',htmlspecialchars(sprintf(__('Your booking %s for: %s has been approved.%sThank you, %s', 'wpdev-booking'),'[bookingtype]','[dates]','<br/><br/>[content]<br/><br/>',$blg_title.'<br/>[siteurl]')));

        add_bk_option( 'booking_email_deny_adress',htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
        add_bk_option( 'booking_email_deny_subject',__('Your booking has been declined', 'wpdev-booking'));
        add_bk_option( 'booking_email_deny_content',htmlspecialchars(sprintf(__('Your booking %s for: %s has been  canceled. %sThank you, %s', 'wpdev-booking'),'[bookingtype]','[dates]','<br/><br/>[denyreason]<br/><br/>[content]<br/><br/>',$blg_title.'<br/>[siteurl]')));

        add_bk_option( 'booking_is_email_reservation_adress', 'On' );
        add_bk_option( 'booking_is_email_approval_adress', 'On' );
        add_bk_option( 'booking_is_email_deny_adress', 'On' );



        add_bk_option( 'booking_widget_title', __('Booking form', 'wpdev-booking') );
        add_bk_option( 'booking_widget_show', 'booking_form' );
        add_bk_option( 'booking_widget_type', '1' );
        add_bk_option( 'booking_widget_calendar_count',  '1');
        add_bk_option( 'booking_widget_last_field','');

        add_bk_option( 'booking_wpdev_copyright_adminpanel','On' );
        add_bk_option( 'booking_is_show_powered_by_notice','On' );
        add_bk_option( 'booking_is_use_captcha' , 'Off' );
        add_bk_option( 'booking_is_show_legend' , 'Off' );
        add_bk_option( 'booking_legend_is_show_item_available' , 'On' );
        add_bk_option( 'booking_legend_text_for_item_available', __('Available', 'wpdev-booking') );
        add_bk_option( 'booking_legend_is_show_item_pending', 'On' );
        add_bk_option( 'booking_legend_text_for_item_pending', __('Pending', 'wpdev-booking') );
        add_bk_option( 'booking_legend_is_show_item_approved', 'On' );
        add_bk_option( 'booking_legend_text_for_item_approved', __('Booked', 'wpdev-booking') );
        if ( class_exists('wpdev_bk_biz_s') ) {
            add_bk_option( 'booking_legend_is_show_item_partially', 'On' );
            add_bk_option( 'booking_legend_text_for_item_partially', __('Partially booked', 'wpdev-booking') );
        }



        // Create here tables which is needed for using plugin
        global $wpdb;
        $charset_collate = '';
        //if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty($wpdb->charset) ) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if ( ! empty($wpdb->collate) ) $charset_collate .= " COLLATE $wpdb->collate";
        //}

        $wp_queries = array();
        if ( ! $this->is_table_exists('booking') ) { // Cehck if tables not exist yet

            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."booking (
                     booking_id bigint(20) unsigned NOT NULL auto_increment,
                     form text ,
                     booking_type bigint(10) NOT NULL default 1,
                     PRIMARY KEY  (booking_id)
                    ) $charset_collate;";
            $wpdb->query(wpdevbk_db_prepare($simple_sql));
        } elseif  ($this->is_field_in_table_exists('booking','form') == 0) {
            $wp_queries[]  = "ALTER TABLE ".$wpdb->prefix ."booking ADD form TEXT AFTER booking_id";
            //$wpdb->query(wpdevbk_db_prepare($simple_sql));
        }

        if  ($this->is_field_in_table_exists('booking','modification_date') == 0) {
            $wp_queries[]  = "ALTER TABLE ".$wpdb->prefix ."booking ADD modification_date datetime AFTER booking_id";
            //$wpdb->query(wpdevbk_db_prepare($simple_sql));
        }

        if  ($this->is_field_in_table_exists('booking','sort_date') == 0) {
            $wp_queries[]  = "ALTER TABLE ".$wpdb->prefix ."booking ADD sort_date datetime AFTER booking_id";
            //$wpdb->query(wpdevbk_db_prepare($simple_sql));
        }

        if  ($this->is_field_in_table_exists('booking','status') == 0) {
            $wp_queries[]  = "ALTER TABLE ".$wpdb->prefix ."booking ADD status varchar(200) NOT NULL default '' AFTER booking_id";
            //$wpdb->query(wpdevbk_db_prepare($simple_sql));
        }

        if  ($this->is_field_in_table_exists('booking','is_new') == 0) {
            $wp_queries[]  = "ALTER TABLE ".$wpdb->prefix ."booking ADD is_new bigint(10) NOT NULL default 1 AFTER booking_id";
            //$wpdb->query(wpdevbk_db_prepare($simple_sql));
        }

        if ( ! $this->is_table_exists('bookingdates') ) { // Cehck if tables not exist yet
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."bookingdates (
                     booking_id bigint(20) unsigned NOT NULL,
                     booking_date datetime NOT NULL default '0000-00-00 00:00:00',
                     approved bigint(20) unsigned NOT NULL default 0
                    ) $charset_collate;";
            $wpdb->query(wpdevbk_db_prepare($simple_sql));

            if( $this->wpdev_bk_personal == false ) {
                $wp_queries[] = "INSERT INTO ".$wpdb->prefix ."booking ( form, modification_date ) VALUES (
                     'text^name1^Jony~text^secondname1^Smith~text^email1^example-free@wpbookingcalendar.com~text^phone1^8(038)458-77-77~textarea^details1^Reserve a room with sea view', NOW() );";
            }
        }

        if (!class_exists('wpdev_bk_biz_l')) {
            if  ($this->is_index_in_table_exists('bookingdates','booking_id_dates') == 0) {
                $simple_sql = "CREATE UNIQUE INDEX booking_id_dates ON ".$wpdb->prefix ."bookingdates (booking_id, booking_date);";
                $wpdb->query(wpdevbk_db_prepare($simple_sql));
            }
        } else {
            if  ($this->is_index_in_table_exists('bookingdates','booking_id_dates') != 0) {
                $simple_sql = "DROP INDEX booking_id_dates ON  ".$wpdb->prefix ."bookingdates ;";
                $wpdb->query(wpdevbk_db_prepare($simple_sql));
            }
        }
        

        if (count($wp_queries)>0) {
            foreach ($wp_queries as $wp_q)
                $wpdb->query(wpdevbk_db_prepare($wp_q));

            if( $this->wpdev_bk_personal == false ) {
                $temp_id = $wpdb->insert_id;
                $wp_queries_sub = "INSERT INTO ".$wpdb->prefix ."bookingdates (
                         booking_id,
                         booking_date
                        ) VALUES
                        ( ". $temp_id .", CURDATE()+ INTERVAL 2 day ),
                        ( ". $temp_id .", CURDATE()+ INTERVAL 3 day ),
                        ( ". $temp_id .", CURDATE()+ INTERVAL 4 day );";
                $wpdb->query(wpdevbk_db_prepare($wp_queries_sub));
            }
        }



        // if( $this->wpdev_bk_personal !== false )  $this->wpdev_bk_personal->pro_activate();
        make_bk_action('wpdev_booking_activation');


        // Examples in demos
        if ( $is_demo ) {  $this->createExamples4Demo(); }
        
//        // Fill Development server by initial bookings
//        if (  $_SERVER['HTTP_HOST'] === 'dev'  ) {  
//            for ($i = 0; $i < 5; $i++) {
//                //if (!class_exists('wpdev_bk_personal')) 
//                $this->createExamples4Demo( array(1,2,3,4,5,6,7,8,9,10,11,12) ); 
//            }
//        }
//        $this->setDefaultInitialValues();

        $this->reindex_booking_db();

        
    }

    // Deactivate
    function wpdev_booking_deactivate() {

        // set execution time to 15 minutes, its not worked if we have SAFE MODE ON at PHP
        if (function_exists('set_time_limit')) 		if( !in_array(ini_get('safe_mode'),array('1', 'On')) ) set_time_limit(900);


        $is_delete_if_deactive =  get_bk_option( 'booking_is_delete_if_deactive' ); // check

        if ($is_delete_if_deactive == 'On') {
            // Delete here tables and options, which are needed for using plugin
            delete_bk_option( 'booking_version_num');

            delete_bk_option( 'booking_skin');
            delete_bk_option( 'bookings_num_per_page');
            delete_bk_option( 'booking_sort_order');
            delete_bk_option( 'booking_sort_order_direction');
            delete_bk_option( 'booking_default_toolbar_tab');
            delete_bk_option( 'bookings_listing_default_view_mode');
            delete_bk_option( 'booking_view_days_num');

            delete_bk_option( 'booking_max_monthes_in_calendar');
            delete_bk_option( 'booking_admin_cal_count' );
            delete_bk_option( 'booking_client_cal_count' );
            delete_bk_option( 'booking_start_day_weeek' );
            delete_bk_option( 'booking_title_after_reservation');
            delete_bk_option( 'booking_title_after_reservation_time');
            delete_bk_option( 'booking_type_of_thank_you_message' , 'message' );
            delete_bk_option( 'booking_thank_you_page_URL' , site_url() );
            delete_bk_option( 'booking_is_use_autofill_4_logged_user' ) ;
            
            delete_bk_option( 'booking_form_is_using_bs_css');
            delete_bk_option( 'booking_form_format_type');
            
            delete_bk_option( 'booking_form_field_active1');
            delete_bk_option( 'booking_form_field_required1');
            delete_bk_option( 'booking_form_field_label1');
            delete_bk_option( 'booking_form_field_active2');
            delete_bk_option( 'booking_form_field_required2');
            delete_bk_option( 'booking_form_field_label2');
            delete_bk_option( 'booking_form_field_active3');
            delete_bk_option( 'booking_form_field_required3');
            delete_bk_option( 'booking_form_field_label3');
            delete_bk_option( 'booking_form_field_active4');
            delete_bk_option( 'booking_form_field_required4');
            delete_bk_option( 'booking_form_field_label4');
            delete_bk_option( 'booking_form_field_active5');
            delete_bk_option( 'booking_form_field_required5');
            delete_bk_option( 'booking_form_field_label5');
            
            
            delete_bk_option( 'booking_date_format');
            delete_bk_option( 'booking_date_view_type');
            delete_bk_option( 'booking_is_delete_if_deactive' ); // check
            delete_bk_option( 'booking_wpdev_copyright_adminpanel' );             // check
            delete_bk_option( 'booking_is_show_powered_by_notice' );             // check
            delete_bk_option( 'booking_is_use_captcha' );
            delete_bk_option( 'booking_is_show_legend' );
            delete_bk_option( 'booking_legend_is_show_item_available' );
            delete_bk_option( 'booking_legend_text_for_item_available' );
            delete_bk_option( 'booking_legend_is_show_item_pending' );
            delete_bk_option( 'booking_legend_text_for_item_pending' );
            delete_bk_option( 'booking_legend_is_show_item_approved' );
            delete_bk_option( 'booking_legend_text_for_item_approved' );
            if ( class_exists('wpdev_bk_biz_s') ) {
                delete_bk_option( 'booking_legend_is_show_item_partially' );
                delete_bk_option( 'booking_legend_text_for_item_partially' );
            }




            delete_bk_option( 'booking_dif_colors_approval_pending'   );
            delete_bk_option( 'booking_is_use_hints_at_admin_panel'  );
            delete_bk_option( 'booking_is_not_load_bs_script_in_client'  );
            delete_bk_option( 'booking_is_not_load_bs_script_in_admin'  );

            delete_bk_option( 'booking_multiple_day_selections' );
            delete_bk_option( 'booking_type_of_day_selections' );

            delete_bk_option( 'booking_unavailable_days_num_from_today' );

            delete_bk_option( 'booking_unavailable_day0' );
            delete_bk_option( 'booking_unavailable_day1' );
            delete_bk_option( 'booking_unavailable_day2' );
            delete_bk_option( 'booking_unavailable_day3' );
            delete_bk_option( 'booking_unavailable_day4' );
            delete_bk_option( 'booking_unavailable_day5' );
            delete_bk_option( 'booking_unavailable_day6' );

            delete_bk_option( 'booking_user_role_booking' );
            delete_bk_option( 'booking_user_role_addbooking' );
            delete_bk_option( 'booking_user_role_resources');
            delete_bk_option( 'booking_user_role_settings' );


            delete_bk_option( 'booking_email_reservation_adress');
            delete_bk_option( 'booking_email_reservation_from_adress');
            delete_bk_option( 'booking_email_reservation_subject');
            delete_bk_option( 'booking_email_reservation_content');

            delete_bk_option( 'booking_email_approval_adress');
            delete_bk_option( 'booking_email_approval_subject');
            delete_bk_option( 'booking_email_approval_content');

            delete_bk_option( 'booking_email_deny_adress');
            delete_bk_option( 'booking_email_deny_subject');
            delete_bk_option( 'booking_email_deny_content');

            delete_bk_option( 'booking_is_email_reservation_adress'  );
            delete_bk_option( 'booking_is_email_approval_adress'  );
            delete_bk_option( 'booking_is_email_deny_adress'  );

            delete_bk_option( 'booking_widget_title');
            delete_bk_option( 'booking_widget_show');
            delete_bk_option( 'booking_widget_type');
            delete_bk_option( 'booking_widget_calendar_count');
            delete_bk_option( 'booking_widget_last_field');

            global $wpdb;
            $wpdb->query(wpdevbk_db_prepare('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'booking'));
            $wpdb->query(wpdevbk_db_prepare('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'bookingdates'));

            // Delete all users booking windows states   //if ( false === $wpdb->query(wpdevbk_db_prepare( "DELETE FROM ". $wpdb->usermeta ." WHERE meta_key LIKE '%_booking_win_%'") ) ){  // Only WIN states
            if ( false === $wpdb->query(wpdevbk_db_prepare( "DELETE FROM ". $wpdb->usermeta ) . " WHERE meta_key LIKE '%booking_%'" ) ){    // All users data
                bk_error('Error during deleting user meta at DB',__FILE__,__LINE__);
                die();
            }
            // Delete or Drafts and Pending from demo sites
            if ( wpdev_bk_is_this_demo() ) {  // Delete all temp posts at the demo sites: (post_status = pending || draft) && ( post_type = post ) && (post_author != 1)
                  $postss = $wpdb->get_results(wpdevbk_db_prepare("SELECT * FROM $wpdb->posts WHERE ( post_status = 'pending' OR  post_status = 'draft' OR  post_status = 'auto-draft' OR  post_status = 'trash' OR  post_status = 'inherit' ) AND ( post_type='post' OR  post_type='revision') AND post_author != 1"));
                  foreach ($postss as $pp) { wp_delete_post( $pp->ID , true ); }
             }

           make_bk_action('wpdev_booking_deactivation');
        }
    }



    function  createExamples4Demo($my_bk_types=array()){ global $wpdb;
            $version = get_bk_version();

            if (class_exists('wpdev_bk_multiuser')) {

              if (empty($my_bk_types))   $my_bk_types=array(13,14,15,16,17);                // The booking resources with these IDs are exist in the Demo sites
              else                       shuffle($my_bk_types);

              // Get NUMBER of Bookings
              $bookings_count = $wpdb->get_results(wpdevbk_db_prepare( "SELECT COUNT(*) as count FROM ".$wpdb->prefix ."booking as bk" ));                      
              if (count($bookings_count)>0)   $bookings_count = $bookings_count[0]->count ;
              if ($bookings_count>=20) return;      
              
              
             $max_num_bookings = 4;                                                        // How many bookings exist  per resource   
              foreach ($my_bk_types as $resource_id) {                                     // Loop all resources                                        
                    $bk_type  = $resource_id;                                              // Booking Resource
                    $min_days = 2;
                    $max_days = 7;                    
                    $evry_one = $max_days+3;                                                  // Multiplier of interval between 2 dates of different bookings
                    $days_start_shift =  rand($max_days,(3*$max_days));//(ceil($max_num_bookings/2)) * $max_days;           // How long far ago we are start bookings    
                    
                for ($i = 0; $i < $max_num_bookings; $i++) {               
                    
                    $is_appr  = rand(0,1);                                                  // Pending | Approved
                    $num_days = rand($min_days,$max_days);                                  // Max Number of Dates for specific booking

                    $second_name = $this->getInitialValues4Demo('second_name');
                    $city =  $this->getInitialValues4Demo('city');
                    $start_time = '14:00';
                    $end_time   = '12:00';
                    
                    $form  = '';
                    $form .= 'text^name'.$bk_type.'^'.$this->getInitialValues4Demo('name').'~';
                    $form .= 'text^secondname'.$bk_type.'^'.$second_name.'~';
                    $form .= 'text^email'.$bk_type.'^'.$second_name.'.example@wpbookingcalendar.com~';
                    $form .= 'text^address'.$bk_type.'^'.$this->getInitialValues4Demo('adress').'~';
                    $form .= 'text^city'.$bk_type.'^'.$city[0].'~';
                    $form .= 'text^postcode'.$bk_type.'^'.$this->getInitialValues4Demo('postcode').'~';
                    $form .= 'text^country'.$bk_type.'^'.$city[1].'~';
                    $form .= 'text^phone'.$bk_type.'^'.$this->getInitialValues4Demo('phone').'~';
                    $form .= 'select-one^visitors'.$bk_type.'^1~';
                    //$form .= 'checkbox^children'.$bk_type.'[]^0~';
                    $form .= 'textarea^details'.$bk_type.'^'.$this->getInitialValues4Demo('info').'~';
                    $form .= 'coupon^coupon'.$bk_type.'^ ';

                    
                    $wp_bk_querie = "INSERT INTO ".$wpdb->prefix ."booking ( form, booking_type, cost, hash, modification_date ) VALUES
                                                       ( '".$form."', ".$bk_type .", ".rand(0,1000).", MD5('". time() . '_' . rand(1000,1000000)."'), NOW() ) ;";
                    $wpdb->query(wpdevbk_db_prepare($wp_bk_querie));
                    $temp_id = $wpdb->insert_id;
                    
                    $wp_queries_sub = "INSERT INTO ".$wpdb->prefix ."bookingdates (
                                         booking_id,
                                         booking_date,
                                         approved
                                        ) VALUES ";
                    for ($d_num = 0; $d_num < $num_days; $d_num++) {
                        $my_interval = ( $i*$evry_one + $d_num);
                        
                        $wp_queries_sub .= "( ". $temp_id .", DATE_ADD(CURDATE(), INTERVAL  -".$days_start_shift." day) + INTERVAL ".$my_interval." day  ,". $is_appr." ),";                                                
                    }
                    $wp_queries_sub = substr($wp_queries_sub,0,-1) . ";";
                    
                    $wpdb->query(wpdevbk_db_prepare($wp_queries_sub));                                        
                 }
              }
            } else if ( $version == 'free' ) {
                 if (empty($my_bk_types))   $my_bk_types=array(1,1);
                 else                       shuffle($my_bk_types);
                
                 for ($i = 0; $i < count($my_bk_types); $i++) {
                     
                    $bk_type = 1;//rand(1,4);
                    $is_appr = rand(0,1);
                    $evry_one = 2;//rand(1,7);
                    if (  $_SERVER['HTTP_HOST'] === 'dev'  ) {  
                        $evry_one = rand(1,14);//2;//rand(1,7);
                        $num_days = rand(1,7);//2;//rand(1,7);
                        $days_start_shift = rand(-28,0);
                    }
                    
                    
                    $second_name = $this->getInitialValues4Demo('second_name');
                    $form  = '';
                    $form .= 'text^name'.$bk_type.'^'.$this->getInitialValues4Demo('name').'~';
                    $form .= 'text^secondname'.$bk_type.'^'.$second_name.'~';
                    $form .= 'text^email'.$bk_type.'^'.$second_name.'.example@wpbookingcalendar.com~';
                    $form .= 'text^phone'.$bk_type.'^'.$this->getInitialValues4Demo('phone').'~';
                    $form .= 'textarea^details'.$bk_type.'^'.$this->getInitialValues4Demo('info');

                    $wp_bk_querie = "INSERT INTO ".$wpdb->prefix ."booking ( form, modification_date ) VALUES ( '".$form."', NOW()  ) ;";
                    $wpdb->query(wpdevbk_db_prepare($wp_bk_querie));
                    $temp_id = $wpdb->insert_id;
                    $wp_queries_sub = "INSERT INTO ".$wpdb->prefix ."bookingdates (
                                         booking_id,
                                         booking_date,
                                         approved
                                        ) VALUES ";
                    
                    if (  $_SERVER['HTTP_HOST'] === 'dev'  ) {  
                        for ($d_num = 0; $d_num < $num_days; $d_num++) {
                                $wp_queries_sub .= "( ". $temp_id .", CURDATE()+ INTERVAL ".($days_start_shift + 2*($i+1)*$evry_one + $d_num)." day  ,". $is_appr." ),";
                        }
                        $wp_queries_sub = substr($wp_queries_sub,0,-1) . ";";
                    } else {
                        $wp_queries_sub .= "( ". $temp_id .", CURDATE()+ INTERVAL ".(2*($i+1)*$evry_one+2)." day ,". $is_appr." ),
                                        ( ". $temp_id .", CURDATE()+ INTERVAL ".(2*($i+1)*$evry_one+3)." day  ,". $is_appr." ),
                                        ( ". $temp_id .", CURDATE()+ INTERVAL ".(2*($i+1)*$evry_one+4)." day ,". $is_appr." );";
                    }
                    
                    $wpdb->query(wpdevbk_db_prepare($wp_queries_sub));
                 }
            } else if ( $version == 'personal' ) {
                    $max_num_bookings = 8;                                                  // How many bookings exist     
                for ($i = 0; $i < $max_num_bookings; $i++) {               

                    $bk_type  = rand(1,4);                                                  // Booking Resource
                    $min_days = 1;
                    $max_days = 7;                    
                    $is_appr  = rand(0,1);                                                  // Pending | Approved
                    $evry_one = $max_days;                                                  // Multiplier of interval between 2 dates of different bookings
                    $num_days = rand($min_days,$max_days);                                  // Max Number of Dates for specific booking
                    $days_start_shift = -1 * (ceil($max_num_bookings/2)) * $max_days;       // How long far ago we are start bookings    

                    $second_name = $this->getInitialValues4Demo('second_name');
                    $form  = '';
                    $form .= 'text^name'.$bk_type.'^'.$this->getInitialValues4Demo('name').'~';
                    $form .= 'text^secondname'.$bk_type.'^'.$second_name.'~';
                    $form .= 'text^email'.$bk_type.'^'.$second_name.'.example@wpbookingcalendar.com~';
                    $form .= 'text^phone'.$bk_type.'^'.$this->getInitialValues4Demo('phone').'~';
                    $form .= 'select-one^visitors'.$bk_type.'^'.rand(1,4).'~';
                    $form .= 'select-one^children'.$bk_type.'^'.rand(0,3).'~';
                    $form .= 'textarea^details'.$bk_type.'^'.$this->getInitialValues4Demo('info');

                    $wp_bk_querie = "INSERT INTO ".$wpdb->prefix ."booking ( form, booking_type, hash,  modification_date ) VALUES
                                                       ( '".$form."', ".$bk_type .", MD5('". time() . '_' . rand(1000,1000000)."'), NOW() ) ;";
                    $wpdb->query(wpdevbk_db_prepare($wp_bk_querie));
                    $temp_id = $wpdb->insert_id;
                    
                    $wp_queries_sub = "INSERT INTO ".$wpdb->prefix ."bookingdates (
                                         booking_id,
                                         booking_date,
                                         approved
                                        ) VALUES ";
                    for ($d_num = 0; $d_num < $num_days; $d_num++) {
                        $wp_queries_sub .= "( ". $temp_id .", CURDATE()+ INTERVAL ".($days_start_shift + $i*$evry_one + $d_num)." day  ,". $is_appr." ),";
                    }
                    $wp_queries_sub = substr($wp_queries_sub,0,-1) . ";";
                    
                    $wpdb->query(wpdevbk_db_prepare($wp_queries_sub));
                 }
            } else if ( $version == 'biz_s' ) {
                    $max_num_bookings = 8;                                                  // How many bookings exist     
                for ($i = 0; $i < $max_num_bookings; $i++) {               

                    $bk_type  = rand(1,4);                                                  // Booking Resource
                    $min_days = 1;
                    $max_days = 1;                    
                    $is_appr  = rand(0,1);                                                  // Pending | Approved
                    $evry_one = $max_days;                                                  // Multiplier of interval between 2 dates of different bookings
                    $num_days = rand($min_days,$max_days);                                  // Max Number of Dates for specific booking
                    $days_start_shift = (ceil($max_num_bookings/4)) * $max_days;       // How long far ago we are start bookings    

                    $second_name = $this->getInitialValues4Demo('second_name');
                    $city =  $this->getInitialValues4Demo('city');
                    $range_time = $this->getInitialValues4Demo('rangetime');
                    $start_time = $range_time[0];
                    $end_time   = $range_time[1];

                    $form  = '';
                    $form .= 'select-one^rangetime'.$bk_type.'^'.$start_time.' - '.$end_time.'~';
                    $form .= 'text^name'.$bk_type.'^'.$this->getInitialValues4Demo('name').'~';
                    $form .= 'text^secondname'.$bk_type.'^'.$second_name.'~';
                    $form .= 'text^email'.$bk_type.'^'.$second_name.'.example@wpbookingcalendar.com~';
                    $form .= 'text^address'.$bk_type.'^'.$this->getInitialValues4Demo('adress').'~';
                    $form .= 'text^city'.$bk_type.'^'.$city[0].'~';
                    $form .= 'text^postcode'.$bk_type.'^'.$this->getInitialValues4Demo('postcode').'~';
                    $form .= 'text^country'.$bk_type.'^'.$city[1].'~';
                    $form .= 'text^phone'.$bk_type.'^'.$this->getInitialValues4Demo('phone').'~';
                    $form .= 'select-one^visitors'.$bk_type.'^'.rand(1,4).'~';
                    $form .= 'checkbox^children'.$bk_type.'[]^'.rand(0,3).'~';
                    $form .= 'textarea^details'.$bk_type.'^'.$this->getInitialValues4Demo('info');

                    $wp_bk_querie = "INSERT INTO ".$wpdb->prefix ."booking ( form, booking_type, cost, hash, modification_date ) VALUES
                                                       ( '".$form."', ".$bk_type .", ".rand(0,1000).", MD5('". time() . '_' . rand(1000,1000000)."'), NOW() ) ;";
                    $wpdb->query(wpdevbk_db_prepare($wp_bk_querie));
                    $temp_id = $wpdb->insert_id;
                    
                    $wp_queries_sub = "INSERT INTO ".$wpdb->prefix ."bookingdates (
                                         booking_id,
                                         booking_date,
                                         approved
                                        ) VALUES ";
                    for ($d_num = 0; $d_num < $num_days; $d_num++) {
                        $my_interval = ( $i*$evry_one + $d_num);
//                        $wp_queries_sub .= "( ". $temp_id .", CURDATE()+ INTERVAL \"".($days_start_shift + $i*$evry_one + $d_num)." ".$start_time.":01\" DAY_SECOND  ,". $is_appr." ),";
//                        $wp_queries_sub .= "( ". $temp_id .", CURDATE()+ INTERVAL \"".($days_start_shift + $i*$evry_one + $d_num)." ".$end_time  .":02\" DAY_SECOND  ,". $is_appr." ),";                        
                        $wp_queries_sub .= "( ". $temp_id .", DATE_ADD(CURDATE(), INTERVAL -".$days_start_shift." DAY) + INTERVAL \"".$my_interval." ".$start_time.":01\" DAY_SECOND  ,". $is_appr." ),";                        
                        $wp_queries_sub .= "( ". $temp_id .", DATE_ADD(CURDATE(), INTERVAL -".$days_start_shift." DAY) + INTERVAL \"".$my_interval." ".$end_time.":02\" DAY_SECOND  ,". $is_appr." ),";
                    }
                    $wp_queries_sub = substr($wp_queries_sub,0,-1) . ";";
                    
                    $wpdb->query(wpdevbk_db_prepare($wp_queries_sub));
                 }
            } else if ( $version == 'biz_m' ) {
                    $max_num_bookings = 8;                                                  // How many bookings exist     
                for ($i = 0; $i < $max_num_bookings; $i++) {               

                    $bk_type  = rand(1,4);                                                  // Booking Resource
                    $min_days = 3;
                    $max_days = 7;                    
                    $is_appr  = rand(0,1);                                                  // Pending | Approved
                    $evry_one = $max_days;                                                  // Multiplier of interval between 2 dates of different bookings
                    $num_days = rand($min_days,$max_days);                                  // Max Number of Dates for specific booking
                    $days_start_shift =  (ceil($max_num_bookings/2)) * $max_days;       // How long far ago we are start bookings    

                    $second_name = $this->getInitialValues4Demo('second_name');
                    $city =  $this->getInitialValues4Demo('city');
                    $start_time = '14:00';
                    $end_time   = '12:00';
                    
                    $form  = '';
                    $form .= 'text^name'.$bk_type.'^'.$this->getInitialValues4Demo('name').'~';
                    $form .= 'text^secondname'.$bk_type.'^'.$second_name.'~';
                    $form .= 'text^email'.$bk_type.'^'.$second_name.'.example@wpbookingcalendar.com~';
                    $form .= 'text^address'.$bk_type.'^'.$this->getInitialValues4Demo('adress').'~';
                    $form .= 'text^city'.$bk_type.'^'.$city[0].'~';
                    $form .= 'text^postcode'.$bk_type.'^'.$this->getInitialValues4Demo('postcode').'~';
                    $form .= 'text^country'.$bk_type.'^'.$city[1].'~';
                    $form .= 'text^phone'.$bk_type.'^'.$this->getInitialValues4Demo('phone').'~';
                    $form .= 'select-one^visitors'.$bk_type.'^'.rand(1,4).'~';
                    $form .= 'checkbox^children'.$bk_type.'[]^'.rand(0,3).'~';
                    $form .= 'textarea^details'.$bk_type.'^'.$this->getInitialValues4Demo('info').'~';
                    $form .= 'text^starttime'.$bk_type.'^'.$start_time.'~';
                    $form .= 'text^endtime'.$bk_type.'^'.$end_time;

                    $wp_bk_querie = "INSERT INTO ".$wpdb->prefix ."booking ( form, booking_type, cost, hash, modification_date ) VALUES
                                                       ( '".$form."', ".$bk_type .", ".rand(0,1000).", MD5('". time() . '_' . rand(1000,1000000)."'), NOW() ) ;";
                    $wpdb->query(wpdevbk_db_prepare($wp_bk_querie));
                    $temp_id = $wpdb->insert_id;
                    
                    $wp_queries_sub = "INSERT INTO ".$wpdb->prefix ."bookingdates (
                                         booking_id,
                                         booking_date,
                                         approved
                                        ) VALUES ";
                    for ($d_num = 0; $d_num < $num_days; $d_num++) {
                        $my_interval = ( $i*$evry_one + $d_num);
                        if ($d_num == 0) {                                       // Check In
                            $wp_queries_sub .= "( ". $temp_id .", DATE_ADD(CURDATE(), INTERVAL  -".$days_start_shift." day) + INTERVAL \"".$my_interval." ".$start_time.":01\" DAY_SECOND  ,". $is_appr." ),";
                        } elseif ($d_num == ($num_days-1) ) {                   // Check Out
                            $wp_queries_sub .= "( ". $temp_id .", DATE_ADD(CURDATE(), INTERVAL -".$days_start_shift." day) + INTERVAL \"".$my_interval." ".$end_time.":02\" DAY_SECOND  ,". $is_appr." ),";
                        } else {
                            $wp_queries_sub .= "( ". $temp_id .", DATE_ADD(CURDATE(), INTERVAL  -".$days_start_shift." day) + INTERVAL ".$my_interval." day  ,". $is_appr." ),";
                        }                        
                    }
                    $wp_queries_sub = substr($wp_queries_sub,0,-1) . ";";
                    
                    $wpdb->query(wpdevbk_db_prepare($wp_queries_sub));
                 }

            } else if ( $version == 'biz_l' ) {
                    $max_num_bookings = 12;                                                  // How many bookings exist     
                for ($res_groups = 0; $res_groups < 2; $res_groups++)    
                for ($i = 0; $i < $max_num_bookings; $i++) {               
                    if($res_groups) 
                        $bk_type  = rand(1,6);                                                  // Booking Resource
                    else $bk_type  = rand(7,12);                                                  // Booking Resource
                    $min_days = 2;
                    $max_days = 7;                    
                    $is_appr  = rand(0,1);                                                  // Pending | Approved
                    $evry_one = $max_days;                                                  // Multiplier of interval between 2 dates of different bookings
                    $num_days = rand($min_days,$max_days);                                  // Max Number of Dates for specific booking
                    $days_start_shift =  (ceil($max_num_bookings/2)) * $max_days;       // How long far ago we are start bookings    

                    $second_name = $this->getInitialValues4Demo('second_name');
                    $city =  $this->getInitialValues4Demo('city');
                    $start_time = '14:00';
                    $end_time   = '12:00';

                    
                    $form  = '';
                    $form .= 'text^name'.$bk_type.'^'.$this->getInitialValues4Demo('name').'~';
                    $form .= 'text^secondname'.$bk_type.'^'.$second_name.'~';
                    $form .= 'text^email'.$bk_type.'^'.$second_name.'.example@wpbookingcalendar.com~';
                    $form .= 'text^address'.$bk_type.'^'.$this->getInitialValues4Demo('adress').'~';
                    $form .= 'text^city'.$bk_type.'^'.$city[0].'~';
                    $form .= 'text^postcode'.$bk_type.'^'.$this->getInitialValues4Demo('postcode').'~';
                    $form .= 'text^country'.$bk_type.'^'.$city[1].'~';
                    $form .= 'text^phone'.$bk_type.'^'.$this->getInitialValues4Demo('phone').'~';
                    $form .= 'select-one^visitors'.$bk_type.'^1~';
                    //$form .= 'checkbox^children'.$bk_type.'[]^0~';
                    $form .= 'textarea^details'.$bk_type.'^'.$this->getInitialValues4Demo('info').'~';
                    $form .= 'coupon^coupon'.$bk_type.'^ ';

                    $wp_bk_querie = "INSERT INTO ".$wpdb->prefix ."booking ( form, booking_type, cost, hash, modification_date ) VALUES
                                                       ( '".$form."', ".$bk_type .", ".rand(0,1000).", MD5('". time() . '_' . rand(1000,1000000)."'), NOW() ) ;";
                    $wpdb->query(wpdevbk_db_prepare($wp_bk_querie));
                    $temp_id = $wpdb->insert_id;
                    
                    $wp_queries_sub = "INSERT INTO ".$wpdb->prefix ."bookingdates (
                                         booking_id,
                                         booking_date,
                                         approved
                                        ) VALUES ";
                    for ($d_num = 0; $d_num < $num_days; $d_num++) {
                        $my_interval = ( $i*$evry_one + $d_num);
                        
                        $wp_queries_sub .= "( ". $temp_id .", DATE_ADD(CURDATE(), INTERVAL  -".$days_start_shift." day) + INTERVAL ".$my_interval." day  ,". $is_appr." ),";                                                
                    }
                    $wp_queries_sub = substr($wp_queries_sub,0,-1) . ";";
                    
                    $wpdb->query(wpdevbk_db_prepare($wp_queries_sub));
                 }
            }
    }


    function getInitialValues4Demo($type) {
        $names = array('Jacob', 'Michael', 'Daniel', 'Anthony', 'William', 'Emma', 'Sophia', 'Kamila', 'Isabella', 'Jack', 'Daniel', 'Matthew',
                'Olivia', 'Emily', 'Grace', 'Jessica', 'Joshua', 'Harry', 'Thomas', 'Oliver', 'Jack' );
        $second_names = array(  'Smith', 'Johnson', 'Widams', 'Brown', 'Jones', 'Miller', 'Davis', 'Garcia', 'Rodriguez', 'Wilyson', 'Gonzalez', 'Gomez',
                'Taylor', 'Bron', 'Wilson', 'Davies', 'Robinson', 'Evans', 'Walker', 'Jackson', 'Clarke' );
        $city =    array( 'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'San Antonio', 'San Diego', 'San Jose', 'Detroit',
                'San Francisco', 'Jacksonville', 'Austin',
                'London', 'Birmingham', 'Leeds', 'Glasgow', 'Sheffield', 'Bradford', 'Edinburgh', 'Liverpool', 'Manchester' );
        $adress =   array('30 Mortensen Avenue', '144 Hitchcock Rd', '222 Lincoln Ave', '200 Lincoln Ave', '65 West Alisal St',
                '426 Work St', '65 West Alisal Street', '159 Main St', '305 Jonoton Avenue', '423 Caiptown Rd', '34 Linoro Ave',
                '50 Voro Ave', '15 East St', '226 Middle St', '35 West Town Street', '59 Other St', '50 Merci Ave', '15 Dolof St',
                '226 Gordon St', '35 Sero Street', '59 Exit St' );
        $country = array( 'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'UK','UK','UK','UK','UK','UK','UK','UK','UK' );

        $range_times = array( array("10:00","12:00"), array("12:00","14:00"), array("14:00","16:00"), array("16:00","18:00"), array("18:00","20:00") );

        switch ($type) {
            case 'rangetime':
                return $range_times[ rand(0 , (count($range_times)-1) ) ] ;
                break;
            case 'name':
                return $names[ rand(0 , (count($names)-1) ) ] ;
                break;
            case 'second_name':
                return $second_names[ rand(0 , (count($second_names)-1) ) ] ;
                break;
            case 'adress':
                return $adress[ rand(0 , (count($adress)-1) ) ] ;
                break;
            case 'city':
                $city_num = rand(0 , (count($city)-1) )  ;
                return array( $city[$city_num], $country[$city_num]) ;
                break;
            case 'postcode':
                return (rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9));
                break;
            case 'phone':
                return (rand(0,9).rand(0,9).rand(0,9).'-'.rand(0,9).rand(0,9).'-'.rand(0,9).rand(0,9)) ;
                break;
            case 'starttime':
                return ('0'.rand(0,9) . ':' .rand(1,3).'0' );
                break;
            case 'endtime':
                return (rand(12,23) . ':' .rand(1,3).'0' );
                break;
            case 'visitors':
                return rand(1,4);
                break;
            default:
                return '';
                break;
        }

    }


    function setDefaultInitialValues($evry_one = 1) {
        global $wpdb;
        $names = array(  'Jacob', 'Michael', 'Daniel', 'Anthony', 'William', 'Emma', 'Sophia', 'Kamila', 'Isabella', 'Jack', 'Daniel', 'Matthew',
                'Olivia', 'Emily', 'Grace', 'Jessica', 'Joshua', 'Harry', 'Thomas', 'Oliver', 'Jack' );
        $second_names = array(  'Smith', 'Johnson', 'Widams', 'Brown', 'Jones', 'Miller', 'Davis', 'Garcia', 'Rodriguez', 'Wilyson', 'Gonzalez', 'Gomez',
                'Taylor', 'Bron', 'Wilson', 'Davies', 'Robinson', 'Evans', 'Walker', 'Jackson', 'Clarke' );
        $city =    array(       'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'San Antonio', 'San Diego', 'San Jose', 'Detroit',
                'San Francisco', 'Jacksonville', 'Austin',
                'London', 'Birmingham', 'Leeds', 'Glasgow', 'Sheffield', 'Bradford', 'Edinburgh', 'Liverpool', 'Manchester' );
        $adress =   array(      '30 Mortensen Avenue', '144 Hitchcock Rd', '222 Lincoln Ave', '200 Lincoln Ave', '65 West Alisal St',
                '426 Work St', '65 West Alisal Street', '159 Main St', '305 Jonoton Avenue', '423 Caiptown Rd', '34 Linoro Ave',
                '50 Voro Ave', '15 East St', '226 Middle St', '35 West Town Street', '59 Other St', '50 Merci Ave', '15 Dolof St',
                '226 Gordon St', '35 Sero Street', '59 Exit St' );
        $country = array( 'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'UK','UK','UK','UK','UK','UK','UK','UK','UK' );
        $info = array(    '  ' ,'  ' ,'  ' ,'  ' ,'  ' ,'  ' ,'  ' ,'  ' ,'  ' ,'  ' ,'  ' ,'  ' ,'  ','  ','  ','  ','  ','  ','  ','  ','  ' );

        for ($i = 0; $i < count($names); $i++) {
            if ( ($i % $evry_one) !==0 ) {
                continue;
            }
            $bk_type = rand(1,4);
            $is_appr = rand(0,1);

            $start_time = '0'.rand(0,9) . ':' .rand(1,3).'0' ;
            $end_time     = rand(12,23) . ':' .rand(1,3).'0' ;

            $form = 'text^starttime'.$bk_type.'^'.$start_time.'~';
            $form .='text^endtime'.$bk_type.'^'.$end_time.'~';
            $form .='text^name'.$bk_type.'^'.$names[$i].'~';
            $form .='text^secondname'.$bk_type.'^'.$second_names[$i].'~';
            $form .='text^email'.$bk_type.'^'.$second_names[$i].'.example@wpbookingcalendar.com~';
            $form .='text^address'.$bk_type.'^'.$adress[$i].'~';
            $form .='text^city'.$bk_type.'^'.$city[$i].'~';
            $form .='text^postcode'.$bk_type.'^'.rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).'~';
            $form .='text^country'.$bk_type.'^'.$country[$i].'~';
            $form .='text^phone'.$bk_type.'^'.rand(0,9).rand(0,9).rand(0,9).'-'.rand(0,9).rand(0,9).'-'.rand(0,9).rand(0,9).'~';
            $form .='select-one^visitors'.$bk_type.'^'.rand(0,9).'~';
            $form .='checkbox^children'.$bk_type.'[]^false~';
            $form .='textarea^details'.$bk_type.'^'.$info[$i];

            $wp_bk_querie = "INSERT INTO ".$wpdb->prefix ."booking ( form, booking_type, cost, hash ) VALUES
                                               ( '".$form."', ".$bk_type .", ".rand(0,1000).", MD5('". time() . '_' . rand(1000,1000000)."') ) ;";
            $wpdb->query(wpdevbk_db_prepare($wp_bk_querie));

            $temp_id = $wpdb->insert_id;
            $wp_queries_sub = "INSERT INTO ".$wpdb->prefix ."bookingdates (
                                 booking_id,
                                 booking_date,
                                 approved
                                ) VALUES
                                ( ". $temp_id .", CURDATE()+ INTERVAL \"".(2*($i+1)*$evry_one+2)." ".$start_time.":01"."\" DAY_SECOND ,". $is_appr." ),
                                ( ". $temp_id .", CURDATE()+ INTERVAL ".(2*($i+1)*$evry_one+3)." day  ,". $is_appr." ),
                                ( ". $temp_id .", CURDATE()+ INTERVAL \"".(2*($i+1)*$evry_one+4)." ".$end_time.":02"."\" DAY_SECOND ,". $is_appr." );";
            $wpdb->query(wpdevbk_db_prepare($wp_queries_sub));
        }
    }

    // Upgrade during bulk upgrade of plugins
    function install_in_bulk_upgrade( $return, $hook_extra ){

        if ( is_wp_error($return) )
		return $return;


        if (isset($hook_extra))
            if (isset($hook_extra['plugin'])) {
                $file_name = basename( WPDEV_BK_FILE );
                $pos = strpos( $hook_extra['plugin']  ,  trim($file_name)  );
                if ($pos !== false) {
                        $this->wpdev_booking_activate();
                }
            }
        return $return;
    }

// </editor-fold>


    
    // <editor-fold defaultstate="collapsed" desc="  M A I N T E N C E      F U N C T I O N S  ">
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///   M A I N T E N C E      F U N C T I O N S     ////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function wpdev_booking_technical_booking_section(){
        $link = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-option';
        if ( (isset($_REQUEST['reindex_sort_data'])) && ($_REQUEST['reindex_sort_data']=='1') ){
            $this->reindex_booking_db();
        }
        ?><div class='meta-box technical-booking-section'>
            <div <?php $my_close_open_win_id = 'bk_general_settings_technical_section'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle','wpdev-booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                <h3 class='hndle'><span><?php _e('Technical support section', 'wpdev-booking'); ?></span></h3> <div class="inside">
                    
                    <table class="form-table"><tbody>
                        <tr valign="top">
                            <th scope="row"><label for="is_reindex_booking_data" ><?php _e('Reindex booking data', 'wpdev-booking'); ?>:</label></th>
                            <td>
                                <input class="button" id="is_reindex_booking_data" type="button" value="<?php  _e('Reindex', 'wpdev-booking');; ?>" name="is_reindex_booking_data"
                                    onclick="javascript: window.location.href='<?php echo $link ;?>&reindex_sort_data=1';"
                                       />                                
                            </td>
                        </tr>
                        <tr><td colspan="2">
                                <p class="description"><?php _e(' Click, if you want to reindex booking data by booking dates sort field (Your installation/update of the plugin must be successful).', 'wpdev-booking');?></p>
                        </td></tr>
                    </tbody></table>
                    
            </div></div></div><?php
    }


    function reindex_booking_db(){ global $wpdb;
        
        if ( $_SERVER['QUERY_STRING'] == 'page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-option&reindex_sort_data=1' )
             $is_show_messages = true;
        else $is_show_messages = false;

        if ($is_show_messages)  {
            // Hide all settings
            ?>
            <style type="text/css" rel="stylesheet" >
                #post_option .meta-box {
                    display:none;
                }
                #post_option .button-primary {
                    display:none;
                }
                #post_option .technical-booking-section {
                    display:block;
                }
            </style>
            <?php
        }

        if  ($this->is_field_in_table_exists('booking','sort_date') == 0) {
            $simple_sql  = "ALTER TABLE ".$wpdb->prefix ."booking ADD sort_date datetime AFTER booking_id";
            $wpdb->query(wpdevbk_db_prepare($simple_sql));
        }

        // Refill the sort date index.
        if  ($this->is_field_in_table_exists('booking','sort_date') != 0) {
            
            //1. Select  all bookings ID, where sort_date is NULL in wp_booking
            $sql  = " SELECT booking_id as id" ;
            $sql .= " FROM ".$wpdb->prefix ."booking as bk" ;
            $sql .= " WHERE sort_date IS NULL" ;
            $bookings_res = $wpdb->get_results(  $sql  );
            
            if ($is_show_messages)  printf(__('%s Found %s not indexed bookings %s', 'wpdev-booking'),' ',count($bookings_res), '<br/>');


            if (count($bookings_res) > 0 ) {
                $id_string = '';
                foreach ($bookings_res as $value) {  $id_string .= $value->id . ','; }
                $id_string = substr($id_string,0,-1);

                //2. Select all (FIRST ??) booking_date, where booking_id = booking_id from #1 in wp_bookingdates
                $sql  = " SELECT booking_id as id, booking_date as date" ;
                $sql .= " FROM ".$wpdb->prefix ."bookingdates as bdt" ;
                $sql .= " WHERE booking_id IN ( ". $id_string ." ) GROUP BY bdt.booking_id ORDER BY bdt.booking_date " ;

                $sort_date_array = $wpdb->get_results(  $sql  );

                if ($is_show_messages) printf(__('%s Finish getting sort dates. %s', 'wpdev-booking'),' ','<br/>');

                //3. Insert  that firtst date into the bookings in wp_booking
                $ii=0;
                foreach ($sort_date_array as $value) { $ii++;
                    $sql  = "UPDATE ".$wpdb->prefix ."booking as bdt ";
                    $sql .= " SET sort_date = '".$value->date. "' WHERE booking_id  = ". $value->id . " ";
                    $wpdb->query( $sql );

                    if ($is_show_messages) printf(__('Updated booking: %s', 'wpdev-booking'),$value->id  . '  ['.$ii.' / '.count($bookings_res).'] <br/>');
                }
            }

        }


    }

    // </editor-fold>

}
}
?>