<?php
if ( (! isset( $_GET['merchant_return_link'] ) ) && (! isset( $_GET['payed_booking'] ) ) && ( (! isset($_GET['pay_sys']) ) || ($_GET['pay_sys'] != 'authorizenet') ) && (!function_exists ('get_option'))   ) { die('You do not have permission to direct access to this file !!!'); }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Getting Ajax requests
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ( isset( $_POST['ajax_action'] ) ) {
    define('DOING_AJAX', true);

    if ( class_exists('wpdev_bk_personal')) { $wpdev_bk_personal_in_ajax = new wpdev_bk_personal(); }
    wpdev_bk_ajax_responder();
}


if ( 
     ( isset( $_GET['payed_booking']) )  || 
     ( isset( $_GET['merchant_return_link']) ) ||
     ( (isset($_GET['pay_sys']) ) && ($_GET['pay_sys'] == 'authorizenet') ) 
   ) {

    if ( class_exists('wpdev_bk_personal')) { $wpdev_bk_personal_in_ajax = new wpdev_bk_personal(); }

    if (function_exists ('wpdev_bk_update_pay_status')) wpdev_bk_update_pay_status();
    die;
} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//    A J A X     R e s p o n d e r     Real Ajax with jQuery sender     ///////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Verify the nonce.    
function wpdev_check_nonce_in_admin_panel( $action_check = 'wpbc_ajax_admin_nonce' ){    
    
    $nonce = ( isset($_REQUEST['wpbc_nonce']) ) ? $_REQUEST['wpbc_nonce'] : '';
    
    if ( ! wp_verify_nonce( $nonce, $action_check ) ) {                         // This nonce is not valid.                                     
        echo "<div class='wpdevbk'><div class='alert alert-danger'>" .sprintf(__('%sError!%s Request do not pass security check! Please refresh the page and try one more time.','wpdev-booking'),'<strong>','</strong>') . "</div></div>";
        die;                
    } 
}
        
            

