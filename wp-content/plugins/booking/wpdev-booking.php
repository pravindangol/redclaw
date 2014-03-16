<?php
/*
Plugin Name: Booking Calendar
Plugin URI: http://wpbookingcalendar.com/demo/
Description: Online reservation and availability checking service for your site.
Version: 5.1
Author: wpdevelop
Author URI: http://wpbookingcalendar.com/
Tested WordPress Versions: 3.3 - 3.8.1
*/

/*  Copyright 2009 - 2013  www.wpbookingcalendar.com  (email: info@wpbookingcalendar.com),

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
*/

// <editor-fold defaultstate="collapsed" desc=" T O D O : & Changelog lists ">
/*
-----------------------------------------------
Change Log and Features for Future Releases :
-----------------------------------------------
 * New Files:
 * =====================================
 * lib\
 * ====================================

= 5.1 =
- Features and issue fixings in All versions:
 * **Responsive Booking Admin panel** that looks great on any device.
 * **Styles improvement on Booking Listing page** for best feet to WordPress 3.8 update and better look on mobile devices.
 * **Styles improvement on Calendar Overview** page for best feet to WordPress 3.8 update and better look on mobile devices.
 * **Styles improvement on Settings page** for best feet to WordPress 3.8 update and better look on mobile devices.
 * **Styles improvement on "Add booking" page** for best feet to WordPress 3.8 update and better look on mobile devices.
 * **New design of "Filter Tab" Fields on the Booking Listing page**, which take less space and show filter info inside for better understanding or requests.
 * Possibility to **define the Form Fields Labales** at the Settings Fields page **in several languages**, if the wordpress blog is multilingual. Example: "First Name[lang=de_DE]Vorname[lang=fr_FR]Prénom"
 * New "Mark as Read All" button in the Filters tab on Booking Listing page.
 * Translation to **Norwegian** language by Håvard Hasli
 * Translation to **Brazilian Portuguese** by Roberto Negraes
 * Updated **Danish** translation by Carl Andersen
 * Updated **Dutch** translation by Gert Pepping
 * Repositioning popover and buttons inside of it on the Calendar Overview page for the better looking on mobile devices.
 * Saving into DB "relative" path to the selected Skin, instead of absolute. Its prevent from the security issue on some servers.
 * Show Booking Dashboard Widget only, if the current user have capability to open "Booking Listing" page.
 * Fixed issue of incorrect showing "checkboxes" on admin panel in Safari
 * Fixed issue with href="#" (scrolling to top of page) in some links.
 * Fixed position of the "Approve", "Cancel", "Delete" and "Edit" links in the mouseover popover at Calendar Overview page in the IE10
 * Fix issue of sending some emails with "\n" character instead of new line, if inside of booking form was used the new lines in the text box(es) .
 * Fix declaration  of the wp-content and wp-plugins directories, using standard WP functions for that.
 * Fixed "Notice: Undefined index: booking_type"
 * Removed SSL declaration constant - WP_BK_SSL. Must to autodect this.
 * Wordpress 3.8 support
- Personal / Business Small / Business Medium / Business Large / MultiUser versions features:
 * **Styles improvement on Resources page** for best feet to WordPress 3.8 update and better look on mobile devices. (Personal, Business Small/Medium/Large, MultiUser)
 * **Styles improvement on Settings Fields** page for best feet to WordPress 3.8 update and better look on mobile devices. (Personal, Business Small/Medium/Large, MultiUser)
 * **Styles improvement on Settings Emails** page for best feet to WordPress 3.8 update and better look on mobile devices. (Personal, Business Small/Medium/Large, MultiUser) 
 * **Styles improvement on Settings Payment** page for best feet to WordPress 3.8 update and better look on mobile devices. (Business Small/Medium/Large, MultiUser)
 * **Styles improvement on Cost and Rates page** for best feet to WordPress 3.8 update and better look on mobile devices. (Business Medium/Large, MultiUser)
 * **Styles improvement on Advanced Cost page** for best feet to WordPress 3.8 update and better look on mobile devices. (Business Medium/Large, MultiUser)
 * **Styles improvement on Availability page** for best feet to WordPress 3.8 update and better look on mobile devices. (Business Medium/Large, MultiUser)
 * **Styles improvement on Season Filters page** for best feet to WordPress 3.8 update and better look on mobile devices. (Business Medium/Large, MultiUser)
 * **Styles improvement on Discount Coupons page** for best feet to WordPress 3.8 update and better look on mobile devices. (Business Large, MultiUser)
 * **Styles improvement on Settings Search page** for best feet to WordPress 3.8 update and better look on mobile devices. (Business Large, MultiUser)
 * **Styles improvement on Settings Users page** for best feet to WordPress 3.8 update and better look on mobile devices. (MultiUser)
 * New. **Show deposit payment**, only if **difference between "Today" and "Check In"** dates higher then specific number of days. (Business Medium/Large, MultiUser)
 * Ability to set **"Valuation days"** cost for **"cost per night"** setting. (Business Medium/Large, MultiUser)
 * Calculation **"Valuation days" cost setting depend from Season Filter of "Check In" date**. Previous "Seson filter for all days", which was worked in limited situations removed. (Business Medium/Large, MultiUser)
 * **Auto select Default Custom Form** for the specific resource, if use shortcode of "booking resource selection" and each booking resource have the default custom form. (Note, you must have no parameter "form_type" in the "bookingselect" shortcode). (Business Medium/Large, MultiUser)
 * Ability to **use "option" parameter in the [bookingselect ...] shortcode** in the same way, as it possible to use for [booking ...] shortcode (Personal, Business Small/Medium/Large, MultiUser)
 * Possibility to select default custom booking form during creation of the Booking Resources  (Business Large, MultiUser)
 * **Pagination of users** table at the Settings Users page (MultiUser)
 * Load the PayPal payment page depend from the locale of website  (Business Small/Medium/Large, MultiUser)
 * Show confirmation dialog for deletion of discount coupons. (Business Large, MultiUser)
 * Trick. Possibility to set TRUE of the constant WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS in wpdev-booking.php file for activation additonal "custom forms" functionality for "regular users" (MultiUser)
 * Trick. Possibility to set TRUE of the constant WP_BK_SHOW_BOOKING_NOTES in wpdev-booking.php file for showing by default all cooments for the specific bookings in booking listing page (Personal, Business Small/Medium/Large, MultiUser)
 * Sanitize title of new Custom forms. Sometimes using not standard symbols, can generate issues in loading such  form  (Business Medium/Large, MultiUser)
 * Fix. Possibility to use the same booking shortcode parameters (like several months setting or options parameter) in the other defined calendars in a booking form. If was used the several  calendars and one booking form, relative to  this instruction: http://wpbookingcalendar.com/faq/booking-many-different-items-via-one-booking-form/  (Business Medium/Large, MultiUser)
 * Fix. Changed the name of submit button at the Settings Payment page, which can  generate issue of saving on some servers (Business Small/Medium/Large, MultiUser)
 * Fix "403 Error" during saving Settings page on some servers. Saving only relative URLs of the "successfuly paid" and "failed" URLs, which are saving in the Settings Payment page. Previously, absolute URLs saving can generate the "403 Error" during saving page on some servers. (Business Small/Medium/Large, MultiUser)
 * Fix issue of showing the HINTs (for exmaple the [check_in_date_hint] and other...) in the booking form, only in default language, even  if the system  was turned to other language (Business Medium/Large, MultiUser)
 * Fix of auto showing HINTs for the selcted dates and cost for the editing booking in admin panel  (Business Medium/Large, MultiUser)
 * Fix issue of not showing the Payment form, but instead of that redirection to the "Thank you" page for the booking form (without calendar) (Business Large, MultiUser)
 * Fix issue in MultiUser version of not correctly showing booking resources (hierarhy) in the Calendar Overview mode, while logged in as not super booking admin user. (MultiUser)
 * Fix issue of not possibility to scroll the monthes in Calendar Overview page,  if was saved the "Filter tab" set as default template. (Personal, Business Small/Medium/Large, MultiUser)
 * Fix issue with saving and configuring additional cost settings for the options, which  have the different titles and values (using @@ symbols in the options). (Business Medium/Large, MultiUser)
 * Fix issue of incorrect highlihiting check in/out dates in the calendar, when visitor select "check in" date in calendar. Its only in case if the "range days selection using 2 mouse clicks" is activated. (Business Medium/Large, MultiUser)
 * Fix issue of Sage Pay integrations. Add the "Sate" billing form integration into the billing form. (Business Small/Medium/Large, MultiUser)
*/
// </editor-fold>


    // Die if direct access to file
    if (   (! isset( $_GET['wpdev_bkpaypal_ipn'] ) ) && 
           (! isset( $_GET['merchant_return_link'] ) ) && 
           (! isset( $_GET['payed_booking'] ) ) && 
           ( (! isset($_GET['pay_sys']) ) || ($_GET['pay_sys'] != 'authorizenet') ) &&
           (! function_exists ('get_option') )  && 
           (! isset( $_POST['ajax_action'] ) ) 
       ) { die('You do not have permission to direct access to this file !!!'); }

    // A J A X /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function wpbc_find_wp_base_path() {
        $dir = dirname(__FILE__);
        do {
            //it is possible to check for other files here
            if( file_exists($dir."/wp-config.php") ) {
                return $dir;
            }
        } while( $dir = realpath("$dir/..") );
        return null;
    }        
    if ( 
         ( isset( $_GET['wpdev_bkpaypal_ipn'] ) )  || 
         ( isset( $_GET['payed_booking'] ) )  || 
         (  isset( $_GET['merchant_return_link']))  || 
         ( (isset($_GET['pay_sys']) ) && ($_GET['pay_sys'] == 'authorizenet') ) ||    
         ( isset( $_POST['ajax_action'] ) ) ) {
            
        if ( file_exists( dirname(__FILE__) . '/../../../wp-load.php' ) ) {
            require_once( dirname(__FILE__) . '/../../../wp-load.php' );
        } else if (file_exists( wpbc_find_wp_base_path() . '/wp-load.php' )) {
            require_once( wpbc_find_wp_base_path() . '/wp-load.php' );
        } else {
            die('Booking Calendar Error! Can not load WP functions!');
        }        
        @header('Content-Type: text/html; charset=' . get_option('blog_charset'));
    } ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // CONFIGURABLE CONSTANTS ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  if (!defined('WP_BK_SSL'))                                  define('WP_BK_SSL',  false );    
    if (!defined('WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS'))       define('WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS',   false );
    if (!defined('WP_BK_SHOW_INFO_IN_FORM'))                    define('WP_BK_SHOW_INFO_IN_FORM',   false );      // This feature can impact to the performace
    if (!defined('WP_BK_SHOW_BOOKING_NOTES'))                   define('WP_BK_SHOW_BOOKING_NOTES',  false );      // Set notes of the specific booking visible by default.
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   D e f i n e     S T A T I C              //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if (!defined('WP_BK_VERSION_NUM'))      define('WP_BK_VERSION_NUM',  '5.1' );
    if (!defined('WP_BK_MINOR_UPDATE'))     define('WP_BK_MINOR_UPDATE',  false );    
    if (!defined('IS_USE_WPDEV_BK_CACHE'))  define('IS_USE_WPDEV_BK_CACHE',  true );    
    if (!defined('WP_BK_DEBUG_MODE'))       define('WP_BK_DEBUG_MODE',  false );

    if (!defined('WPDEV_BK_FILE'))       define('WPDEV_BK_FILE',  __FILE__ );
    
