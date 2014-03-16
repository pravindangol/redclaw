<?php
/**
 * Welcome Page Class
 *
 * Shows a feature overview for the new version (major).
 *
 * Adapted from code in EDD (Copyright (c) 2012, Pippin Williamson) and WP.
 * 
 * @version     2.0.0
*/


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPBC_Welcome Class
 */
class WPBC_Welcome {

	/**
	 * @var string The capability users should have to view the page
	 */
	public $minimum_capability = 'read';    //'manage_options';
        private $asset_path = 'http://wpbookingcalendar.com/assets/';        
        /**
	 * Get things started
	 *
	 * @access  public
	 * @since 1.4
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
	}

	/**
	 * Register the Dashboard Pages which are later hidden but these pages
	 * are used to render the Welcome and Credits pages.
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function admin_menus() {
		// About Page
		add_dashboard_page(
			__( 'Welcome to WP Booking Calendar', 'wpdev-booking-overview' ),
			__( 'Welcome to WP Booking Calendar', 'wpdev-booking-overview' ),
			$this->minimum_capability,
			'wpbc-about',
			array( $this, 'about_screen' )
		);

		// Credits Page
		add_dashboard_page(
			__( 'Welcome to WP Booking Calendar', 'wpdev-booking-overview' ),
			__( 'Welcome to WP Booking Calendar', 'wpdev-booking-overview' ),
			$this->minimum_capability,
			'wpbc-about-premium',
			array( $this, 'about_premium_screen' )
		);
	}

	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function admin_head() {
		remove_submenu_page( 'index.php', 'wpbc-about' );
		remove_submenu_page( 'index.php', 'wpbc-about-premium' );
		?>
		<style type="text/css" media="screen">
		/*<![CDATA[*/
		.wpbc-badge {
                    display:none;
/*                      padding-top: 150px;
                        height: 52px;
                        width: 185px;
                        color: #666;
                        font-weight: bold;
                        font-size: 14px;
                        text-align: center;
                        text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8);
                        margin: 0 -5px;
                        background: url('<?php $badge_url = WPDEV_BK_PLUGIN_URL . '/assets/images/wpbc-badge.png'; echo $badge_url; ?>') no-repeat;*/
		}

/*		.wpbc-welcome-page .about-wrap .wpbc-badge {
                    position: absolute;
                    top: 0;
                    right: 0;
		}*/

                .wpbc-welcome-page .about-text {
                    margin-right:0px;
                }
                .wpbc-welcome-page .versions {
                    color: #999999;
                    font-size: 12px;
                    font-style: normal;
                    margin: 0;
                    text-align: right;
                    text-shadow: 0 -1px 0 #EEEEEE;
                }
                .wpbc-welcome-page .versions a,
                .wpbc-welcome-page .versions a:hover{
                   color: #999;
                   text-decoration:none;
                }
                .wpbc-welcome-page .update-nag {
                    border-color: #E3C58E;
                    border-radius: 5px;
                    -moz-border-radius: 5px;
                    -webkit-border-radius: 5px;
                    box-shadow: 0 1px 3px #EEEEEE;
                    color: #998877;
                    font-size: 12px;
                    font-weight: bold;
                    margin: 15px 0 0;   
                    width:90%;
                }