function wpdev_bk_ajax_responder() {

    global $wpdb;
    $action = $_POST['ajax_action'];

    if  (isset($_POST['wpdev_active_locale'])) {    // Reload locale according request parameter
            global  $l10n;
            if (isset($l10n['wpdev-booking'])) unset($l10n['wpdev-booking']);

            if(! defined('WPDEV_BK_LOCALE_RELOAD') ) define('WPDEV_BK_LOCALE_RELOAD', $_POST['wpdev_active_locale']);

            // Reload locale settings, its required for the correct  dates format
            if (isset($l10n['default'])) unset($l10n['default']);               // Unload locale     
            add_filter('locale', 'getBookingLocale',999);                       // Set filter to load the locale of the Booking Calendar
            load_default_textdomain();                                          // Load default locale            
            global $wp_locale;
            $wp_locale = new WP_Locale();                                       // Reload class

            loadLocale(WPDEV_BK_LOCALE_RELOAD);
    }

    switch ( $action ) :

        case  'INSERT_INTO_TABLE':             
            wpdev_check_nonce_in_admin_panel( $action );            
            wpdev_bk_insert_new_booking();
            die();
            break;

        case 'UPDATE_READ_UNREAD':
            wpdev_check_nonce_in_admin_panel();
            make_bk_action('check_multiuser_params_for_client_side_by_user_id', $_POST['user_id'] );

            $is_read_or_unread = $_POST[ "is_read_or_unread" ];
            if ($is_read_or_unread == 1)   $is_new = '1';
            else                           $is_new = '0';

            $id_of_new_bookings       = $_POST[ "booking_id" ];
            $arrayof_bookings_id    = explode('|',$id_of_new_bookings);
            $user_id = $_POST[ "user_id" ];
                    
            renew_NumOfNewBookings(  $arrayof_bookings_id, $is_new , $user_id );
//debuge($arrayof_bookings_id, 'all_array');

            ?>  <script type="text/javascript">
                    <?php foreach ($arrayof_bookings_id as $bk_id) {
                            if ( $bk_id == 'all' ) 
                                $bk_id = 0;
                            if ($is_new == '1') { ?>
                                set_booking_row_unread(<?php echo $bk_id ?>);
                            <?php } else { ?>
                                set_booking_row_read(<?php echo $bk_id ?>);                                
                            <?php }?>
                    <?php } ?>
                    <?php if ($is_new == '1') { ?>
                    //    var my_num = parseInt(jQuery('.bk-update-count').text()) + parseInt(1<?php echo '*' . count($arrayof_bookings_id); ?>);
                    <?php } else { ?>
                    //    var my_num = parseInt(jQuery('.bk-update-count').text()) - parseInt(1<?php echo '*' . count($arrayof_bookings_id); ?>);
                    <?php } ?>
                    //jQuery('.bk-update-count').html( my_num );
                    document.getElementById('ajax_message').innerHTML = '<?php if ($is_new == '1') { echo __('Set as Read', 'wpdev-booking'); } else { echo __('Set as Unread', 'wpdev-booking'); } ?>';
                    jQuery('#ajax_message').fadeOut(1000);
                </script> <?php
            die();

            break;

        case 'UPDATE_APPROVE' :
            wpdev_check_nonce_in_admin_panel();
            make_bk_action('check_multiuser_params_for_client_side_by_user_id', $_POST['user_id'] );

            // Approve or Reject
            $is_approve_or_pending = $_POST[ "is_approve_or_pending" ];
            if ($is_approve_or_pending == 1)   $is_approve_or_pending = '1';
            else                        $is_approve_or_pending = '0';
            // Booking ID
            $booking_id       = $_POST[ "booking_id" ];
            $approved_id    = explode('|',$booking_id);
            if (! isset($_POST["denyreason"])) $_POST["denyreason"]='';
            $denyreason     = $_POST["denyreason"];
            $is_send_emeils = $_POST["is_send_emeils"];
            

            if ( (count($approved_id)>0) && ($approved_id !==false)) {

                $approved_id_str = join( ',', $approved_id);

                if ( false === $wpdb->query( wpdevbk_db_prepare("UPDATE ".$wpdb->prefix ."bookingdates SET approved = '".$is_approve_or_pending."' WHERE booking_id IN ($approved_id_str)") ) ){
                    ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating to DB' ,__FILE__,__LINE__); ?></div>'; </script> <?php
                    die();
                }

                renew_NumOfNewBookings( explode(',', $approved_id_str) );

                if ($is_approve_or_pending == '1') {
                    sendApproveEmails($approved_id_str, $is_send_emeils,$denyreason);
                    $all_bk_id_what_canceled = apply_bk_filter('cancel_pending_same_resource_bookings_for_specific_dates', false, $approved_id_str );         
//debuge($pending_bookings);
                } else
                    sendDeclineEmails($approved_id_str, $is_send_emeils,$denyreason);

                ?>  <script type="text/javascript">
                        <?php foreach ($approved_id as $bk_id) {
                                if ($is_approve_or_pending == '1') { ?>
                                    set_booking_row_approved_in_timeline(<?php echo $bk_id ?>);
                                    set_booking_row_approved(<?php echo $bk_id ?>);
                                    set_booking_row_read(<?php echo $bk_id ?>);
                                <?php } else { ?>
                                    set_booking_row_pending_in_timeline(<?php echo $bk_id ?>);
                                    set_booking_row_pending(<?php echo $bk_id ?>);
                                <?php }?>
                        <?php } ?>
                        document.getElementById('ajax_message').innerHTML = '<?php if ($is_approve_or_pending == '1') { echo __('Set as Approved', 'wpdev-booking'); } else { echo __('Set as Pending', 'wpdev-booking'); } ?>';
                        jQuery('#ajax_message').fadeOut(1000);
                    </script> <?php
                die();
            }
            break;

        case 'DELETE_APPROVE' :
            wpdev_check_nonce_in_admin_panel();
            make_bk_action('check_multiuser_params_for_client_side_by_user_id', $_POST['user_id'] );

            $booking_id       = $_POST[ "booking_id" ];         // Booking ID
            if (! isset($_POST["denyreason"])) $_POST["denyreason"] = '';
            $denyreason     = $_POST["denyreason"];
            if ( ( $denyreason == __('Reason for cancellation here', 'wpdev-booking')) || ( $denyreason == __('Reason of cancellation here', 'wpdev-booking')) || ( $denyreason == 'Reason of cancel here') )  $denyreason = '';
            $is_send_emeils = $_POST["is_send_emeils"];
            $approved_id    = explode('|',$booking_id);

            if ( (count($approved_id)>0) && ($approved_id !=false) && ($approved_id !='')) {

                $approved_id_str = join( ',', $approved_id);

                sendDeclineEmails($approved_id_str, $is_send_emeils,$denyreason);


                if ( false === $wpdb->query( wpdevbk_db_prepare("DELETE FROM ".$wpdb->prefix ."bookingdates WHERE booking_id IN ($approved_id_str)") ) ){
                    ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during deleting dates at DB' ,__FILE__,__LINE__); ?></div>'; </script> <?php
                    die();
                }

                if ( false === $wpdb->query(wpdevbk_db_prepare( "DELETE FROM ".$wpdb->prefix ."booking WHERE booking_id IN ($approved_id_str)") ) ){
                    ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during deleting reservation at DB',__FILE__,__LINE__ ); ?></div>'; </script> <?php
                    die();
                }

                ?>
                    <script type="text/javascript">
                        <?php foreach ($approved_id as $bk_id) { ?>
                            set_booking_row_deleted_in_timeline(<?php echo $bk_id ?>);
                            set_booking_row_deleted(<?php echo $bk_id ?>);
                        <?php } ?>
                        document.getElementById('ajax_message').innerHTML = '<?php echo __('Deleted', 'wpdev-booking'); ?>';
                        jQuery('#ajax_message').fadeOut(1000);
                    </script>
                <?php
                die();
            }
            break;

        case 'DELETE_BY_VISITOR':
            wpdev_check_nonce_in_admin_panel( $action );
            make_bk_action('wpdev_delete_booking_by_visitor');
            break;

        case 'SAVE_BK_COST':
            wpdev_check_nonce_in_admin_panel();
            make_bk_action('wpdev_save_bk_cost');
            break;

        case 'SEND_PAYMENT_REQUEST':
            wpdev_check_nonce_in_admin_panel();
            make_bk_action('wpdev_send_payment_request');
            break;


        case 'CHANGE_PAYMENT_STATUS':
            wpdev_check_nonce_in_admin_panel();
            make_bk_action('wpdev_change_payment_status');
            break;

        case 'UPDATE_BK_RESOURCE_4_BOOKING':
            wpdev_check_nonce_in_admin_panel();
            make_bk_action('wpdev_updating_bk_resource_of_booking');
            break;


        case 'UPDATE_REMARK':
            wpdev_check_nonce_in_admin_panel();
            make_bk_action('wpdev_updating_remark');
            break;

        case 'DELETE_BK_FORM':
            wpdev_check_nonce_in_admin_panel();
            make_bk_action('wpdev_delete_booking_form');
            break;

        case 'USER_SAVE_WINDOW_STATE':
            wpdev_check_nonce_in_admin_panel();
            update_user_option($_POST['user_id'],'booking_win_' . $_POST['window'] ,$_POST['is_closed']);
            die();
            break;

        case 'CALCULATE_THE_COST':
            wpdev_check_nonce_in_admin_panel( $action );            
            make_bk_action('wpdev_ajax_show_cost');
            die();
            break;

        case 'BOOKING_SEARCH':
            wpdev_check_nonce_in_admin_panel( $action );
            make_bk_action('wpdev_ajax_booking_search');
            die();
            break;

        case 'CHECK_BK_NEWS':
            wpdev_check_nonce_in_admin_panel();
            wpdev_ajax_check_bk_news();
            die();
            break;
        case 'CHECK_BK_FEATURES':
            wpdev_check_nonce_in_admin_panel();
            wpdev_ajax_check_bk_news('info/features/');
            die();
            break;
        case 'CHECK_BK_VERSION':
            wpdev_check_nonce_in_admin_panel();
            wpdev_ajax_check_bk_version();
            die();
            break;

        case 'SAVE_BK_LISTING_FILTER':
            wpdev_check_nonce_in_admin_panel();
            make_bk_action('wpdev_ajax_save_bk_listing_filter');
            die();
            break;
        case 'DELETE_BK_LISTING_FILTER':
            wpdev_check_nonce_in_admin_panel();
            make_bk_action('wpdev_ajax_delete_bk_listing_filter');
            die();
            break;
        case 'EXPORT_BOOKINGS_TO_CSV'    :
            wpdev_check_nonce_in_admin_panel();
            make_bk_action('wpdev_ajax_export_bookings_to_csv');
            die();

        default:
            wpdev_check_nonce_in_admin_panel();
            if (function_exists ('wpdev_pro_bk_ajax')) wpdev_pro_bk_ajax();
            die();

        endswitch;
}