//    if (WP_BK_SSL)   $wpdev_bk_my_site_url = site_url('','https');
//    else             $wpdev_bk_my_site_url = site_url();
//    if (!defined('WP_CONTENT_DIR'))      define('WP_CONTENT_DIR', ABSPATH . 'wp-content');                   // Z:\home\test.wpdevelop.com\www/wp-content
//    if (!defined('WP_CONTENT_URL'))      define('WP_CONTENT_URL', $wpdev_bk_my_site_url . '/wp-content');    // http://test.wpdevelop.com/wp-content
//    if (!defined('WP_PLUGIN_DIR'))       define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');               // Z:\home\test.wpdevelop.com\www/wp-content/plugins
//    if (!defined('WP_PLUGIN_URL'))       define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');               // http://test.wpdevelop.com/wp-content/plugins
//    if (!defined('WPDEV_BK_PLUGIN_FILENAME'))  define('WPDEV_BK_PLUGIN_FILENAME',  basename( __FILE__ ) );              // menu-compouser.php
//    if (!defined('WPDEV_BK_PLUGIN_DIRNAME'))   define('WPDEV_BK_PLUGIN_DIRNAME',  plugin_basename(dirname(__FILE__)) ); // menu-compouser
//    if (!defined('WPDEV_BK_PLUGIN_DIR')) define('WPDEV_BK_PLUGIN_DIR', WP_PLUGIN_DIR.'/'.WPDEV_BK_PLUGIN_DIRNAME ); // Z:\home\test.wpdevelop.com\www/wp-content/plugins/menu-compouser
//    if (!defined('WPDEV_BK_PLUGIN_URL')) define('WPDEV_BK_PLUGIN_URL', $wpdev_bk_my_site_url.'/wp-content/plugins/'.WPDEV_BK_PLUGIN_DIRNAME ); // http://test.wpdevelop.com/wp-content/plugins/menu-compouser
    
    if (!defined('WP_CONTENT_DIR'))            define('WP_CONTENT_DIR', ABSPATH . 'wp-content');                        // Z:\home\server.com\www/wp-content
    if (!defined('WP_PLUGIN_DIR'))             define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');                    // Z:\home\server.com\www/wp-content/plugins
    if (!defined('WPDEV_BK_PLUGIN_FILENAME'))  define('WPDEV_BK_PLUGIN_FILENAME',  basename( __FILE__ ) );              // menu-compouser.php
    if (!defined('WPDEV_BK_PLUGIN_DIRNAME'))   define('WPDEV_BK_PLUGIN_DIRNAME',  plugin_basename(dirname(__FILE__)) ); // menu-compouser
    if (!defined('WPDEV_BK_PLUGIN_DIR'))       define('WPDEV_BK_PLUGIN_DIR', WP_PLUGIN_DIR.'/'.WPDEV_BK_PLUGIN_DIRNAME ); // Z:\home\server.com\www/wp-content/plugins/menu-compouser
    //if (!defined('WP_CONTENT_URL'))      define('WP_CONTENT_URL', content_url() );                          // http://server.com/wp-content
    //if (!defined('WP_PLUGIN_URL'))       define('WP_PLUGIN_URL',  plugins_url() );                          // http://server.com/wp-content/plugins
    if (!defined('WPDEV_BK_PLUGIN_URL')) define('WPDEV_BK_PLUGIN_URL', plugins_url( '', WPDEV_BK_FILE ) );  // http://server.com/wp-content/plugins/menu-compouser
        
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   L O A D   F I L E S                      //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-booking-functions.php')) {     // S u p p o r t    f u n c t i o n s
        require_once(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-booking-functions.php' ); }
        
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/wpbc-class-dismiss.php')) {          // C L A S S  -  Dismiss
        require_once(WPDEV_BK_PLUGIN_DIR. '/lib/wpbc-class-dismiss.php' ); 
        global $wpbc_Dismiss;
        $wpbc_Dismiss = new WPBC_Dismiss();
    }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-settings-general.php')) {     // S e t t i n g s
        require_once(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-settings-general.php' ); }
        
        
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-booking-widget.php')) {        // W i d g e t s
        require_once(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-booking-widget.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/js/captcha/captcha.php'))  {             // C A P T C H A
        require_once(WPDEV_BK_PLUGIN_DIR. '/js/captcha/captcha.php' );}

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/personal.php'))   {                  // O t h e r
        require_once(WPDEV_BK_PLUGIN_DIR. '/inc/personal.php' ); }
        
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-bk-lib.php')) {                // S u p p o r t    l i b
        require_once(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-bk-lib.php' ); }
                
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-bk-timeline.php')) {           // T i m e l i n e    l i b
        require_once(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-bk-timeline.php' ); }
        
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-bk-edit-toolbar-buttons.php')) { // B o o k i n g    B u t t o n s   in   E d i t   t o o l b a r
        require_once(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-bk-edit-toolbar-buttons.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-booking-class.php'))           // C L A S S    B o o k i n g
        { require_once(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-booking-class.php' ); }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // GET VERSION NUMBER
    $plugin_data = get_file_data_wpdev(  __FILE__ , array( 'Name' => 'Plugin Name', 'PluginURI' => 'Plugin URI', 'Version' => 'Version', 'Description' => 'Description', 'Author' => 'Author', 'AuthorURI' => 'Author URI', 'TextDomain' => 'Text Domain', 'DomainPath' => 'Domain Path' ) , 'plugin' );
    if (!defined('WPDEV_BK_VERSION'))    define('WPDEV_BK_VERSION',   $plugin_data['Version'] );                             // 0.1
            

    //    A J A X     R e s p o n d e r     // RUN if Ajax //
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-booking-ajax.php'))  { require_once(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-booking-ajax.php' ); }

    // Wellcome page
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/wpbc-welcome.php'))                 // W E L C O M E
        { require_once(WPDEV_BK_PLUGIN_DIR. '/lib/wpbc-welcome.php' ); }
    
    // RUN //
    $wpdev_bk = new wpdev_booking();
?>