		/*]]>*/
		</style>
		<?php
	}
        
        /** Show Title Section and Menu of Welcome page 
         * 
         */
        public function title_section() {
                list( $display_version ) = explode( '-', WPDEV_BK_VERSION );
                ?>
                <h1><?php printf( __( 'Welcome to WP Booking Calendar %s', 'wpdev-booking-overview' ), $display_version ); ?></h1>
                <div class="about-text"><?php 
                    _e('Thank you for updating to the latest version!', 'wpdev-booking-overview'); 
                    printf( __( '%s is more polished, powerful and easy to use than ever before.', 'wpdev-booking-overview' ), ' WP Booking Calendar ' . $display_version ); ?></div>
                <!--div class="wpbc-badge"><?php printf( __( 'Version %s', 'wpdev-booking-overview' ), $display_version ); ?></div-->

                <h2 class="nav-tab-wrapper">
                    <?php 
                        $is_about_tab_active = $is_about_premium_tab_active = '';
                        if ( ( isset($_GET['page']) ) && ( $_GET['page'] == 'wpbc-about' ) )            $is_about_tab_active = ' nav-tab-active ';
                        if ( ( isset($_GET['page']) ) && ( $_GET['page'] == 'wpbc-about-premium' ) )    $is_about_premium_tab_active = ' nav-tab-active ';
                    ?>
                        <a class="nav-tab<?php echo $is_about_tab_active; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wpbc-about' ), 'index.php' ) ) ); ?>">
                                <?php _e( "What's New", 'wpdev-booking-overview' ); ?>
                        </a><a class="nav-tab<?php echo $is_about_premium_tab_active; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wpbc-about-premium' ), 'index.php' ) ) ); ?>">
                                <?php _e( "Even more Premium Features", 'wpdev-booking-overview' ); ?>
                        </a>
                </h2>                
                <?php
        }

        /** Show Maintence section  for the MINOR Updates
         * 
         */
        public function maintence_section(){
                
                if ( ! ( ( defined('WP_BK_MINOR_UPDATE')) && (WP_BK_MINOR_UPDATE) ) )
                    return;
                
                list( $display_version ) = explode( '-', WPDEV_BK_VERSION );    
                ?>
                <div class="changelog point-releases">
                        <h3><?php _e( "Maintenance and Security Release", 'wpdev-booking-overview' ); ?></h3>
                        <p><strong><?php printf( __( 'Version %s', 'wpdev-booking-overview' ), $display_version ); ?></strong> <?php printf( __( 'addressed some security issues and fixed %s bugs', 'wpdev-booking-overview' ), '' ); ?>. 
                            <?php printf( __( 'For more information, see %sthe release notes%s', 'wpdev-booking-overview' ),'<a href="http://wpbookingcalendar.com/changelog/" target="_blank">','</a>') ?>.
                        </p>
                </div>                        
                <?php              
        }
        
        
	/**
	 * Render About Screen
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
        public function about_screen() {
          ?><div class="wrap about-wrap wpbc-welcome-page">
              
                <?php $this->title_section(); ?>

                <?php $this->maintence_section(); ?>
              
                <div class="changelog">  
                    <h2 class="about-headline-callout"><?php _e( "Responsive, modern and easy to use Booking Admin Panel", 'wpdev-booking-overview' );?></h2>
                    <div style="margin:0 1% auto;width:100%">
                        <img src="<?php echo $this->asset_path; ?>5.1/booking-listing.png" style="width: 70%;float:left;border:none;box-shadow: 0 1px 3px #777777;">
                        <img src="<?php echo $this->asset_path; ?>5.1/booking-listing-m.png" style="width: 20.85%;float:left;margin-left: 3%;border:none;box-shadow: 0 1px 3px #777777;">
                    </div>
<!--                    <div style="margin:0 3% auto;width:100%">    
                        <img src="<?php echo $this->asset_path; ?>5.1/booking-overview.png" style="width: 64.3%;float:left;border:none;box-shadow: 0 1px 3px #777777;">
                        <img src="<?php echo $this->asset_path; ?>5.1/booking-overview-m.png" style="width: 25%;float:left;margin-left: 3%;border:none;box-shadow: 0 1px 3px #777777;">
                        
                    </div>-->
                </div>
              
                <div class="clear" style="height:30px;border-bottom:1px solid #DFDFDF;"></div>
              
                <div class="changelog">  
                    <div class="feature-section col two-col">
                        <div class="col-1">
                            <img src="<?php echo $this->asset_path; ?>5.1/form-fields.png" style="width: 75%;margin:10px 12%;float:right;border:none;box-shadow: 0 1px 3px #777777;">
                        </div>
                        <div class="col-2 last-feature">                            
                            <h3><?php _e( 'Multilingual Form Fields', 'wpdev-booking-overview' ); ?></h3>
                            <p><?php _e( 'Ability to define booking form fields in several languages, while using multilingual plugins like WPML or qTranslate.', 'wpdev-booking-overview' ); ?></p>
                        </div>
                    </div>
                </div>
              
                <div class="clear" style="height:1px;border-bottom:1px solid #DFDFDF;"></div>
                
                <div class="changelog"> 

                    <h3><?php _e('Under the Hood','wpdev-booking-overview');?></h3>
                    <div class="feature-section col three-col">

                        <div class="col-1">
                            <h4><?php _e( "New translations", 'wpdev-booking-overview' ); ?></h4>
                            <p><?php printf(__( "Support translations of several new languages (%sNorwegian%s, %sBrazilian Portuguese%s) and update of some exist lanagues (%sDanish%s, %sDutch%s).", 'wpdev-booking-overview' )
                                    ,'<strong>','</strong>'
                                    ,'<strong>','</strong>'
                                    ,'<strong>','</strong>'
                                    ,'<strong>','</strong>'
                                    ); ?></p>
                        </div>
                        
                        <div class="col-2">
                            <h4><?php _e( "New 'Set Read All' button ", 'wpdev-booking-overview' ); ?></h4>
                            <p><?php  _e( "New button to set all bookings as 'read' in the Filters tab on Booking Listing page", 'wpdev-booking-overview' ); ?></p>
                        </div>     

                        <div class="col-3 last-feature">
                            <h4><?php _e( "Many other improvements and fixes", 'wpdev-booking-overview' ); ?></h4>
                            <p><?php  _e( "This update contain many other small fixes, code refactoring and improvements.", 'wpdev-booking-overview' ); ?></p>
                        </div>

                    </div>
                    <div class="clear" style="height:30px;border-top:1px solid #DFDFDF;"></div>
                    <div>
                        <p class='description'>                        
                        <?php printf( __( 'For more information about current update, see %srelease notes%s or check', 'wpdev-booking-overview' )
                                ,'<a class="button button-secondary" href="http://wpbookingcalendar.com/changelog/" target="_blank">','</a>'); 
                        ?> <a class='button button-primary'  href="http://wpbookingcalendar.com/updates/whats-new-5-0/" target="_blank"><?php _e( "What's New in previous Update", 'wpdev-booking-overview' );?> 5.0 ...</a>                    
                        </p>
                    </div>
                </div>
              
                <?php //$this->about_screen_5_0(); ?>    
              
                <?php wpbc_welcome_panel(); ?>    
              
            </div><?php
        }
        
	public function about_screen_5_0() {		
		?>
		<div class="wrap about-wrap wpbc-welcome-page">
			
                    <?php 
                    //$this->title_section();                                     // Show Title of Welcome page
                    ?>
                        
                    <?php 
                    //$this->maintence_section();                                 // Template for the Minor updates
                    ?>
                        <div class="changelog">                            
                            <h3><?php _e('Responsive, stylish and easy to customize design','wpdev-booking-overview');?></h3>
                            <div class="feature-section col three-col">
                                <div>                                    
                                    <img src="<?php echo $this->asset_path; ?>5.0/responsive-design.png" style="border: medium none;box-shadow: 0 0 0;margin: 0 0 0.5em 20px;width: 80%;" >
                                    <h4><?php _e( "Responsive front end design", 'wpdev-booking-overview' ); ?></h4>
                                    <p><?php  _e( "We have completely rewritten the CSS of calendar skins and booking form. Booking form and calendar support fully responsive design that looks great on any device.", 'wpdev-booking-overview' ); ?>
                                    </p>
                                </div>

                                <div>
                                    <img src="<?php echo $this->asset_path; ?>5.0/insert.png" style="border:none;box-shadow: 0 1px 3px #777777;margin: 3px 0 1em 3px;width: 99%;" >
                                    <h4><?php _e( "Easy to customize", 'wpdev-booking-overview' ); ?></h4>
                                    <p><?php printf(__( "Much  more easy to configure for fitting to your site design. Configure calendar width, height or even structure %s(number of months in a row)%s from settings in a minute.", 'wpdev-booking-overview' ),'<em>','</em>'); ?></p>                                        
                                </div>
                                
                                <div class="last-feature">
                                    <img src="<?php echo $this->asset_path; ?>5.0/sleek-design.png" style="border:none;box-shadow: 0 1px 3px #777777;margin: 3px 3px 1em 0px;width: 95%;" >
                                    <h4><?php _e( "Smoother Booking Experience", 'wpdev-booking-overview' ); ?></h4>
                                    <p><?php  _e( "Booking form has new attractive calendar skins and sleek form fields with nice warning messages.", 'wpdev-booking-overview' ); ?></p>
                                </div>

                            </div>
                        </div>

                        <?php if ( (  $_SERVER['HTTP_HOST'] === 'dev'  ) || ! class_exists('wpdev_bk_personal')) { ?>                    
                        <div class="changelog"> 
                            <h3><?php _e('Configure your predefined set of form fields','wpdev-booking-overview');?></h3>
                            <div class="feature-section images-stagger-right">
                                
                               <img src="<?php echo $this->asset_path; ?>5.0/settings-fields-free.png" style="width:40%; border:none;box-shadow: 0 1px 3px #777777;margin-top: 1px;">
                               <h4><?php _e( "Write Labels for your fields", 'wpdev-booking-overview' ); ?></h4>
                               <p><?php  printf(__( "Now %s(at Booking %s Settings %s Fields page)%s is possible to change the form fields labels.", 'wpdev-booking-overview' ),'<em>','&gt;','&gt;','</em>'); ?></p> 
                               
                               <h4><?php _e( "Activate or Deactivate fields", 'wpdev-booking-overview' ); ?></h4>
                               <p><?php  _e( "You can activate or deactivate the fields from the predefined fields set.", 'wpdev-booking-overview' ); ?></p>
                               
                               <h4><?php _e( "Set as required specific fields", 'wpdev-booking-overview' ); ?></h4>
                               <p><?php  _e( "You can set as required specific fields in your booking form from the predefined fields set.", 'wpdev-booking-overview' ); ?></p>
                               
                            </div>
                        </div>
                        <?php } ?>
                    
                    
                        <div class="changelog">
                            <div class="feature-section">  
                                <h3><?php _e( "Nice and Easy to Understand Interface with Buttons for Fast Actions", 'wpdev-booking-overview' ); ?></h3> 
                                <img src="<?php echo $this->asset_path; ?>5.0/interface.png" style="border: medium none;box-shadow: 0 1px 3px #777777;width: 98%;margin:0px 3px;">
                                <div class="last-feature">           
                                    <h4><?php _e( "Approve or Reject Booking in 1 Click on Calendar Overview page", 'wpdev-booking-overview' ); ?></h4> 
                                    <p><?php _e( "Make common actions, like Approve, Decline or Delete of the booking in One Click on the Calendar Overview panel", 'wpdev-booking-overview' ); ?>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    
                    
                        <div class="changelog"> 
                            
                            <h3><?php _e('Under the Hood','wpdev-booking-overview');?></h3>
                            <div class="feature-section col two-col">
                                
                                <div>
                                    <!--img src="<?php echo $this->asset_path; ?>5.0/code-css-black10.png" style="border:none;box-shadow: 0 1px 3px #777777;margin: 3px 0 1em 3px;width: 99%;" -->
                                    <h4><?php _e( "Customize the Calendar Skins", 'wpdev-booking-overview' ); ?></h4>                            
                                    <p><?php  printf(__( "The calendar skins %s are located separately from the calendar structure file %s and very well commented. So you do not need to worry about the structure, sizes or responsive design of the calendar and concentrate only on design.", 'wpdev-booking-overview' ),'<code>../css/skins/</code>','<code>../css/calendar.css</code>'); ?></p> 
                                    </p>
                                </div>     

                                <div class="last-feature">
                                    <h4><?php _e( "Improved Performance", 'wpdev-booking-overview' ); ?></h4>
                                    <p><?php  echo "WP Booking Calendar"; _e( "%s has been dramatically improved in terms of performance to make your site run better and faster.", 'wpdev-booking-overview' ); ?></p>
                                </div>
                                
                            </div>
                            
                            <p><?php _e( "Many other improvements and small issues fixing.", 'wpdev-booking-overview' ); ?> <?php 
                               printf( __( 'For more information, see %sthe release notes%s', 'wpdev-booking-overview' ),'<a href="http://wpbookingcalendar.com/changelog/" target="_blank">','</a>') ?>.</p>                            
                    
                        </div>
                        
			<!--div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' =>  WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking-option' ), 'admin.php' ) ) ); ?>"><?php _e( 'Go to WP Booking Calendar Settings', 'wpdev-booking-overview' ); ?></a>
			</div-->
                        <?php wpbc_welcome_panel(); ?>
                        <?php if (false) {                                      // Get Remote News or some other info

                            $response = wp_remote_post( OBC_CHECK_URL . 'info/', array() );

                            if (! ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) ) {

                                $body_to_show = json_decode( wp_remote_retrieve_body( $response ) );

                                ?><style type="text/css" media="screen">#bk_news_loaded{display:block !important;}</style><?php

                                echo $body_to_show ;
                            }
                        } ?>                                              
		</div>
		<?php
	}

        
	/**
	 * Render Credits Screen
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
        public function about_premium_screen() {
            list( $display_version ) = explode( '-', WPDEV_BK_VERSION );
            ?>
            <div class="wrap about-wrap wpbc-welcome-page">

                <?php 
                $this->title_section();                                     // Show Title of Welcome page
                ?>
                <p class="update-nag"><?php _e( "New Features in the Paid versions of", 'wpdev-booking-overview' ); ?> <?php echo ' WP Booking Calendar '.$display_version ; ?></p>

                <div class="changelog">
                    <h2 style="margin:0.2em 0;" class="about-headline-callout"><?php _e( "Responsive and easy to use Booking Admin Panel", 'wpdev-booking-overview' ); ?></h2>
                    <div style="margin:0 1% auto;width:100%">    
                        <img src="<?php echo $this->asset_path; ?>5.1/booking-overview-p.png" style="width: 71%;float:left;border:none;box-shadow: 0 1px 3px #777777;">
                        <img src="<?php echo $this->asset_path; ?>5.1/booking-overview-p-m.png" style="width: 21.9%;margin-left: 3%;float:left;border:none;box-shadow: 0 1px 3px #777777;">                        
                        
                    </div>
                    <div class="clear"></div>

                    <p><?php _e( "Booking Admin Panel support fully responsive design that looks great on any device", 'wpdev-booking-overview' ); ?>. <br/>
                       <?php _e( "Industry standard Interface - resources at the first column and booking dates in right rows", 'wpdev-booking-overview' ); ?>.
                    </p>
                    <span class="versions">{ <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">Personal, Business Small / Medium / Large, MultiUser</a> <?php _e( "versions", 'wpdev-booking-overview' ); ?> }</span>
                </div>
                
                <div class="clear" style="height:20px;border-bottom:1px solid #DFDFDF;"></div>
                <div class="clear" style="height:20px;"></div>
                
                <div class="feature-section col two-col">
                    
                    <div >
                        <img src="<?php echo $this->asset_path; ?>5.1/deposit.png" style="width: 60%;border:none;box-shadow: 0 1px 3px #777777;">
                        <h4><?php _e( "Conditions for Deposit payment", 'wpdev-booking-overview' ); ?></h4>
                        <p><?php  
                            printf(__( "Show deposit payment, only if difference between %sToday%s and %sCheck In%s days higher then specific number of days.", 'wpdev-booking-overview' )
                                    ,'<strong>"','"</strong>' 
                                    ,'<strong>"','"</strong>' 
                                  ); 
                            ?>
                        </p>
                    </div>
                    <div class="last-feature">
                        <img src="<?php echo $this->asset_path; ?>5.1/valuation.png" style="width: 100%;border:none;box-shadow: 0 1px 3px #777777;">
                        <div >

                            <h4><?php _e( "Valuation days for cost per night", 'wpdev-booking-overview' ); ?></h4>
                            <p><?php  
                                printf(__( "Ability to set %sValuation days%s cost settings if was setting %scost per night%s option. ", 'wpdev-booking-overview' )
                                        ,'<strong>"','"</strong>' 
                                        ,'<strong>"','"</strong>' 
                                      ); 
                                ?>
                        </div>

                        <div>
                            <h4><?php _e( "Valuation days depend from Check In", 'wpdev-booking-overview' ); ?></h4>
                            <p><?php  
                                printf(__( "Calculation %sValuation days%s cost setting that depend from Season Filter of %sCheck In%s date.", 'wpdev-booking-overview' )
                                        ,'<strong>"','"</strong>' 
                                        ,'<strong>"','"</strong>' 
                                      ); 
                                ?>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <span class="versions">{ <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">Business Medium / Large, MultiUser</a> <?php _e( "versions", 'wpdev-booking-overview' ); ?> }</span>
                </div>
                
                <div class="clear" style="height:10px;border-bottom:1px solid #DFDFDF;"></div>
                
                <div class="changelog"> 
                    <h3><?php _e('Under the Hood','wpdev-booking-overview');?></h3>
                    <div class="feature-section col three-col">
                        <div class="col-1">
                            <h4><?php _e( "Users Pagination", 'wpdev-booking-overview' ); ?></h4>
                            <p><?php  _e( "Pagination of users listing on the Settings Users page", 'wpdev-booking-overview' ); ?></p>
                            <span class="versions">{ <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">MultiUser</a> <?php _e( "version", 'wpdev-booking-overview' ); ?> }</span>
                        </div>                        
                        <div class="col-2">
                            <h4><?php _e( "Custom form selection", 'wpdev-booking-overview' ); ?></h4>
                            <p><?php  _e( "Ability to select default custom booking form during creation of booking resources", 'wpdev-booking-overview' ); ?></p>
                            <span class="versions">{ <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">Business Large, MultiUser</a> <?php _e( "versions", 'wpdev-booking-overview' ); ?> }</span>
                        </div>     
                        <?php /*
                        <div class="col-2">
                            <h4><?php _e( "Payment page depend from locale", 'wpdev-booking-overview' ); ?></h4>
                            <p><?php  _e( "Load the PayPal payment page that depend from the locale of original website", 'wpdev-booking-overview' ); ?></p>
                            <span class="versions">{ <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">Business Small / Medium / Large, MultiUser</a> <?php _e( "versions", 'wpdev-booking-overview' ); ?> }</span>
                        </div> */ ?>
                        <div class="col-3 last-feature">
                            <h4><?php _e( "Custom forms for regular users", 'wpdev-booking-overview' ); ?></h4>
                            <p><?php echo '<strong>'; _e('Trick'); echo '.</strong> '; printf(__( "Ability to set %s of the constant %s in %s file for activation additional %scustom forms%s functionality for %sregular users%s", 'wpdev-booking-overview' )
                                    ,'<code>TRUE</code>'
                                    ,'<code>WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS</code>'
                                    ,' <em>wpdev-booking.php</em> '
                                    ,'<em>"','"</em>'
                                    ,'<em>"','"</em>'                                    
                                    ); ?></p>
                            <span class="versions">{ <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">MultiUser</a> <?php _e( "version", 'wpdev-booking-overview' ); ?> }</span>
                        </div>                        

                        <div class="clear" style="width:100%;"></div>
                        
                        <div class="col-1">
                            <h4><?php _e( "Auto select Default Custom Form", 'wpdev-booking-overview' ); ?></h4>
                            <p><?php printf(__( "Auto select %sDefault Custom Form%s of specific resource, for %sbooking resource selection%s shortcode - %s. %sNote, parameter %sform_type%s in this shortcode must be skipped%s.", 'wpdev-booking-overview' )
                                    ,'<strong>','</strong>'
                                    ,'<strong>','</strong>'
                                    ,'<code>[bookingselect]</code>'
                                    ,'<em>'
                                    ,'<code>','</code>'
                                    ,'</em>'
                                    ); 
                            ?></p>
                            <span class="versions">{ <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">Business Medium / Large, MultiUser</a> <?php _e( "versions", 'wpdev-booking-overview' ); ?> }</span>
                        </div>                                                
                        <div class="col-2">
                            <h4><?php _e( "Using 'options' for booking resources selection", 'wpdev-booking-overview' ); ?></h4>
                            <p><?php
                            printf(__( "Ability to use %s parameter in the %s shortcode in the same way, as it possible to use for %s shortcode.", 'wpdev-booking-overview' )
                                    ,'<code>option</code>'
                                    ,'<code>[bookingselect ...]</code>'
                                    ,'<code>[booking ...]</code>'
                                    ); 
                            ?></p>
                            <span class="versions">{ <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">Business Medium / Large, MultiUser</a> <?php _e( "versions", 'wpdev-booking-overview' ); ?> }</span>
                        </div>     
                        <div class="col-3 last-feature">
                            <h4><?php _e( "Auto expand booking notes", 'wpdev-booking-overview' ); ?></h4>
                            <p><?php echo '<strong>'; _e('Trick'); echo '.</strong> '; printf(__( "Ability to set %s of the constant %s in %s file for expand %sfilled notes section%s of bookings in booking listing page", 'wpdev-booking-overview' )
                                    ,'<code>TRUE</code>'
                                    ,'<code>WP_BK_SHOW_BOOKING_NOTES</code>'
                                    ,' <em>wpdev-booking.php</em> '
                                    ,'<em>"','"</em>'
                                    ,'<em>"','"</em>'                                    
                                    ); ?></p>                            
                            <span class="versions">{ <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">Personal, Business Small / Medium / Large, MultiUser</a> <?php _e( "versions", 'wpdev-booking-overview' ); ?> }</span>
                        </div>     
                    </div>
                </div>
                    
                <div class="clear" style="height:30px;border-top:1px solid #DFDFDF;"></div>
                
                <div>
                    <p class='description'>                        
                    <?php printf( __( 'For more information about current update, see %srelease notes%s or check', 'wpdev-booking-overview' )
                            ,'<a class="button button-secondary" href="http://wpbookingcalendar.com/changelog/" target="_blank">','</a>'); 
                    ?> <a class='button button-primary'  href="http://wpbookingcalendar.com/updates/whats-new-5-0/" target="_blank"><?php _e( "What's New in previous Update", 'wpdev-booking-overview' );?> 5.0 ...</a>                    
                    </p>
                </div>

                <div class="return-to-dashboard">
                        <a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' =>  WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking-option' ), 'admin.php' ) ) ); ?>"><?php _e( 'Go to WP Booking Calendar Settings', 'wpdev-booking-overview' ); ?></a>
                </div>
            <?php
        }
        
        
	public function about_premium_screen_5_0() {
		list( $display_version ) = explode( '-', WPDEV_BK_VERSION );
		?>
		<div class="wrap about-wrap wpbc-welcome-page">
			
                    <?php 
                    $this->title_section();                                     // Show Title of Welcome page
                    ?>
                    <p class="update-nag"><?php _e( "New Features in the Paid versions of", 'wpdev-booking-overview' ); ?> <?php echo ' WP Booking Calendar '.$display_version ; ?></p>
                    
                    
                    <div class="changelog">
                        <h3><?php _e( "New Calendar Overview panel for Multiple Booking Resources ('Matrix')", 'wpdev-booking-overview' ); ?></h3>
                        <div class="feature-section col three-col">
                            <img src="<?php echo $this->asset_path; ?>5.0/calendar-overview-for-several-resources.png" style="width: 99%;border:none;box-shadow: 0 1px 3px #777777;margin-top: 1px;">
                            <div>
                                <h4><?php _e( "Beautiful Easy to Understand Interface", 'wpdev-booking-overview' ); ?></h4>
                                <p><?php _e( "Resources at the first column and booking dates are in right rows.", 'wpdev-booking-overview' ); ?></p>
                            </div>
                            <div>
                                <h4><?php _e( "Day, Week, Month or 2 Months View Mode", 'wpdev-booking-overview' ); ?></h4>
                                <p><?php _e( "Possibility to set a Day, Week, Month or 2 Months view mode for the 'Matrix' Calendar Overview panel.", 'wpdev-booking-overview' ); ?> </p>
                            </div>
                            <div class="last-feature">
                                <h4><?php _e( "Select Several Specific or All Resources", 'wpdev-booking-overview' ); ?></h4>
                                <p><?php _e( "Possibility to select several specific or all booking resources at 'Filter tab' of Booking Listing or Calendar Overview panel.", 'wpdev-booking-overview' ); ?></p>
                            </div>
                            <span class="versions">{ <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">Personal, Business Small / Medium / Large, MultiUser</a> <?php _e( "versions", 'wpdev-booking-overview' ); ?> }</span>
                        </div>        
                    </div>                    
               
                    
                    <div class="changelog">
                        <div class="feature-section images-stagger-right">
                           <h3><?php _e( "Show Pending Days as Available", 'wpdev-booking-overview' ); ?></h3> 
                           <img src="<?php echo $this->asset_path; ?>5.0/pending-days-available.png" style="width: 50%;border:none;box-shadow: 0 1px 3px #777777;margin-top: 1px;">
                           <div>
                                <h4><?php _e( "Possibility to book Pending Days", 'wpdev-booking-overview' ); ?></h4>
                                <p><?php _e( "Set Pending booked dates as available in calendar, until you'll Approve some booking. Do not lose any bookings, if some bookings were made by mistake or it was spam.", 'wpdev-booking-overview' ); ?> 
                                   </p>
                           </div>
                           <div class="last-feature">
                                <h4><?php _e( "Auto Decline", 'wpdev-booking-overview' ); ?></h4>
                                <p><?php _e( "Activate the Auto decline (delete) all pending bookings in your booking resource for the specific date(s), if you've approved other booking for these date(s).", 'wpdev-booking-overview' ); ?>
                                   </p>
                           </div>
                           <span class="versions">{ <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">Business Large, MultiUser</a> <?php _e( "versions", 'wpdev-booking-overview' ); ?> }</span>
                        </div>
                    </div>                    
      
                    
                    <div class="changelog">
                        <div class="feature-section images-stagger-left">  
                            <h3><?php _e( "Different Time Slots (or other content) for the Different Selected Days.", 'wpdev-booking-overview' ); ?></h3> 
                            <img src="<?php echo $this->asset_path; ?>5.0/different-times-selection.png" style="border: medium none;box-shadow: 0 1px 3px #777777;width: 45%;">
                            <div>
                                <br><h4><?php _e( "Set Different Time Slots for the Different Days.", 'wpdev-booking-overview' ); ?></h4>
                                <p><?php _e( "You can configure different Time Slots availability for the different days (weekday or date from season filter). Each week day (day of specific season filter) can have different time slots list.", 'wpdev-booking-overview' ); ?>               
                                   <?php printf( __( "Check  more %shere%s.", 'wpdev-booking-overview' ),"<a href='http://wpbookingcalendar.com/help/different-time-slots-selections-for-different-days/' target='_blank'>",'</a>') ?>.</p>
                            </div>
                            <div class="last-feature">
                                <h4><?php _e( "Show Specific Form Content, if Desire Date is Selected", 'wpdev-booking-overview' ); ?></h4>
                                <p><?php _e( "You need to show specific field(s), form section or just text, if specific date is selected (weekday or date from season filter) - it's also possible.", 'wpdev-booking-overview' ); ?>
                                   <?php printf( __( "Check  more %shere%s.", 'wpdev-booking-overview' ),"<a href='http://wpbookingcalendar.com/help/different-content-for-different-days-selection/' target='_blank'>",'</a>') ?>.</p>
                            </div>
                            <span class="versions"> { <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">Business Medium/Large, MultiUser</a> <?php _e( "versions", 'wpdev-booking-overview' ); ?> }</span>
                        </div>
                    </div>
                    
                    
                    <div class="changelog">
                        <div class="feature-section images-stagger-right">
                            <h3><?php _e( "Configure Days Selection as You Need.", 'wpdev-booking-overview' ); ?></h3> 
                            
                                <img src="<?php echo $this->asset_path; ?>5.0/days-selection-configuration.png" style="border: medium none;box-shadow: 0 1px 3px #777777;width: 45%;">
                                
                                <h4><?php _e( "Configure Number of Days selection", 'wpdev-booking-overview' ); ?></h4>
                                <p><?php _e( "Specify that during certain seasons (or week days), the specific minimum (or fixed) number of days must be booked.", 'wpdev-booking-overview' ); ?>
                                   <?php printf( __( "Check  more about 'options' %shere%s.", 'wpdev-booking-overview' ),"<a href='http://wpbookingcalendar.com/help/booking-calendar-shortcodes/' target='_blank'>",'</a>') ?>                                   
                                   <br><span class="versions" style="clear:both;float:none;"> { <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">Business Medium/Large, MultiUser</a> <?php _e( "versions", 'wpdev-booking-overview' ); ?> }</span>
                                </p>
                                
                                <h4><?php _e( "Set several Start Days", 'wpdev-booking-overview' ); ?></h4>
                                <p><?php _e( "Specify several weekdays as possible start day for the range days selection.", 'wpdev-booking-overview' ); ?></p>
                            
                            
                                <h4><?php _e( "Easy configuration of range days selection", 'wpdev-booking-overview' ); ?></h4>
                                <p><?php _e( "Configure 'specific days selections', for the 'range days selection' mode, in more comfortable way.", 'wpdev-booking-overview' ); ?>  
                                   <?php _e( "Separate days by dash or comma.", 'wpdev-booking-overview' ); ?>
                                   <br><em><?php printf( __( "Example: '%s'. It's mean possibility to select: %s days.", 'wpdev-booking-overview' ),'3-5,7,14','3, 4, 5, 7, 14'); ?></em>
                                   <br><span class="versions"> { <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">Business Small/Medium/Large, MultiUser</a> <?php _e( "versions", 'wpdev-booking-overview' ); ?> }</span>
                                </p>
                            
                            
                        </div>
                    </div>

                    
                    <div class="changelog">
                        <div class="feature-section images-stagger-left">  
                            <h3><?php _e( "Easily configure your booking form fields.", 'wpdev-booking-overview' ); ?></h3> 
                            <img src="<?php echo $this->asset_path; ?>5.0/form-fields.png" style="border: medium none;box-shadow: 0 1px 3px #777777;width: 20%;">
                            <div>
                                <h4><?php _e( "New help system for booking form fields configuration.", 'wpdev-booking-overview' ); ?></h4>
                                <p><?php _e( "You can configure different form field’s shortcodes easily using smart configuration panel.", 'wpdev-booking-overview' ); ?>               
                            </div>
                            <div class="last-feature">
                                <h4><?php _e( "New form Fields and parameters", 'wpdev-booking-overview' ); ?></h4>
                                <p><?php _e( "Booking Calendar support many new additional parameters for exist shortcodes, and some new fields, like radio buttons.", 'wpdev-booking-overview' ); ?>
                            </div>
                            <span class="versions"> { <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">Personal, Business Small/Medium/Large, MultiUser</a> <?php _e( "versions", 'wpdev-booking-overview' ); ?> }</span>
                        </div>
                    </div>
                    
                    
                    <div class="changelog">
                        <div class="feature-section images-stagger-right">
                            <h3><?php _e( "Payments and Costs.", 'wpdev-booking-overview' ); ?></h3> 
                                <img src="<?php echo $this->asset_path; ?>5.0/payment-forms.png" style="border: medium none;box-shadow: 0 1px 3px #777777;margin-top: -20px;width: auto;">
                            <h4>Authorize.Net</h4>
                            <p><?php printf( __( "Integration %s Payment Gateway - Server Integration Method (SIM).", 'wpdev-booking-overview' ),'Authorize.Net'); ?> 
                               <?php _e( "Integration of this gateway was one of the most our common user requests.", 'wpdev-booking-overview' ); ?> 
                               <?php _e( "Now it's existing.", 'wpdev-booking-overview' ); ?>
                            </p>

                            <h4><?php _e( "Currency Format", 'wpdev-booking-overview' ); ?></h4>
                            <p><?php _e( "Configure format of showing the cost in the payment form.", 'wpdev-booking-overview' ); ?>
                                <br><span class="versions">{ <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">Business Small / Medium / Large, MultiUser</a> <?php _e( "versions", 'wpdev-booking-overview' ); ?> }</span>
                            </p><br>
                            
                            <h4><?php _e( "Do More with  Less Actions", 'wpdev-booking-overview' ); ?></h4>     
                            <p><?php _e( "Assign  Availability, Rates, Valuations days or Deposit amount to the several booking resources in 1 step.", 'wpdev-booking-overview' ); ?> 
                               <?php _e( "Select several booking resources. Click on Availability, Rates, Valuations days or Deposit button.", 'wpdev-booking-overview' ); ?> 
                               <?php _e( "Configure and Update settings.  That's it", 'wpdev-booking-overview' ); ?>              
                               <br><span class="versions">{ <?php _e( "Available in", 'wpdev-booking-overview' ); ?> <a href="http://wpbookingcalendar.com" target="_blank">Business Medium / Large, MultiUser</a> <?php _e( "versions", 'wpdev-booking-overview' ); ?> }</span>
                            </p>
                            
                        </div>
                    </div>
                    
                    
                    

<div class="changelog"> 

    <h3><?php _e('Under the Hood','wpdev-booking-overview');?></h3>
    <div class="feature-section col three-col">
        <div>                            
            <h4><?php echo "[denyreason] "; _e( "shortcode for 'Approve' email", 'wpdev-booking-overview' ); ?></h4>                            
            <p><?php _e( "It means that we can write some text into the 'Reason of cancelation' field for having this custom text in the Approve email template.", 'wpdev-booking-overview' ); ?> 
               <br><span class="versions">{ <a href="http://wpbookingcalendar.com" target="_blank">Personal, Business Small / Medium / Large, MultiUser</a> }</span>
            </p>            
        </div>     
        <div>
            <h4><?php printf( __( "%s Support", 'wpdev-booking-overview' ),'qTranslate'); ?></h4>
            <p><?php printf( __( "Full support of the %s plugin. ", 'wpdev-booking-overview' ),'qTranslate'); ?> 
                <?php _e( "Including the search results content and links to the post with booking forms in correct language.", 'wpdev-booking-overview' ); ?>
               <br><span class="versions">{ <a href="http://wpbookingcalendar.com" target="_blank">Personal, Business Small / Medium / Large, MultiUser</a> }</span>
            </p>            
            
        </div>
        <div class="last-feature">
            <h4><?php _e( "Edit Past Bookings", 'wpdev-booking-overview' ); ?></h4>
            <p><?php _e( "Edit your bookings, where the time or dates have been already in the past.", 'wpdev-booking-overview' ); ?> 
            <br><span class="versions">{ <a href="http://wpbookingcalendar.com" target="_blank">Personal, Business Small / Medium / Large, MultiUser</a> }</span>
            </p>

            <?php /** ?><h4><?php _e( "Delete your Saved Filter", 'wpdev-booking-overview' ); ?></h4>
            <p><?php _e( "Delete your Default saved filter template on the Booking Listing page at Filter tab", 'wpdev-booking-overview' ); ?> 
                <br><span class="versions">{ <a href="http://wpbookingcalendar.com" target="_blank">Personal, Business Small / Medium / Large, MultiUser</a> }</span>
            </p><?php /**/ ?>
            
        </div>
    </div>

    
    <div class="feature-section col three-col">
        <div>
            <h4><?php _e( "Checking End Time only", 'wpdev-booking-overview' ); ?></h4>
            <p><?php _e( "Set checking is only for end time of bookings for Today.", 'wpdev-booking-overview' ); ?>  
               <?php _e( "So start time could be already in a past.", 'wpdev-booking-overview' ); ?> 
               <?php _e( "Reactivate old checking using", 'wpdev-booking-overview' ); ?> JavaScript <code>is_check_start_time_gone=true;</code>
            <br><span class="versions">{ <a href="http://wpbookingcalendar.com" target="_blank">Business Small / Medium / Large, MultiUser</a> }</span>
            </p>
        </div>        
        <div>
            <h4><?php _e( "Set cost for the 'Check Out' date", 'wpdev-booking-overview' ); ?></h4>                            
            <p><?php printf( __( "Possibility to use new reserved word %s (Check Out date) in the Valuation days cost settings page for the field 'For'. So you can define the cost of the last selected date.", 'wpdev-booking-overview' ),'<strong><code>LAST</code></strong>'); ?>
               <br><span class="versions">{ <a href="http://wpbookingcalendar.com" target="_blank">Business Medium / Large, MultiUser</a> }</span>
            </p>

        </div>    
        <div class="last-feature">            
            <h4><?php _e( "Advanced Additional Cost", 'wpdev-booking-overview' ); ?></h4>
            <p><?php printf( __( "Set additional cost of the booking as percentage of the original booking cost. This original booking cost doesn't have impact from any other additional cost settings. Usage: just write %s sign before percentage number. Example: %s.", 'wpdev-booking-overview' ),"'+'", "+50%"); ?>
            <br><span class="versions">{ <a href="http://wpbookingcalendar.com" target="_blank">Business Medium / Large, MultiUser</a> }</span>   
            </p>                        
        </div>
    </div>
    
    

    
    <div class="feature-section col three-col">
        <div>
            <h4><?php _e( "Set 'Check In/Out' dates as Available", 'wpdev-booking-overview' ); ?></h4>
            <p><?php _e( "Set 'check in/out' dates as available for resources with capacity higher than one. It's useful, if check in date of booking have to be the same as check out date of other booking.", 'wpdev-booking-overview' ); ?>
            <br><span class="versions">{ <a href="http://wpbookingcalendar.com" target="_blank">Business Large, MultiUser</a> }</span>
            </p>
        </div>
        <div>
            <h4><?php _e( "Visitors Selection in Search Result", 'wpdev-booking-overview' ); ?></h4>
            <p><?php _e( "Auto selection of the correct visitor number in the booking form, when the user redirected from the search form.", 'wpdev-booking-overview' ); ?> 
            <br><span class="versions">{ <a href="http://wpbookingcalendar.com" target="_blank">Business Large, MultiUser</a> }</span>    
            </p>
            
        </div>
        <div class="last-feature">
            <h4><?php _e( "Use 'Unavailable days from today' settings in Search Form", 'wpdev-booking-overview' ); ?></h4>
            <p><?php _e( "Using the settings of 'Unavailable days from today' for the calendars in the search form.", 'wpdev-booking-overview' ); ?>
            <br><span class="versions">{ <a href="http://wpbookingcalendar.com" target="_blank">Business Large, MultiUser</a> }</span>    
            </p>
            
        </div>
    </div>
    

    
    
    <div class="feature-section col two-col">
        <div>
            <h4><?php _e( "Shortcodes for Emails and Content Form", 'wpdev-booking-overview' ); ?></h4>                            
            <p><?php _e( "Long list of useful shortcodes for the Email Templates and the 'Content of Booking Fields' form", 'wpdev-booking-overview' ); ?>:  
                <code>[cost_hint]</code>, 
                <code>[original_cost_hint]</code>, 
                <code>[additional_cost_hint]</code>, 
                <code>[deposit_hint]</code>, 
                <code>[balance_hint]</code>, 
                <code>[check_in_date_hint]</code>, 
                <code>[check_out_date_hint]</code>, 
                <code>[start_time_hint]</code>, 
                <code>[end_time_hint]</code>, 
                <code>[selected_dates_hint]</code>, 
                <code>[selected_timedates_hint]</code>, 
                <code>[selected_short_dates_hint]</code>, 
                <code>[selected_short_timedates_hint]</code>, 
                <code>[days_number_hint]</code>, 
                <code>[nights_number_hint]</code> 
             
             </p>
             <span class="versions">{ <a href="http://wpbookingcalendar.com" target="_blank">Business Medium / Large, MultiUser</a> }</span>
        </div>    
        <div class="last-feature">            
            <h4><?php _e( "New Shortcodes for Hints in booking form.", 'wpdev-booking-overview' ); ?></h4>                            
            <p><?php printf(__( "New shortcodes for showing real-time hints (like %s) inside of the booking form", 'wpdev-booking-overview' ),'[cost_hint]'); ?>:
                <code>[deposit_hint]</code>, 
                <code>[balance_hint]</code>, 
                <code>[check_in_date_hint]</code>, 
                <code>[check_out_date_hint]</code>, 
                <code>[start_time_hint]</code>, 
                <code>[end_time_hint]</code>, 
                <code>[selected_dates_hint]</code>, 
                <code>[selected_timedates_hint]</code>, 
                <code>[selected_short_dates_hint]</code>, 
                <code>[selected_short_timedates_hint]</code>, 
                <code>[days_number_hint]</code>, 
                <code>[nights_number_hint]</code> 
            
            </p>
            <span class="versions">{ <a href="http://wpbookingcalendar.com" target="_blank">Business Medium / Large, MultiUser</a> }</span>
        </div>
    </div>
    
    
    
    <p><?php _e( "Many other improvements and small issue fixing.", 'wpdev-booking-overview' ); ?> <?php 
       printf( __( 'For more information, see %sthe release notes%s', 'wpdev-booking-overview' ),'<a href="http://wpbookingcalendar.com/changelog/" target="_blank">','</a>') ?>.</p>                            

</div>

                                
                    <div class="return-to-dashboard">
                            <a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' =>  WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking-option' ), 'admin.php' ) ) ); ?>"><?php _e( 'Go to WP Booking Calendar Settings', 'wpdev-booking-overview' ); ?></a>
                    </div>

		</div>
		<?php
	}

	/**
	 * Sends user to the Welcome page on first activation of plugin as well as each
	 * time plugin is upgraded to a new version
	 */
	public function welcome() {
            // Bail if no activation redirect transient is set
            if ( ! get_transient( '_wpbc_activation_redirect' ) )
                    return;

                // Delete the redirect transient
                delete_transient( '_wpbc_activation_redirect' );

                // Bail if DEMO or activating from network, or bulk, or within an iFrame
                if ( wpdev_bk_is_this_demo() || is_network_admin() || isset( $_GET['activate-multi'] ) || defined( 'IFRAME_REQUEST' ) )
                        return;

                wp_safe_redirect( admin_url( 'index.php?page=wpbc-about' ) );
                exit;                
	}
}
new WPBC_Welcome();

function wpbc_welcome_panel() {
    ?>
<style type="text/css" media="screen">
/*<![CDATA[*/
    /* WPBC Welcome Panel */                
    .wpbc-panel .welcome-panel {
        background: linear-gradient(to top, #F5F5F5, #FAFAFA) repeat scroll 0 0 #F5F5F5;
        border-color: #DFDFDF;
    }
    .wpbc-panel .welcome-panel {
            position: relative;
            overflow: auto;
            margin: 20px 0;
            padding: 23px 10px 12px;
            border-width: 1px;
            border-style: solid;
            border-radius: 3px;
            font-size: 13px;
            line-height: 2.1em;
    }
    .wpbc-panel .welcome-panel h3 {
            margin: 0;
            font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", sans-serif;
            font-size: 21px;
            font-weight: normal;
            line-height: 1.2;
    }
    .wpbc-panel .welcome-panel h4 {
            margin: 1.33em 0 0;
            font-size: 13px;
    }
    .wpbc-panel .welcome-panel a{
        color:#21759B;
    }
    .wpbc-panel .welcome-panel .about-description {
            font-size: 16px;
            margin: 0;
    }
    .wpbc-panel .welcome-panel .welcome-panel-close {
            position: absolute;
            top: 5px;
            right: 10px;
            padding: 8px 3px;
            font-size: 13px;
            text-decoration: none;
            line-height: 1;
    }
    .wpbc-panel .welcome-panel .welcome-panel-close:before {
            content: ' ';
            position: absolute;
            left: -12px;
            width: 10px;
            height: 100%;
            background: url('../wp-admin/images/xit.gif') 0 7% no-repeat;
    }
    .wpbc-panel .welcome-panel .welcome-panel-close:hover:before {
            background-position: 100% 7%;
    }
    .wpbc-panel .welcome-panel .button.button-hero {
        margin: 15px 0 3px;
    }
    .wpbc-panel .welcome-panel-content {
            margin-left: 13px;
            max-width: 1500px;
    }
    .wpbc-panel .welcome-panel .welcome-panel-column-container {
            clear: both;
            overflow: hidden;
            position: relative;
    }
    .wpbc-panel .welcome-panel .welcome-panel-column {
            width: 32%;
            min-width: 200px;
            float: left;
    }
    .ie8 .wpbc-panel .welcome-panel .welcome-panel-column {
            min-width: 230px;
    }
    .wpbc-panel .welcome-panel .welcome-panel-column:first-child {
            width: 36%;
    }
    .wpbc-panel .welcome-panel-column p {
            margin-top: 7px;
    }
    .wpbc-panel .welcome-panel .welcome-icon {
        background: none;    
        display: block;
        padding: 2px 0 8px 2px;    
    }
    .wpbc-panel .welcome-panel .welcome-add-page {
            background-position: 0 2px;
    }
    .wpbc-panel .welcome-panel .welcome-edit-page {
            background-position: 0 -90px;
    }
    .wpbc-panel .welcome-panel .welcome-learn-more {
            background-position: 0 -136px;
    }
    .wpbc-panel .welcome-panel .welcome-comments {
            background-position: 0 -182px;
    }
    .wpbc-panel .welcome-panel .welcome-view-site {
            background-position: 0 -274px;
    }
    .wpbc-panel .welcome-panel .welcome-widgets-menus {
            background-position: 1px -229px;
            line-height: 14px;
    }
    .wpbc-panel .welcome-panel .welcome-write-blog {
            background-position: 0 -44px;
    }
    .wpbc-panel .welcome-panel .welcome-panel-column ul {
            margin: 0.8em 1em 1em 0;
    }
    .wpbc-panel .welcome-panel .welcome-panel-column li {
        line-height: 16px;
        list-style-type: none;
    }
    @media screen and (max-width: 870px) {
            .wpbc-panel .welcome-panel .welcome-panel-column,
            .wpbc-panel .welcome-panel .welcome-panel-column:first-child {
                    display: block;
                    float: none;
                    width: 100%;
            }
            .wpbc-panel .welcome-panel .welcome-panel-column li {
                    display: inline-block;
                    margin-right: 13px;
            }
            .wpbc-panel .welcome-panel .welcome-panel-column ul {
                    margin: 0.4em 0 0;
            }
            .wpbc-panel .welcome-panel .welcome-icon {
                    padding-left: 25px;
            }
    }
/*]]>*/
</style>                
<div id="wpbc-panel-get-started" class="wpbc-panel" style="display:none;"> <div class="welcome-panel"> 
<?php 
    $is_panel_visible = false;
    if ( (class_exists('WPBC_Dismiss')) && (! wpdev_bk_is_this_demo() ) ){
        global $wpbc_Dismiss;
        $is_panel_visible = $wpbc_Dismiss->render(array( 'id'    =>  'wpbc-panel-get-started',
                                     'title' =>  __( 'Dismiss' , 'wpdev-booking-overview' )
                              ));
    }
    if ($is_panel_visible)
        wpbc_welcome_panel_content(); 
?> 
</div> </div>
<?php
}

function wpbc_welcome_panel_content() {
?>
    <div class="welcome-panel-content">
    <p class="about-description"><?php _e( 'We&#8217;ve assembled some links to get you started:' ); ?></p>
    <div class="welcome-panel-column-container">
        <div class="welcome-panel-column">
            <h4><?php _e( 'Get Started' ,'wpdev-booking-overview'); ?></h4>
            <ul>
                <li><?php printf( '<div class="welcome-icon">' . 
                        __( 'Insert booking form %sshortcode%s into your %sPost%s or %sPage%s' , 'wpdev-booking-overview' ) .
                        '</div>',
                        '<strong>','</strong>', 
                        '<a href="'.admin_url( 'edit.php' ).'">', '</a>', 
                        '<a href="'.admin_url( 'edit.php?post_type=page' ).'">', '</a>'); ?></li>                            

                <li><?php printf( '<div class="welcome-icon">' . 
                        __( 'or add booking calendar %sWidget%s to your sidebar.'  , 'wpdev-booking-overview' ) .
                        '</div>',
                        '<a href="'.admin_url( 'widgets.php' ).'">', '</a>'); ?></li>                            

                <li><?php printf( '<div class="welcome-icon">' .                             
                        __( 'Check %show todo%s that and what %sshortcodes%s are available.' , 'wpdev-booking-overview' ).
                        '</div>', 
                        '<a href="http://wpbookingcalendar.com/help/inserting-booking-form/" target="_blank">', '</a>', 
                        '<a href="http://wpbookingcalendar.com/help/booking-calendar-shortcodes/" target="_blank">', '</a>' ); ?></li>
                <li><?php printf( '<div class="welcome-icon">' .                             
                        __( 'Add new booking from your post/page or from %sAdmin Panel%s.' , 'wpdev-booking-overview'  ).
                        '</div>', 
                        '<a href="'. esc_url( admin_url( add_query_arg( array( 'page' =>  WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking-reservation' ), 'admin.php' ) ) ) .'">', '</a>' ); ?></li>                    
            </ul>
        </div>
        <div class="welcome-panel-column">
                <h4><?php _e( 'Next Steps' ,'wpdev-booking-overview'); ?></h4>
                <ul>
                    <li><?php printf( '<div class="welcome-icon">' . 
                        __( 'Check %sBooking Listing%s page for new bookings.' , 'wpdev-booking-overview'   ) .
                        '</div>',                            
                        '<a href="'. esc_url( admin_url( add_query_arg( array( 'page' =>  WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking-overview' ), 'admin.php' ) ) ) .'">', 
                        '</a>' ); ?></li>                                                    
                    <li><?php printf( '<div class="welcome-icon">' . 
                        __( 'Configure booking %sSettings%s.' , 'wpdev-booking-overview'   ) .
                        '</div>',                            
                        '<a href="'. esc_url( admin_url( add_query_arg( array( 'page' =>  WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking-option' ), 'admin.php' ) ) ) .'">', 
                        '</a>' ); ?></li>                            
                    <li><?php printf( '<div class="welcome-icon">' . 
                        __( 'Configure predefined set of your %sForm Fields%s.' , 'wpdev-booking-overview'   ) .
                        '</div>',                            
                        '<a href="'. esc_url( admin_url( add_query_arg( array( 'page' =>  WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking-option&tab=form' ), 'admin.php' ) ) ) .'">', 
                        '</a>' ); ?></li>                            
                </ul>
        </div>
        <div class="welcome-panel-column welcome-panel-last">
                <h4><?php _e( 'Have a questions?' ,'wpdev-booking-overview'); ?></h4>
                <ul>
                    <li><?php printf( '<div class="welcome-icon">' . 
                        __( 'Check out our %sHelp%s' , 'wpdev-booking-overview'   ) .
                        '</div>',                            
                        '<a href="http://wpbookingcalendar.com/help/" target="_blank">', 
                        '</a>' ); ?></li>                            
                    <li><?php printf( '<div class="welcome-icon">' . 
                        __( 'See %sFAQ%s.' , 'wpdev-booking-overview'   ) .
                        '</div>',                            
                        '<a href="http://wpbookingcalendar.com/faq/" target="_blank">', 
                        '</a>' ); ?></li>                            
                    <li><?php printf( '<div class="welcome-icon">' . 
                        __( 'Still having questions? Contact %sSupport%s.' , 'wpdev-booking-overview'   ) .
                        '</div>',                            
                        '<a href="http://wpbookingcalendar.com/support/" target="_blank">', 
                        '</a>' ); ?></li>                            
                </ul>
        </div>
    </div>
    <?php printf( '<div class="welcome-icon welcome-widgets-menus" style="text-align:right;font-style:italic;">' . __( 'Need even more functionality? Check <a href="%1$s" target="_blank">higher versions</a>' ) . '.</div>', 'http://wpbookingcalendar.com/features/' ); ?>
    </div> 
    <?php
}

?>