function wpdev_bk_insert_new_booking(){  global $wpdb;

    make_bk_action('check_multiuser_params_for_client_side', $_POST[  "bktype" ] );

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Define init variables
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $dates          = $_POST[ "dates" ];
    $bktype         = $_POST[ "bktype" ];
    $formdata       = $_POST[ "form" ];
    $formdata = escape_any_xss($formdata);
    $is_send_emeils = 1;        if (isset($_POST["is_send_emeils"])) $is_send_emeils = $_POST["is_send_emeils"];

    $my_booking_id  = 0;
    $my_booking_hash= '';


    if (function_exists ('get_booking_title')) $bk_title = get_booking_title( $bktype );
    else $bk_title = '';

    if (isset($_POST['my_booking_hash'])) {
        $my_booking_hash = $_POST['my_booking_hash'];
        if ($my_booking_hash!='') {
            $my_booking_id_type = false;
            $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $my_booking_hash);
            if ($my_booking_id_type !== false) {
                $my_booking_id = $my_booking_id_type[0];
                $bktype        = $my_booking_id_type[1];
            }
        }
    }



    if (strpos($dates,' - ')!== FALSE) {
        $dates =explode(' - ', $dates );
        $dates = createDateRangeArray($dates[0],$dates[1]);
    }
//debuge($_POST);
    ///  CAPTCHA CHECKING   //////////////////////////////////////////////////////////////////////////////////////
    $the_answer_from_respondent = $_POST['captcha_user_input'];
    $prefix = $_POST['captcha_chalange'];
    if (! ( ($the_answer_from_respondent == '') && ($prefix == '') )) {
        $captcha_instance = new wpdevReallySimpleCaptcha();
        $correct = $captcha_instance->check($prefix, $the_answer_from_respondent);

        if (! $correct) {
            $word = $captcha_instance->generate_random_word();
            $prefix = mt_rand();
            $captcha_instance->generate_image($prefix, $word);

            $filename = $prefix . '.png';
            $captcha_url = WPDEV_BK_PLUGIN_URL . '/js/captcha/tmp/' .$filename;
            $ref = substr($filename, 0, strrpos($filename, '.'));
            ?> <script type="text/javascript">
                document.getElementById('captcha_input<?php echo $bktype; ?>').value = '';
                // chnage img
                document.getElementById('captcha_img<?php echo $bktype; ?>').src = '<?php echo $captcha_url; ?>';
                document.getElementById('wpdev_captcha_challenge_<?php echo $bktype; ?>').value = '<?php echo $ref; ?>';
                document.getElementById('captcha_msg<?php echo $bktype; ?>').innerHTML =
                    '<span class="alert" style="padding: 5px 5px 4px;vertical-align: middle;text-align:center;margin:5px;"><?php echo __('The code you entered is incorrect', 'wpdev-booking'); ?></span>';
                document.getElementById('submiting<?php echo $bktype; ?>').innerHTML ='';
                jQuery('#captcha_input<?php echo $bktype; ?>')
                .fadeOut( 350 ).fadeIn( 300 )
                .fadeOut( 350 ).fadeIn( 400 )
                .animate( {opacity: 1}, 4000 )
                ;  // mark red border
                jQuery("span.wpdev-help-message span.alert")
                .fadeIn( 1 )
                //.css( {'color' : 'red'} )
                .animate( {opacity: 1}, 10000 )
                .fadeOut( 2000 );   // hide message
                document.getElementById('captcha_input<?php echo $bktype; ?>').focus();    // make focus to elemnt
                jQuery('#booking_form_div<?php echo $bktype; ?> input[type=button]').prop("disabled", false);
            </script> <?php
            die();
        }
    }//////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    $my_modification_date = "'" . date_i18n( 'Y-m-d H:i:s'  ) ."'" ;    // Localize booking modification date
    //$my_modification_date = 'NOW()';                                  // Server value modification date

    if ($my_booking_id>0) {                  // Edit exist booking

        if ( strpos($_SERVER['HTTP_REFERER'],'wp-admin/admin.php?') !==false ) {
            ?> <script type="text/javascript">
                document.getElementById('ajax_working').innerHTML =
                    '<div class="updated ajax_message" id="ajax_message">\n\
                        <div style="float:left;"><?php echo __('Updating...', 'wpdev-booking'); ?></div> \n\
                        <div class="wpbc_spin_loader">\n\
                               <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
                        </div>\n\
                    </div>';
            </script> <?php
        }
        $update_sql = "UPDATE ".$wpdb->prefix ."booking AS bk SET bk.form='$formdata', bk.booking_type=$bktype , bk.modification_date=".$my_modification_date." WHERE bk.booking_id=$my_booking_id;";
        if ( false === $wpdb->query(wpdevbk_db_prepare( $update_sql ) ) ){
            ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating exist booking in BD',__FILE__,__LINE__); ?></div>'; </script> <?php
            die();
        }

        // Check if dates already aproved or no
        $slct_sql = "SELECT approved FROM ".$wpdb->prefix ."bookingdates WHERE booking_id IN ($my_booking_id) LIMIT 0,1";
        $slct_sql_results  = $wpdb->get_results( wpdevbk_db_prepare($slct_sql) );
        if ( count($slct_sql_results) > 0 ) {
            $is_approved_dates = $slct_sql_results[0]->approved;
        }

        $delete_sql = "DELETE FROM ".$wpdb->prefix ."bookingdates WHERE booking_id IN ($my_booking_id)";
        if ( false === $wpdb->query(wpdevbk_db_prepare( $delete_sql ) ) ){
            ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating exist booking for deleting dates in BD' ,__FILE__,__LINE__); ?></div>'; </script> <?php
            die();
        }
        $booking_id = (int) $my_booking_id;       //Get ID  of reservation

    } else {                                // Add new booking

        $sql_insertion = "INSERT INTO ".$wpdb->prefix ."booking (form, booking_type, modification_date) VALUES ('$formdata',  $bktype, ".$my_modification_date." )" ;

        if ( false === $wpdb->query(wpdevbk_db_prepare( $sql_insertion ) ) ){
            ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during inserting into BD',__FILE__,__LINE__); ?></div>'; </script> <?php
            die();
        }
        // Make insertion into BOOKINGDATES
        $booking_id = (int) $wpdb->insert_id;       //Get ID  of reservation



        $is_approved_dates = '0';
        $auto_approve_new_bookings_is_active       =  get_bk_option( 'booking_auto_approve_new_bookings_is_active' );
        if ( trim($auto_approve_new_bookings_is_active) == 'On')
            $is_approved_dates = '1';


    }





    $my_dates = explode(",",$dates);
    $i=0; foreach ($my_dates as $md) {$my_dates[$i] = trim($my_dates[$i]) ; $i++; }

    $start_end_time = get_times_from_bk_form($formdata, $my_dates, $bktype);
    $start_time = $start_end_time[0];
    $end_time = $start_end_time[1];
    $my_dates = $start_end_time[2];

    make_bk_action('wpdev_booking_post_inserted', $booking_id, $bktype, str_replace('|',',',$dates),  array($start_time, $end_time ) );
    $my_cost = apply_bk_filter('get_booking_cost_from_db', '', $booking_id);


    $i=0;
    foreach ($my_dates as $md) { // Set in dates in such format: yyyy.mm.dd
        if ($md != '') {
            $md = explode('.',$md);
            $my_dates[$i] = $md[2] . '.' . ( (intval($md[1])<10) ? ('0'.intval($md[1])) : $md[1] ) . '.' . ( (intval($md[0])<10) ? ('0'.intval($md[0])) : $md[0] ) ;
        } else { unset($my_dates[$i]) ; } // If some dates is empty so remove it   // This situation can be if using several bk calendars and some calendars is not checked
        $i++;

    }
