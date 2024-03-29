    var is_booking_without_payment = false;
    var date_approved = [];
    var date2approve = [];
    var date_admin_blank = [];
    var dates_additional_info = [];
    var is_all_days_available = [];
    var avalaibility_filters = [];
    var is_show_cost_in_tooltips = false;
    var is_show_availability_in_tooltips = false;
    var global_avalaibility_times = [];   
    var numbb = 0;
    //var is_use_visitors_number_for_availability;
    var timeoutID_of_thank_you_page = null;    

    // Initialisation
    function init_datepick_cal(bk_type,  date_approved_par, my_num_month, start_day_of_week, start_bk_month  ){
        
            if ( jQuery('#calendar_booking'+ bk_type).hasClass('hasDatepick') == true ) { // If the calendar with the same Booking resource is activated already, then exist.
                return false;
            }

            var cl = document.getElementById('calendar_booking'+ bk_type);if (cl === null) return; // Get calendar instance and exit if its not exist

            date_approved[ bk_type ] = date_approved_par;

            var isRangeSelect = false;
            var bkMultiDaysSelect   = 365;
            if ( bk_days_selection_mode==='dynamic' ) { isRangeSelect     = true; bkMultiDaysSelect = 0; }
            if ( bk_days_selection_mode==='single' )    bkMultiDaysSelect = 0;

            var bkMinDate = 0;
            var bkMaxDate = booking_max_monthes_in_calendar;
            
            var is_this_admin = false;
            if ( (location.href.indexOf('wpdev-booking.phpwpdev-booking-reservation') != -1 ) &&
                 (location.href.indexOf('booking_hash') != -1 ) ) {
                is_this_admin = true; 
                bkMinDate = null;
                bkMaxDate = null;
            }

            function click_on_cal_td(){
                if(typeof( selectDayPro ) == 'function') {selectDayPro(  bk_type);}
            }

            function selectDay(date) { 

                if(typeof( bkRangeDaysSelection ) == 'function') { // Check if this minimum BS version, and then proced
                    jQuery('.datepick-days-cell' ).popover('hide');
                }
                jQuery('#date_booking' + bk_type).val(date);              
                if(typeof( selectDayPro ) == 'function') {selectDayPro( date, bk_type);}
                
                
            }

            function hoverDay(value, date){ 

                if(typeof( hoverDayTime ) == 'function') {hoverDayTime(value, date, bk_type);}

                if ( (location.href.indexOf('wpdev-booking.phpwpdev-booking')==-1) ||
                     (location.href.indexOf('wpdev-booking.phpwpdev-booking-reservation')>0) )
                { // Do not show it (range) at the main admin page
                    if(typeof( hoverDayPro ) == 'function')  {hoverDayPro(value, date, bk_type);}
                }
                //if(typeof( hoverAdminDay ) == 'function')  { hoverAdminDay(value, date, bk_type); }
             }

            function applyCSStoDays(date ){
                var class_day = (date.getMonth()+1) + '-' + date.getDate() + '-' + date.getFullYear();
                var additional_class = ' ';

                if(typeof( prices_per_day  ) !== 'undefined')
                    if(typeof(  prices_per_day[bk_type] ) !== 'undefined')
                        if(typeof(  prices_per_day[bk_type][class_day] ) !== 'undefined') {
                            additional_class += ' rate_'+prices_per_day[bk_type][class_day];
                        }
                
                // define season filter names as classes
                if(typeof( wpdev_bk_season_filter  ) !== 'undefined')
                        if(typeof(  wpdev_bk_season_filter[class_day] ) !== 'undefined') {
                            additional_class += ' '+wpdev_bk_season_filter[class_day].join(' ');
                        }

                        
                if (is_this_admin == false) {
                    var my_test_date = new Date( wpdev_bk_today[0],(wpdev_bk_today[1]-1), wpdev_bk_today[2] ,0,0,0 );  //Get today           
                    if ( (days_between( date, my_test_date)) < block_some_dates_from_today ) 
                        return [false, 'cal4date-' + class_day +' date_user_unavailable']; 
                }
                
                if (typeof( is_this_day_available ) == 'function') {
                    var is_day_available = is_this_day_available( date, bk_type);
                    if (! is_day_available) {return [false, 'cal4date-' + class_day +' date_user_unavailable'];}
                }

                // Time availability
                if (typeof( check_global_time_availability ) == 'function') {check_global_time_availability( date, bk_type );}

                var blank_admin_class_day = '';
                if(typeof(date_admin_blank[ bk_type ]) !== 'undefined')
                    if(typeof(date_admin_blank[ bk_type ][ class_day ]) !== 'undefined') {
                        blank_admin_class_day = ' date_admin_blank ';
                    }

                // Check availability per day for H.E.
                var reserved_days_count = 1;
                if(typeof(availability_per_day) !== 'undefined')
                if(typeof(availability_per_day[ bk_type ]) !== 'undefined')
                   if(typeof(availability_per_day[ bk_type ][ class_day ]) !== 'undefined') {
                      reserved_days_count = parseInt( availability_per_day[ bk_type ][ class_day ] );}

                // we have 0 available at this day - Only for resources, which have childs
                if (  wpdev_in_array( parent_booking_resources, bk_type ) )
                        if (reserved_days_count <= 0) {
                                if(typeof(date2approve[ bk_type ]) !== 'undefined')
                                   if(typeof(date2approve[ bk_type ][ class_day ]) !== 'undefined')
                                     return [false, 'cal4date-' + class_day +' date2approve date_unavailable_for_all_childs ' + blank_admin_class_day];
                                 return [false, 'cal4date-' + class_day +' date_approved date_unavailable_for_all_childs ' + blank_admin_class_day];
                        }

                //var class_day_previos = (date.getMonth()+1) + '-' + (date.getDate()-1) + '-' + date.getFullYear();
                var th=0;
                var tm=0;
                var ts=0;
                var time_return_value = false;
                // Select dates which need to approve, its exist only in Admin
                if(typeof(date2approve[ bk_type ]) !== 'undefined')
                   if(typeof(date2approve[ bk_type ][ class_day ]) !== 'undefined') {

                      for (var ia=0;ia<date2approve[ bk_type ][ class_day ].length;ia++) {


                          th = date2approve[ bk_type ][ class_day ][ia][3];
                          tm = date2approve[ bk_type ][ class_day ][ia][4];
                          ts = date2approve[ bk_type ][ class_day ][ia][5];
                          if ( ( th == 0 ) && ( tm == 0 ) && ( ts == 0 ) )
                              return [false, 'cal4date-' + class_day +' date2approve' + blank_admin_class_day]; // Orange
                          else {
                              if ( is_booking_used_check_in_out_time === true ) {
                                  if (ts == '1')  additional_class += ' check_in_time';
                                  if (ts == '2')  additional_class += ' check_out_time';
                              }
                              time_return_value = [true, 'date_available cal4date-' + class_day +' date2approve timespartly' + additional_class]; // Times
                              if(typeof( isDayFullByTime ) == 'function') {
                                  if ( isDayFullByTime(bk_type, class_day ) ) return [false, 'cal4date-' + class_day +' date2approve' + blank_admin_class_day]; // Orange
                              }
                          }

                      }

                   }

                //select Approved dates
                if(typeof(date_approved[ bk_type ]) !== 'undefined')
                  if(typeof(date_approved[ bk_type ][ class_day ]) !== 'undefined') {

                      for (var ia=0;ia<date_approved[ bk_type ][ class_day ].length;ia++) {

                          th = date_approved[ bk_type ][ class_day ][ia][3];
                          tm = date_approved[ bk_type ][ class_day ][ia][4];
                          ts = date_approved[ bk_type ][ class_day ][ia][5];
                          if ( ( th == 0 ) && ( tm == 0 ) && ( ts == 0 ) )
                            return [false, 'cal4date-' + class_day +' date_approved' + blank_admin_class_day]; //Blue or Grey in client
                          else {
                              if ( is_booking_used_check_in_out_time === true ) {
                                  if (ts == '1')  additional_class += ' check_in_time';
                                  if (ts == '2')  additional_class += ' check_out_time';
                              }
                            time_return_value = [true,  'date_available cal4date-' + class_day +' date_approved timespartly' + additional_class]; // Times
                            if(typeof( isDayFullByTime ) == 'function') {
                                if ( isDayFullByTime(bk_type, class_day ) ) return [false, 'cal4date-' + class_day +' date_approved' + blank_admin_class_day]; // Blue or Grey in client
                            }
                          }

                      }
                  }


                for (var i=0; i<user_unavilable_days.length;i++) {
                    if (date.getDay()==user_unavilable_days[i])   return [false, 'cal4date-' + class_day +' date_user_unavailable' ];
                }

                var is_datepick_unselectable = '';
                var is_calendar_booking_unselectable= jQuery('#calendar_booking_unselectable' + bk_type);
                if (is_calendar_booking_unselectable.length != 0 ) {
                    is_datepick_unselectable = 'datepick-unselectable'; //  //datepick-unselectable
                }

                if ( time_return_value !== false )  {
                    if ( is_booking_used_check_in_out_time === true ) {
                        // If the date is cehck in/out and the check in/out time is activated so  then  this date is unavailbale
                        if ( ( additional_class.indexOf('check_in_time') != -1 ) && ( additional_class.indexOf('check_out_time') != -1 ) ){ 
                            // Make this date unavailbale
                            time_return_value[0] = false;                             
                            // Remove CSS classes from this date
                            time_return_value[1]=time_return_value[1].replace("check_in_time",""); 
                            time_return_value[1]=time_return_value[1].replace("check_out_time",""); 
                            time_return_value[1]=time_return_value[1].replace("timespartly",""); 
                            time_return_value[1]=time_return_value[1].replace("date_available",""); 
                        }
                    }
                    return time_return_value;
                } else                                return [true, 'date_available cal4date-' + class_day +' reserved_days_count' + reserved_days_count + ' '  + is_datepick_unselectable + additional_class+ ' '];
            }

            function changeMonthYear(year, month){ 
                if(typeof( bkRangeDaysSelection ) == 'function') { // Check if this minimum BS version, and then proced
                    if(typeof( prepare_tooltip ) == 'function') {
                        setTimeout("prepare_tooltip("+bk_type+");",1000);
                    }
                }
                if(typeof( prepare_highlight ) == 'function') {
                 setTimeout("prepare_highlight();",1000);
                }
            }
            // Configure and show calendar
            jQuery('#calendar_booking'+ bk_type).text('');
            jQuery('#calendar_booking'+ bk_type).datepick(
                    {beforeShowDay: applyCSStoDays,
                        onSelect: selectDay,
                        onHover:hoverDay,
                        onChangeMonthYear:changeMonthYear,
                        showOn: 'both',
                        multiSelect: bkMultiDaysSelect,
                        numberOfMonths: my_num_month,
                        stepMonths: 1,
                        prevText: '&laquo;',
                        nextText: '&raquo;',
                        dateFormat: 'dd.mm.yy',
                        changeMonth: false, 
                        changeYear: false,
                        minDate: bkMinDate, maxDate: bkMaxDate, //'1Y',
                        showStatus: false,
                        multiSeparator: ', ',
                        closeAtTop: false,
                        firstDay:start_day_of_week,
                        gotoCurrent: false,
                        hideIfNoPrevNext:true,
                        rangeSelect:isRangeSelect,
                        useThemeRoller :false // ui-cupertino.datepick.css
                    }
            );


            if ( start_bk_month != false ) {
                var inst = jQuery.datepick._getInst(document.getElementById('calendar_booking'+bk_type));
                inst.cursorDate = new Date();
                inst.cursorDate.setFullYear( start_bk_month[0], (start_bk_month[1]-1) ,  1 );
                inst.drawMonth = inst.cursorDate.getMonth();
                inst.drawYear = inst.cursorDate.getFullYear();

                jQuery.datepick._notifyChange(inst);
                jQuery.datepick._adjustInstDate(inst);
                jQuery.datepick._showDate(inst);
                jQuery.datepick._updateDatepick(inst);
            }


            //jQuery('td.datepick-days-cell').bind('click', 'selectDayPro');
            if(typeof( bkRangeDaysSelection ) == 'function') { // Check if this minimum BS version, and then proced
                if(typeof( prepare_tooltip ) == 'function') {setTimeout("prepare_tooltip("+bk_type+");",1000);}
            }
    }

    // Get fisrst day of selection
    function get_first_day_of_selection(dates) {

        // Multiple days selections
        if ( dates.indexOf(',') != -1 ){                  
            var dates_array =dates.split(/,\s*/);
            var length = dates_array.length;
            var element = null;
            var new_dates_array = [];

            for (var i = 0; i < length; i++) {

              element = dates_array[i].split(/\./);

              new_dates_array[new_dates_array.length] = element[2]+'.' + element[1]+'.' + element[0];       //2013.12.20
            }        
            new_dates_array.sort();

            element = new_dates_array[0].split(/\./);

            return element[2]+'.' + element[1]+'.' + element[0];                    //20.12.2013
        }

        // Range days selection
        if ( dates.indexOf(' - ') != -1 ){                  
            var start_end_date = dates.split(" - ");
            return start_end_date[0];
        }

        // Single day selection 
        return dates;                                                               //20.12.2013
    }

    // Get fisrst day of selection
    function get_last_day_of_selection(dates) {

        // Multiple days selections
        if ( dates.indexOf(',') != -1 ){                  
            var dates_array =dates.split(/,\s*/);
            var length = dates_array.length;
            var element = null;
            var new_dates_array = [];

            for (var i = 0; i < length; i++) {

              element = dates_array[i].split(/\./);

              new_dates_array[new_dates_array.length] = element[2]+'.' + element[1]+'.' + element[0];       //2013.12.20
            }        
            new_dates_array.sort();

            element = new_dates_array[(new_dates_array.length-1)].split(/\./);

            return element[2]+'.' + element[1]+'.' + element[0];                    //20.12.2013
        }

        // Range days selection
        if ( dates.indexOf(' - ') != -1 ){                  
            var start_end_date = dates.split(" - ");
            return start_end_date[(start_end_date.length-1)];
        }

        // Single day selection 
        return dates;                                                               //20.12.2013
    }

    // Show Yes/No dialog
    function bk_are_you_sure( message_question ){
            var answer = confirm( message_question );
            if ( answer) { return true; }
            else         { return false;}
    }

    function bk_admin_show_message( message, m_type, m_delay ){
        
        document.getElementById('ajax_working').innerHTML = '<div id="bk_alert_message" class="bk_alert_message"></div>';
        
        var alert_class = 'alert ';
        if (m_type == 'error')      alert_class += 'alert-error '; 
        if (m_type == 'info')       alert_class += 'alert-info '; 
        if (m_type == 'success')    alert_class += 'alert-success '; 
                
        document.getElementById('bk_alert_message').innerHTML =   '<div class="'+alert_class+'"> ' +
                                                                        '<a class="close" data-dismiss="alert">&times;</a> ' + 
                                                                        message + 
                                                                     '</div>';
        jQuery('#bk_alert_message').animate( {opacity: 1}, m_delay ).fadeOut(500);        
    }

    // Set Booking listing row as   R e a d
    function set_booking_row_read(booking_id){
        if (booking_id == 0) {
            jQuery('.new-label').addClass('hidden_items');
            jQuery('.bk-update-count').html( '0' );
        } else {
            jQuery('#booking_mark_'+booking_id + '').addClass('hidden_items');
            decrese_new_counter();
        }
    }

    // Set Booking listing row as   U n R e a d
    function set_booking_row_unread(booking_id){
        jQuery('#booking_mark_'+booking_id + '').removeClass('hidden_items');
        increase_new_counter();
    }

    function increase_new_counter () {
        var my_num = parseInt(jQuery('.bk-update-count').html());
        my_num = my_num + 1;
        jQuery('.bk-update-count').html(my_num);
    }

    function decrese_new_counter () {
        var my_num = parseInt(jQuery('.bk-update-count').html());
        if (my_num>0){
            my_num = my_num - 1;
            jQuery('.bk-update-count').html(my_num);
        }
    }


                       



    // Functions for the TimeLine
    function set_booking_row_approved_in_timeline(booking_id){  
        ////Approve   Add    to   [cell_bk_id_9] class [approved]    -- TODO: Also  in the [a_bk_id_9] - chnaged "data-content" attribute
        jQuery('.cell_bk_id_'+booking_id).addClass('approved');
        jQuery('.timeline_info_bk_actionsbar_' + booking_id + ' .approve_bk_link').addClass('hidden_items');
        jQuery('.timeline_info_bk_actionsbar_' + booking_id + ' .pending_bk_link').removeClass('hidden_items');
    }
    function set_booking_row_pending_in_timeline(booking_id){
        //Remove    Remove from [cell_bk_id_9] class [approved]      -- TODO: Also  in the [a_bk_id_9] - chnaged "data-content" attribute
        jQuery('.cell_bk_id_'+booking_id).removeClass('approved');
        jQuery('.timeline_info_bk_actionsbar_' + booking_id + ' .pending_bk_link').addClass('hidden_items');
        jQuery('.timeline_info_bk_actionsbar_' + booking_id + ' .approve_bk_link').removeClass('hidden_items');
    }
    function set_booking_row_deleted_in_timeline(booking_id){
        //          Remove in [cell_bk_id_9]   classes [time_booked_in_day]
        //          Delete element: [a_bk_id_]
        // TODO: Here is possible issue, if we are have several bookings per the same date and deleted only one
        
        // make actions on the elements, which are not have CLASS: "here_several_bk_id"
        // And have CLASS a_bk_id_ OR cell_bk_id_        
        jQuery(':not(.here_several_bk_id).a_bk_id_'+booking_id).fadeOut(1000);
        jQuery(':not(.here_several_bk_id).cell_bk_id_'+booking_id).removeClass('time_booked_in_day');
    }
    
    // Set Booking listing   R O W   Approved
    function set_booking_row_approved(booking_id){
        jQuery('#booking_row_'+booking_id + ' .booking-labels .label-approved').removeClass('hidden_items');
        jQuery('#booking_row_'+booking_id + ' .booking-labels .label-pending').addClass('hidden_items');

        jQuery('#booking_row_'+booking_id + ' .booking-dates .field-booking-date').addClass('approved');

        jQuery('#booking_row_'+booking_id + ' .booking-actions .approve_bk_link').addClass('hidden_items');
        jQuery('#booking_row_'+booking_id + ' .booking-actions .pending_bk_link').removeClass('hidden_items');

    }

    // Set Booking listing   R O W   Pending
    function set_booking_row_pending(booking_id){
        jQuery('#booking_row_'+booking_id + ' .booking-labels .label-approved').addClass('hidden_items');
        jQuery('#booking_row_'+booking_id + ' .booking-labels .label-pending').removeClass('hidden_items');

        jQuery('#booking_row_'+booking_id + ' .booking-dates .field-booking-date').removeClass('approved');

        jQuery('#booking_row_'+booking_id + ' .booking-actions .approve_bk_link').removeClass('hidden_items');
        jQuery('#booking_row_'+booking_id + ' .booking-actions .pending_bk_link').addClass('hidden_items');

    }

    // Remove  Booking listing   R O W
    function set_booking_row_deleted(booking_id){
        jQuery('#booking_row_'+booking_id).fadeOut(1000);
    }

    // Set in Booking listing   R O W   Resource title
    function set_booking_row_resource_name(booking_id, resourcename){
        jQuery('#booking_row_'+booking_id + ' .booking-labels .label-resource').html(resourcename);
    }


    // Set in Booking listing   R O W   new Remark in hint
    function set_booking_row_remark_in_hint(booking_id, new_remark){
        jQuery('#booking_row_'+booking_id + ' .booking-actions .remark_bk_link').attr('data-original-title', new_remark);

        var my_img = jQuery('#booking_row_'+booking_id + ' .booking-actions .remark_bk_link img').attr('src');
        var check_my_img = my_img.substr( my_img.length - 7);
        if (check_my_img !== '_rd.png') {
            my_img = my_img.substr(0, my_img.length - 4);
            jQuery('#booking_row_'+booking_id + ' .booking-actions .remark_bk_link img').attr('src', my_img+'_rd.png');
        } else {
            my_img = my_img.substr(0, my_img.length - 7);
            jQuery('#booking_row_'+booking_id + ' .booking-actions .remark_bk_link img').attr('src', my_img+'.png');
        }


    }

    // Set in Booking listing   R O W   new Remark in hint
    function set_booking_row_payment_status(booking_id, payment_status, payment_status_show){

        jQuery('#booking_row_'+booking_id + ' .booking-labels .label-payment-status').removeClass('label-important');
        jQuery('#booking_row_'+booking_id + ' .booking-labels .label-payment-status').removeClass('label-success');

        jQuery('#booking_row_'+booking_id + ' .booking-labels .label-payment-status').html(payment_status_show);

        if (payment_status == 'OK') {
            jQuery('#booking_row_'+booking_id + ' .booking-labels .label-payment-status').addClass('label-success');
        } else if (payment_status == '') {
            jQuery('#booking_row_'+booking_id + ' .booking-labels .label-payment-status').addClass('label-important');
        } else {
            jQuery('#booking_row_'+booking_id + ' .booking-labels .label-payment-status').addClass('label-important');
        }
    }



    // Approve or set Pending  booking
    function approve_unapprove_booking(booking_id, is_approve_or_pending, user_id, wpdev_active_locale, is_send_emeils ) {

        if ( booking_id !='' ) {

            var wpdev_ajax_path     = wpdev_bk_plugin_url+'/'+wpdev_bk_plugin_filename;
            var ajax_type_action    = 'UPDATE_APPROVE';
            var ajax_bk_message     = 'Updating...';
            //var is_send_emeils      = 1;
            var denyreason          = '';
            if (is_send_emeils == 1) {
                is_send_emeils = jQuery('#is_send_email_for_pending').attr('checked');
                if (is_send_emeils == undefined) {is_send_emeils = 0 ;}
                else                             {is_send_emeils = 1 ;}
                denyreason = jQuery('#denyreason').val();
            } else {
                is_send_emeils = 0;
            }


            document.getElementById('ajax_working').innerHTML =
            '<div class="updated ajax_message" id="ajax_message">\n\
                <div style="float:left;">'+ajax_bk_message+'</div> \n\
                <div class="wpbc_spin_loader">\n\
                       <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
                </div>\n\
            </div>';

            jQuery.ajax({                                           // Start Ajax Sending
                url: wpdev_ajax_path,
                type:'POST',
                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                // beforeSend: someFunction,
                data:{
                    ajax_action : ajax_type_action,         // Action
                    booking_id : booking_id,                  // ID of Booking  - separator |
                    is_approve_or_pending : is_approve_or_pending,           // Approve: 1, Reject: 0
                    is_send_emeils : is_send_emeils,
                    denyreason: denyreason,
                    user_id: user_id,
                    wpdev_active_locale:wpdev_active_locale,
                    wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 
                }
            });
            return false;  
        }

        return true;
    }

    // Delete booking
    function delete_booking(booking_id, user_id, wpdev_active_locale, is_send_emeils ) {

        if ( booking_id !='' ) {

            var wpdev_ajax_path     = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename;
            var ajax_type_action    = 'DELETE_APPROVE';
            var ajax_bk_message     = 'Updating...';
            //var is_send_emeils      = 1;
            var denyreason          = '';
            if (is_send_emeils == 1) {
                is_send_emeils = jQuery('#is_send_email_for_pending').attr('checked');
                if (is_send_emeils == undefined) {is_send_emeils = 0 ;}
                else                             {is_send_emeils = 1 ;}
                denyreason = jQuery('#denyreason').val();
            } else {
                is_send_emeils = 0;
            }

            document.getElementById('ajax_working').innerHTML =
            '<div class="updated ajax_message" id="ajax_message">\n\
                <div style="float:left;">'+ajax_bk_message+'</div> \n\
                <div class="wpbc_spin_loader">\n\
                       <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
                </div>\n\
            </div>';

            jQuery.ajax({                                           // Start Ajax Sending
                url: wpdev_ajax_path,
                type:'POST',
                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                // beforeSend: someFunction,
                data:{
                    ajax_action : ajax_type_action,         // Action
                    booking_id : booking_id,                  // ID of Booking  - separator |
                    is_send_emeils : is_send_emeils,
                    denyreason: denyreason,
                    user_id: user_id,
                    wpdev_active_locale:wpdev_active_locale,
                    wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 
                }
            });
            return false;
        }

        return true;
    }


    // Mark as Read or Unread selected bookings
    function mark_read_booking(booking_id, is_read_or_unread, user_id, wpdev_active_locale ) {

        if ( booking_id !='' ) {

            var wpdev_ajax_path     = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename;
            var ajax_type_action    = 'UPDATE_READ_UNREAD';
            var ajax_bk_message     = 'Updating...';

            document.getElementById('ajax_working').innerHTML =
            '<div class="updated ajax_message" id="ajax_message">\n\
                <div style="float:left;">'+ajax_bk_message+'</div> \n\
                <div class="wpbc_spin_loader">\n\
                       <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
                </div>\n\
            </div>';

            jQuery.ajax({                                           // Start Ajax Sending
                url: wpdev_ajax_path,
                type:'POST',
                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                // beforeSend: someFunction,
                data:{
                    ajax_action : ajax_type_action,         // Action
                    booking_id : booking_id,                  // ID of Booking  - separator |
                    is_read_or_unread : is_read_or_unread,           // Read: 1, Unread: 0
                    user_id: user_id,
                    wpdev_active_locale:wpdev_active_locale,
                    wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value
                }
            });
            return false;
        }

        return true;
    }


    // Get the list of ID in selected bookings from booking listing
    function get_selected_bookings_id_in_booking_listing(){

        var checkedd = jQuery(".booking_list_item_checkbox:checked");
        var id_for_approve = "";

        // get all IDs
        checkedd.each(function(){
            var id_c = jQuery(this).attr('id');
            id_c = id_c.substr(20,id_c.length-20);
            id_for_approve += id_c + "|";
        });

        if ( id_for_approve.length > 1 )
            id_for_approve = id_for_approve.substr(0,id_for_approve.length-1);      //delete last "|"

        return id_for_approve ;
    }


    // Send booking Cacel by visitor
    function bookingCancelByVisitor(booking_hash, bk_type, wpdev_active_locale){

        
        if (booking_hash!='') {


            document.getElementById('submiting' + bk_type).innerHTML =
                '<div style="height:20px;width:100%;text-align:center;margin:15px auto;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//div>';

            var wpdev_ajax_path = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename;
            var ajax_type_action='DELETE_BY_VISITOR';
  
            jQuery.ajax({                                           // Start Ajax Sending
                url: wpdev_ajax_path,
                type:'POST',
                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond_insert' + bk_type).html( data ) ;},
                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                // beforeSend: someFunction,
                data:{
                    ajax_action : ajax_type_action,
                    booking_hash : booking_hash,
                    bk_type : bk_type,
                    wpdev_active_locale:wpdev_active_locale,
                    wpbc_nonce: document.getElementById('wpbc_nonce_delete'+bk_type).value 
                }
            });
            return false;
        }
        return true;
    }


    function showSelectedInDropdown(selector_id, title, value){
        jQuery('#' + selector_id + '_selector .wpbc_selected_in_dropdown').html( title );
        jQuery('#' + selector_id ).val( value );
        jQuery('#' + selector_id + '_container').hide();
    }
 
    // Scroll to script
    function makeScroll(object_name) {
         var targetOffset = jQuery( object_name ).offset().top;
         //targetOffset = targetOffset - 50;
         if (targetOffset<0) targetOffset = 0;
         if ( jQuery('#wpadminbar').length > 0 ) targetOffset = targetOffset - 50;
         else  targetOffset = targetOffset - 20;
         jQuery('html,body').animate({scrollTop: targetOffset}, 500);
    }

    //Admin function s for checking all checkbos in one time
    function setCheckBoxInTable(el_stutus, el_class){
         jQuery('.'+el_class).attr('checked', el_stutus);

         if ( el_stutus ) {
             jQuery('.'+el_class).parent().parent().addClass('row_selected_color');
         } else {
             jQuery('.'+el_class).parent().parent().removeClass('row_selected_color');
         }
    }


    // Set selected days at calendar as UnAvailable
    function setUnavailableSelectedDays( bk_type ){
        var sel_dates = jQuery('#calendar_booking'+bk_type).datepick('getDate');
        var class_day2;
        for( var i =0; i <sel_dates.length; i++) {
          class_day2 = (sel_dates[i].getMonth()+1) + '-' + sel_dates[i].getDate() + '-' + sel_dates[i].getFullYear();
          date_approved[ bk_type ][ class_day2 ] = [ (sel_dates[i].getMonth()+1) ,  sel_dates[i].getDate(),  sel_dates[i].getFullYear(),0,0,0];
          jQuery('#calendar_booking'+bk_type+' td.cal4date-'+class_day2).html(sel_dates[i].getDate());
          // jQuery('#calendar_booking'+bk_type).datepick('refresh');
        }
    }


    // Aftre reservation action is done
    function setReservedSelectedDates( bk_type ){

        if (document.getElementById('calendar_booking'+bk_type) === null )  {
            document.getElementById('submiting' + bk_type).innerHTML = '';
            document.getElementById("booking_form_div"+bk_type).style.display="none";
            makeScroll('#ajax_respond_insert'+bk_type);
            var is_pay_now = false;
            if ( document.getElementById('paypalbooking_form'+bk_type) != null )
                if ( document.getElementById('paypalbooking_form'+bk_type).innerHTML != '' ) is_pay_now = true;
            if ( (! is_pay_now) || ( is_booking_without_payment == true ) )             
                if (type_of_thank_you_message == 'page') {      // Page
                                //location.href= thank_you_page_URL;
                                timeoutID_of_thank_you_page = setTimeout(function ( ) {location.href= thank_you_page_URL;} ,1000);
                } else {                                        // Message
                                document.getElementById('submiting'+bk_type).innerHTML = '<div class=\"submiting_content wpdev-help-message alert alert-success\" >'+new_booking_title+'</div>';
                                jQuery('.submiting_content').fadeOut( new_booking_title_time );
                }
        } else {

                setUnavailableSelectedDays(bk_type);                            // Set days as unavailable
                document.getElementById('date_booking'+bk_type).value = '';     // Set textarea date booking to ''
                document.getElementById('calendar_booking'+bk_type).style.display= 'none';
                jQuery('.block_hints').css( {'display' : 'none'} );

                var is_admin = 0;
                if (location.href.indexOf('booking.php') != -1 ) {is_admin = 1;}
                if (is_admin == 0) {
                    // Get calendar from the html and insert it before form div, which will hide after btn click
                    jQuery('#calendar_booking'+bk_type).insertBefore("#booking_form_div"+bk_type);
                    document.getElementById("booking_form_div"+bk_type).style.display="none";
                    
                    jQuery('#calendar_booking'+bk_type).hide();
                    makeScroll('#ajax_respond_insert'+bk_type);

                    var is_pay_now = false;

                    if ( document.getElementById('paypalbooking_form'+bk_type) != null )
                        if ( document.getElementById('paypalbooking_form'+bk_type).innerHTML != '' ) is_pay_now = true;

                if ( (! is_pay_now) || ( is_booking_without_payment == true ) ) {
                        if (type_of_thank_you_message == 'page') {      // Page
                            // thank_you_page_URL
                           // location.href= thank_you_page_URL;
                            timeoutID_of_thank_you_page = setTimeout(function ( ) {location.href= thank_you_page_URL;} ,1000);
                        } else {                                        // Message
                            //new_booking_title;
                            //new_booking_title_time;
                            document.getElementById('submiting'+bk_type).innerHTML = '<div class=\"submiting_content wpdev-help-message alert alert-success\" >'+new_booking_title+'</div>';
                            jQuery('.submiting_content').fadeOut( new_booking_title_time );
                            //setTimeout(function ( ) {location.reload(true);} ,new_booking_title_time);
                        }
                    }

                } else {
                    setTimeout(function ( ) {location.reload(true);} ,1000);
                }
        }
    }


        function showErrorMessage( element , errorMessage) {


            makeScroll( element );

            jQuery("[name='"+ element.name +"']")
                    .fadeOut( 350 ).fadeIn( 300 )
                    .fadeOut( 350 ).fadeIn( 400 )
                    .fadeOut( 350 ).fadeIn( 300 )
                    .fadeOut( 350 ).fadeIn( 400 )
                    .animate( {opacity: 1}, 4000 )
            ;  // mark red border
            if (jQuery("[name='"+ element.name +"']").attr('type') == "checkbox") {
                jQuery("[name='"+ element.name +"']").parent()
                        .after('<span class="wpdev-help-message alert">'+ errorMessage +'</span>'); // Show message

            } else {
                jQuery("[name='"+ element.name +"']")
                        .after('<span class="wpdev-help-message alert">'+ errorMessage +'</span>'); // Show message
            }
            jQuery(".wpdev-help-message")
                    .css( {'padding' : '5px 5px 4px', 'margin' : '2px', 'vertical-align': 'middle' } );
            jQuery(".widget_wpdev_booking .booking_form .wpdev-help-message")
                    .css( {'vertical-align': 'sub' } ) ;
            jQuery(".wpdev-help-message")
                    .animate( {opacity: 1}, 10000 )
                    .fadeOut( 2000 );   
            element.focus();    // make focus to elemnt
            return;

        }

    // Check fields at form and then send request
    function mybooking_submit( submit_form , bk_type, wpdev_active_locale){


        var count = submit_form.elements.length;
        var formdata = '';
        var inp_value;
        var element;
        var el_type;


        // Serialize form here
        for (i=0; i<count; i++)   {
            element = submit_form.elements[i];
            
            if ( (element.type !=='button') && (element.type !=='hidden') && ( element.name !== ('date_booking' + bk_type) )   ) {           // Skip buttons and hidden element - type


                // Get Element Value
                if ( element.type == 'checkbox' ){
                    
                    if (element.value == '') {
                        inp_value = element.checked;
                    } else {
                        if (element.checked) inp_value = element.value;
                        else inp_value = '';
                    }
                    
                } else if ( element.type == 'radio' ) {
 
                    if (element.checked) inp_value = element.value;
                    else continue;

                } else {
                    inp_value = element.value;
                }                      

                // Get value in selectbox of multiple selection
                if (element.type =='select-multiple') {
                    inp_value = jQuery('[name="'+element.name+'"]').val() ;
                    if (( inp_value == null ) || (inp_value.toString() == '' ))
                        inp_value='';
                }


                // Recheck for max num. available visitors selection
                if ( element.name == ('visitors'+bk_type) )
                    if( typeof( is_max_visitors_selection_more_than_available ) == 'function' )
                        if ( is_max_visitors_selection_more_than_available( bk_type, inp_value, element ) )
                            return;



                // Validation Check --- Requred fields
                if ( element.className.indexOf('wpdev-validates-as-required') !== -1 ){             
                    if  ((element.type =='checkbox') && ( element.checked === false))      {showErrorMessage( element , message_verif_requred_for_check_box);return;}
                    if  ((element.type !='checkbox') && ( inp_value === ''))   {showErrorMessage( element , message_verif_requred);return;}
                }

                // Validation Check --- Email correct filling field
                if ( element.className.indexOf('wpdev-validates-as-email') !== -1 ){                
                    var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                    if ( inp_value != '' )
                        if(reg.test(inp_value) == false) {showErrorMessage( element , message_verif_emeil);return;}
                }


                // Get Form Data
                if ( element.name !== ('captcha_input' + bk_type) ) {
                    if (formdata !=='') formdata +=  '~';                                                // next field element

                    el_type = element.type;
                    if ( element.className.indexOf('wpdev-validates-as-email') !== -1 )  el_type='email';
                    if ( element.className.indexOf('wpdev-validates-as-coupon') !== -1 ) el_type='coupon';
                    
                    inp_value = inp_value + '';
                    inp_value = inp_value.replace(new RegExp("\\^",'g'), '&#94;'); // replace registered characters
                    inp_value = inp_value.replace(new RegExp("~",'g'), '&#126;'); // replace registered characters

                    inp_value = inp_value.replace(/"/g, '&#34;'); // replace double quot
                    inp_value = inp_value.replace(/'/g, '&#39;'); // replace single quot

                    formdata += el_type + '^' + element.name + '^' + inp_value ;                    // element attr
                }
            }

        }  // End Fields Loop

        // Recheck Times
        if( typeof( is_this_time_selections_not_available ) == 'function' )
            if ( is_this_time_selections_not_available( bk_type, submit_form.elements ) )
                return;

        if (document.getElementById('calendar_booking'+bk_type) != null ) {
        var inst = jQuery.datepick._getInst(document.getElementById('calendar_booking'+bk_type));
        if (bk_days_selection_mode == 'dynamic')
            if (bk_2clicks_mode_days_min != undefined)
                if (inst.dates.length < bk_2clicks_mode_days_min ) {
                    alert(message_verif_selectdts);
                    return;
                }
        }
        //Show message if no selected days
        if (document.getElementById('date_booking' + bk_type).value == '')  {



            if ( document.getElementById('additional_calendars' + bk_type) != null ) { // Checking according additional calendars.

                var id_additional_str = document.getElementById('additional_calendars' + bk_type).value; //Loop have to be here based on , sign
                var id_additional_arr = id_additional_str.split(',');
                var is_all_additional_days_unselected = true;
                for (var ia=0;ia<id_additional_arr.length;ia++) {
                    if (document.getElementById('date_booking' + id_additional_arr[ia] ).value != '' ) {
                        is_all_additional_days_unselected = false;
                    }
                }

                if (is_all_additional_days_unselected) {
                    alert(message_verif_selectdts);
                    return;
                }

            } else {
                alert(message_verif_selectdts);
                return;
            }
        }


        // Cpatch  verify
        var captcha = document.getElementById('wpdev_captcha_challenge_' + bk_type);
        
        //Disable Submit button
        jQuery('#booking_form_div' + bk_type + ' input[type=button]').prop("disabled", true);
        if (captcha != null)  form_submit_send( bk_type, formdata, captcha.value, document.getElementById('captcha_input' + bk_type).value ,wpdev_active_locale);
        else                  form_submit_send( bk_type, formdata, '',            '' ,                                                      wpdev_active_locale);
        return;
    }

    // Gathering params for sending Ajax request and then send it
    function form_submit_send( bk_type, formdata, captcha_chalange, user_captcha ,wpdev_active_locale){

            document.getElementById('submiting' + bk_type).innerHTML = '<div style="height:20px;width:100%;text-align:center;margin:15px auto;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//div>';

            var my_booking_form = '';
            var my_booking_hash = '';
            if (document.getElementById('booking_form_type' + bk_type) != undefined)
                my_booking_form =document.getElementById('booking_form_type' + bk_type).value;

            if (wpdev_bk_edit_id_hash != '') my_booking_hash = wpdev_bk_edit_id_hash;

            var is_send_emeils= jQuery('#is_send_email_for_new_booking');
            if (is_send_emeils.length == 0 ) {
                is_send_emeils = 1;
            } else {
                is_send_emeils = is_send_emeils.attr('checked' );
                if (is_send_emeils == undefined) {is_send_emeils = 0 ;}
                if (is_send_emeils) is_send_emeils = 1;
                else                is_send_emeils = 0;
            }
            send_ajax_submit(bk_type,formdata,captcha_chalange,user_captcha,is_send_emeils,my_booking_hash,my_booking_form,wpdev_active_locale   ); // Ajax sending request

            var formdata_additional_arr;
            var formdata_additional;
            var my_form_field;
            var id_additional;
            var id_additional_str;
            var id_additional_arr;
            if (document.getElementById('additional_calendars' + bk_type) != null ) {
                
                id_additional_str = document.getElementById('additional_calendars' + bk_type).value; //Loop have to be here based on , sign
                id_additional_arr = id_additional_str.split(',');

                for (var ia=0;ia<id_additional_arr.length;ia++) {
                    formdata_additional_arr = formdata;
                    formdata_additional = '';
                    id_additional = id_additional_arr[ia];

                   
                    formdata_additional_arr = formdata_additional_arr.split('~');
                    for (var j=0;j<formdata_additional_arr.length;j++) {
                        my_form_field = formdata_additional_arr[j].split('^');
                        if (formdata_additional !=='') formdata_additional +=  '~';

                        if (my_form_field[1].substr( (my_form_field[1].length -2),2)=='[]')
                          my_form_field[1] = my_form_field[1].substr(0, (my_form_field[1].length - (''+bk_type).length ) - 2 ) + id_additional + '[]';
                        else
                          my_form_field[1] = my_form_field[1].substr(0, (my_form_field[1].length - (''+bk_type).length ) ) + id_additional ;


                        formdata_additional += my_form_field[0] + '^' + my_form_field[1] + '^' + my_form_field[2];
                    }


                    if (document.getElementById('date_booking' + id_additional).value != '' ) {
                        setUnavailableSelectedDays(id_additional);                                              // Set selected days unavailable in this calendar
                        jQuery('#calendar_booking'+id_additional).insertBefore("#booking_form_div"+bk_type);    // Insert calendar before form to do not hide it
                        if (document.getElementById('paypalbooking_form'+id_additional) != null)
                            jQuery('#paypalbooking_form'+id_additional).insertBefore("#booking_form_div"+bk_type);    // Insert payment form to do not hide it
                        else {
                            jQuery("#booking_form_div"+bk_type).append('<div id="paypalbooking_form'+id_additional+'" ></div>');
                            jQuery("#booking_form_div"+bk_type).append('<div id="ajax_respond_insert'+id_additional+'" ></div>');
                        }
                        send_ajax_submit( id_additional ,formdata_additional,captcha_chalange,user_captcha,is_send_emeils,my_booking_hash,my_booking_form ,wpdev_active_locale  );
                    }
                }
            }
    }

    //<![CDATA[
    function send_ajax_submit(bk_type,formdata,captcha_chalange,user_captcha,is_send_emeils,my_booking_hash,my_booking_form  ,wpdev_active_locale ) {
            // Ajax POST here

            var my_bk_res = bk_type;
            if ( document.getElementById('bk_type' + bk_type) != null ) my_bk_res = document.getElementById('bk_type' + bk_type).value;

            jQuery.ajax({                                           // Start Ajax Sending
                url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
                type:'POST',
                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond_insert' + bk_type).html( data ) ;},
                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                // beforeSend: someFunction,
                data:{
                    ajax_action : 'INSERT_INTO_TABLE',
                    bktype: my_bk_res ,
                    dates: document.getElementById('date_booking' + bk_type).value ,
                    form: formdata,
                    captcha_chalange:captcha_chalange,
                    captcha_user_input: user_captcha,
                    is_send_emeils : is_send_emeils,
                    my_booking_hash:my_booking_hash,
                    booking_form_type:my_booking_form,
                    wpdev_active_locale:wpdev_active_locale,
                    wpbc_nonce: document.getElementById('wpbc_nonce' + bk_type).value 
                }
            });
    }
    //]]>


    function wpdevExclusiveCheckbox(element){
        
        jQuery('[name="'+element.name+'"]').prop("checked", false);             // Uncheck  all checkboxes with  this name

        element.checked = true;
    }

    // Set selectbox with multiple selections - Exclusive
    function wpdevExclusiveSelectbox(element){
        
        // Get all selected elements.
        var selectedOptions = jQuery.find('[name="'+element.name+'"] option:selected');
        
        // Check if we are have more than 1 selection
        if ( selectedOptions.length > 1 ) {
            
            var ind = selectedOptions[0].index;                                             // Get index of the first  selected element
            jQuery('[name="'+element.name+'"] option').prop("selected", false);             // Uncheck  all checkboxes with  this name
            jQuery('[name="'+element.name+'"] option:eq('+ind+')').prop("selected", true);  // Set the first element selected
        }        
    }


    // Prepare to show tooltips
    function prepare_tooltip(myParam){   
           var tooltip_day_class_4_show = " .timespartly";
           if (is_show_availability_in_tooltips) {
               if (  wpdev_in_array( parent_booking_resources , myParam ) )
                    tooltip_day_class_4_show = " .datepick-days-cell";//" .datepick-days-cell a";  // each day
           }
           if (is_show_cost_in_tooltips) {
                tooltip_day_class_4_show =  " .datepick-days-cell";//" .datepick-days-cell a";  // each day
           }

          // Show tooltip at each day if time availability filter is set
          if(typeof( global_avalaibility_times[myParam]) != "undefined") {
              if (global_avalaibility_times[myParam].length>0)  tooltip_day_class_4_show = " .datepick-days-cell";  // each day
          }
 

        jQuery("#calendar_booking" + myParam + tooltip_day_class_4_show ).popover( {
            placement: 'top'
          , delay: { show: 500, hide: 1 }
          , content: ''
          , template: '<div class="wpdevbk popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>'
        });

    }

    // Hint labels inside of input boxes
    jQuery(document).ready( function(){
        
            jQuery('div.inside_hint').click(function(){
                    jQuery(this).css('visibility', 'hidden').siblings('.has-inside-hint').focus();
            });

            jQuery('input.has-inside-hint').blur(function() {
                if ( this.value == '' )
                    jQuery(this).siblings('.inside_hint').css('visibility', '');
            }).focus(function(){
                    jQuery(this).siblings('.inside_hint').css('visibility', 'hidden');
            });
            
            jQuery('.booking_form_div input[type=button]').prop("disabled", false);
    });



function openModalWindow(content_ID){
    //alert('!!!' + content);
    jQuery('.modal_content_text').attr('style','display:none;');
    document.getElementById( content_ID ).style.display = 'block';
    var buttons = {};//{ "Ok": wpdev_bk_dialog_close };
    jQuery("#wpdev-bk-dialog").dialog( {
            autoOpen: false,
            width: 700,
            height: 330,
            buttons:buttons,
            draggable:false,
            hide: 'slide',
            resizable: false,
            modal: true,
            title: '<img src="'+wpdev_bk_plugin_url+ '/img/calendar-16x16.png" align="middle" style="margin-top:1px;"> Booking Calendar'
    });
    jQuery("#wpdev-bk-dialog").dialog("open");
}

function wpdev_bk_dialog_close(){
    jQuery("#wpdev-bk-dialog").dialog("close");
}

function wpdev_togle_box(boxid){
    if ( jQuery( '#' + boxid ).hasClass('closed') ) jQuery('#' + boxid).removeClass('closed');
    else                                            jQuery('#' + boxid).addClass('closed');
}



//<![CDATA[
function verify_window_opening(us_id,  window_id ){

        var is_closed = 0;

        if (jQuery('#' + window_id ).hasClass('closed') == true){
            jQuery('#' + window_id ).removeClass('closed');
        } else {
            jQuery('#' + window_id ).addClass('closed');
            is_closed = 1;
        }


        jQuery.ajax({                                           // Start Ajax Sending
                url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
                type:'POST',
                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                // beforeSend: someFunction,
                data:{
                    ajax_action : 'USER_SAVE_WINDOW_STATE',
                    user_id: us_id ,
                    window: window_id,
                    is_closed: is_closed,
                    wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 
                }
        });

}
//]]>

 function wpdev_in_array (array_here, p_val) {
	for(var i = 0, l = array_here.length; i < l; i++) {
		if(array_here[i] == p_val) {
			return true;
		}
	}
	return false;
}


function days_between(date1, date2) {

    // The number of milliseconds in one day
    var ONE_DAY = 1000 * 60 * 60 * 24;

    // Convert both dates to milliseconds
    var date1_ms = date1.getTime();
    var date2_ms = date2.getTime();

    // Calculate the difference in milliseconds
    var difference_ms =  date1_ms - date2_ms;

    // Convert back to days and return
    return Math.round(difference_ms/ONE_DAY);

}

function showwidedates_at_admin_side(){
                                 jQuery('.short_dates_view').addClass('hide_dates_view');
                                jQuery('.short_dates_view').removeClass('show_dates_view');
                                jQuery('.wide_dates_view').addClass('show_dates_view');
                                jQuery('.wide_dates_view').removeClass('hide_dates_view');
                                jQuery('#showwidedates').addClass('hide_dates_view');

                                jQuery('.showwidedates').addClass('hide_dates_view');
                                jQuery('.showshortdates').addClass('show_dates_view');
                                jQuery('.showshortdates').removeClass('hide_dates_view');
                                jQuery('.showwidedates').removeClass('show_dates_view');
}

function showshortdates_at_admin_side(){
                                jQuery('.wide_dates_view').addClass('hide_dates_view');
                                jQuery('.wide_dates_view').removeClass('show_dates_view');
                                jQuery('.short_dates_view').addClass('show_dates_view');
                                jQuery('.short_dates_view').removeClass('hide_dates_view');

                                jQuery('.showshortdates').addClass('hide_dates_view');
                                jQuery('.showwidedates').addClass('show_dates_view');
                                jQuery('.showwidedates').removeClass('hide_dates_view');
                                jQuery('.showshortdates').removeClass('show_dates_view');

}



function daysInMonth(month,year) {
    var m = [31,28,31,30,31,30,31,31,30,31,30,31];
    if (month != 2) return m[month - 1];
    if (year%4 != 0) return m[1];
    if (year%100 == 0 && year%400 != 0) return m[1];
    return m[1] + 1;
}


function setSelectBoxByValue(el_id, el_value) {

    for (var i=0; i < document.getElementById(el_id).length; i++) {
        if (document.getElementById(el_id)[i].value == el_value) {
            document.getElementById(el_id)[i].selected = true;
        }
    }
}


//<![CDATA[
function save_bk_listing_filter(us_id,  filter_name, filter_value ){

        var wpdev_ajax_path     = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename;
        var ajax_type_action    = 'SAVE_BK_LISTING_FILTER';
        var ajax_bk_message     = 'Saving...';

        document.getElementById('ajax_working').innerHTML =
        '<div class="updated ajax_message" id="ajax_message">\n\
            <div style="float:left;">'+ajax_bk_message+'</div> \n\
            <div class="wpbc_spin_loader">\n\
                   <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
            </div>\n\
        </div>';

        jQuery.ajax({
                url: wpdev_ajax_path,
                type:'POST',
                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                data:{
                    ajax_action : ajax_type_action,
                    user_id: us_id ,
                    filter_name: filter_name ,
                    filter_value: filter_value,
                    wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 

                }
        });
}
//]]>



//<![CDATA[
function delete_bk_listing_filter(us_id,  filter_name ){

        var wpdev_ajax_path     = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename;
        var ajax_type_action    = 'DELETE_BK_LISTING_FILTER';
        var ajax_bk_message     = 'Deleting...';

        document.getElementById('ajax_working').innerHTML =
        '<div class="updated ajax_message" id="ajax_message">\n\
            <div style="float:left;">'+ajax_bk_message+'</div> \n\
            <div class="wpbc_spin_loader">\n\
                   <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
            </div>\n\
        </div>';

        jQuery.ajax({
                url: wpdev_ajax_path,
                type:'POST',
                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                data:{
                    ajax_action : ajax_type_action,
                    user_id: us_id ,
                    filter_name: filter_name,
                    wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 
                }
        });
}
//]]>