//debuge($my_dates);    
    sort($my_dates); // Sort dates
//debuge($my_dates);

    $my_dates4emeil = '';
    $i=0;
    $insert='';
    $my_date_previos = '';
    foreach ($my_dates as $my_date) {
        $i++;          // Loop through all dates
        if (strpos($my_date,'.')!==false) {

            if ( get_bk_option( 'booking_recurrent_time' ) !== 'On') {
                    $my_date = explode('.',$my_date);
                    if ($i == 1) {
                        if ( (! isset($start_time[0])) || (empty($start_time[0])) ) $start_time[0] = '00';
                        if ( (! isset($start_time[1])) || (empty($start_time[1])) ) $start_time[1] = '00';                        
                        if ( ($start_time[0] == '00') && ($start_time[1] == '00') ) $start_time[2] = '00';

                        $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[0], $my_date[1], $my_date[2], $start_time[0], $start_time[1], $start_time[2] );
                    }elseif ($i == count($my_dates)) {

                        if ( (! isset($end_time[0])) || (empty($end_time[0])) ) $end_time[0] = '00';
                        if ( (! isset($end_time[1])) || (empty($end_time[1])) ) $end_time[1] = '00';                        
                        if ( ($end_time[0] == '00') && ($end_time[1] == '00') ) $end_time[2] = '00';

                        $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[0], $my_date[1], $my_date[2], $end_time[0], $end_time[1], $end_time[2] );
                    }else {
                        $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[0], $my_date[1], $my_date[2], '00', '00', '00' );
                    }
                    $my_dates4emeil .= $date . ',';
                    if ( !empty($insert) ) $insert .= ', ';
                    $insert .= "('$booking_id', '$date', '$is_approved_dates' )";
            } else {
                    if ($my_date_previos  == $my_date) continue; // escape for single day selections.

                    $my_date_previos  = $my_date;
                    $my_date = explode('.',$my_date);
                    $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[0], $my_date[1], $my_date[2], $start_time[0], $start_time[1], $start_time[2] );
                    $my_dates4emeil .= $date . ',';
                    if ( !empty($insert) ) $insert .= ', ';
                    $insert .= "('$booking_id', '$date', '$is_approved_dates' )";

                    $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[0], $my_date[1], $my_date[2], $end_time[0], $end_time[1], $end_time[2] );
                    $my_dates4emeil .= $date . ',';
                    if ( !empty($insert) ) $insert .= ', ';
                    $insert .= "('$booking_id', '$date', '$is_approved_dates' )";

            }

        }
    }
    $my_dates4emeil = substr($my_dates4emeil,0,-1);

    $my_dates4emeil_check_in_out = explode(',',$my_dates4emeil);
    $my_check_in_date  = change_date_format($my_dates4emeil_check_in_out[0] );
    $my_check_out_date = change_date_format($my_dates4emeil_check_in_out[ count($my_dates4emeil_check_in_out)-1 ] );

    // Save the sort date
    $sql_sort_date  = "UPDATE ".$wpdb->prefix ."booking SET sort_date = '".$my_dates4emeil_check_in_out[0]. "' WHERE booking_id  = ". $booking_id . " ";
    $wpdb->query( $sql_sort_date );
//debuge($insert);

    if ( !empty($insert) )
        if ( false === $wpdb->query(wpdevbk_db_prepare("INSERT INTO ".$wpdb->prefix ."bookingdates (booking_id, booking_date, approved) VALUES " . $insert) ) ){
            ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during inserting into BD - Dates',__FILE__,__LINE__); ?></div>'; </script> <?php
            die();
        }

    if ($my_booking_id>0) { // For editing exist booking

        if ($is_send_emeils != 0 )
            sendModificationEmails($booking_id, $bktype, $formdata  );

        if ( strpos($_SERVER['HTTP_REFERER'],'wp-admin/admin.php?') ===false ) {
            do_action('wpdev_new_booking',$booking_id, $bktype, str_replace('|',',',$dates), array($start_time, $end_time ) ,$formdata );
        }

        ?> <script type="text/javascript">
        <?php
        if ( strpos($_SERVER['HTTP_REFERER'],'wp-admin/admin.php?') ===false ) { ?>
                    setReservedSelectedDates('<?php echo $bktype; ?>');
            <?php }  else { ?>
                    document.getElementById('ajax_message').innerHTML = '<?php echo __('Updated successfully', 'wpdev-booking'); ?>';
                    jQuery('#ajax_message').fadeOut(1000);
                    document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php echo __('Updated successfully', 'wpdev-booking'); ?></div>';
                    if ( jQuery('#wpdev_http_referer').length > 0 ) {
                           location.href=jQuery('#wpdev_http_referer').val();
                    } else location.href='admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ;?>wpdev-booking&view_mode=vm_listing&tab=actions&wh_booking_id=<?php echo  $my_booking_id;?>';
            <?php } ?>
            </script> <?php

    } else {

        // For inserting NEW booking
        if ( count($my_dates) > 0 )                    
            if ($is_send_emeils != 0 )
                sendNewBookingEmails($booking_id, $bktype, $formdata) ;

        do_action('wpdev_new_booking',$booking_id, $bktype, str_replace('|',',',$dates), array($start_time, $end_time ) ,$formdata );
        /*
        // Start Mail Chimp Customization
        $booking_form_show = get_form_content ($formdata , $bktype );
        if ( ( isset ($booking_form_show['subscribe_me'] )) && ( $booking_form_show['subscribe_me'] == 'yes') ) {

            if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/MailChimp.class.php')) { // Include MailChimp class (You can download it from  here https://github.com/drewm/mailchimp-api/ )
                require_once(WPDEV_BK_PLUGIN_DIR. '/lib/MailChimp.class.php' ); 

                $MailChimp = new MailChimp('key-my');                          // You are need to specify here YOUR KEY !!!!

                $result = $MailChimp->call('lists/subscribe', array(
                                'id'                => 'id' . $booking_id ,          
                                'email'             => array('email'=>$booking_form_show['email']),
                                'merge_vars'        => array('FNAME'=>$booking_form_show['name'], 'LNAME'=>$booking_form_show['secondname']),
                                'double_optin'      => false,
                                'update_existing'   => true,
                                'replace_interests' => false,
                                'send_welcome'      => false,
                            ));
                // print_r($result);
            }
        } // End Mail Chimp Customization
        /**/

        
        $auto_approve_new_bookings_is_active       =  get_bk_option( 'booking_auto_approve_new_bookings_is_active' );
        if ( trim($auto_approve_new_bookings_is_active) == 'On') {
            sendApproveEmails($booking_id, 1);
        }
        ?> <script type="text/javascript"> setReservedSelectedDates('<?php echo $bktype; ?>'); </script>  <?php
    }

    // ReUpdate booking resource TYPE if its needed here
    if (! empty($dates) ) // check to have dates not empty
        make_bk_action('wpdev_booking_reupdate_bk_type_to_childs', $booking_id, $bktype, str_replace('|',',',$dates),  array($start_time, $end_time ) , $formdata );

}
?>