(function( $ ) {
    'use strict';

    /**
     * When the window is loaded:
     **/
    /*$( window ).load(function() {

    });*/

    /**
     * When the DOM is ready start
     **/
    $(function() {

        var irisOptions = {
            defaultColor: false,
            change: function(event, ui){},
            clear: function() {},
            hide: true,
            palettes: ['#3b639b','#00775f','#a88300','#37dd00','#d587ff']
        };

        $('#_sn_wp_notice_color').wpColorPicker(irisOptions);

        $(document).on('click', '.sn-wp-toast-sample', function(e) {
            e.preventDefault();
            var $this = $(this);
            var notice_type = $this.data('notice-type');
            var notice_color = $this.data('notice-color');
            // console.log(notice_color);
            $('ul.toast-samples > li > div').removeClass('active');
            $this.addClass('active');
            $('#_sn_wp_notice_type input[name="_sn_wp_notice_type"]').val(notice_type);
            $('#_sn_wp_notice_color').iris('color', notice_color);
        });

        $(document).on('click', '.sn-wp-preview-button', function(e) {
            e.preventDefault();
            toastr.clear();

            setTimeout(function() {
                let shortCutFunction = 'success';
                let post_ID = $('#post_ID').val();
                let title = $('#titlewrap').find('#title').val();
                let message = $('#_sn_wp_message').val();
                let notice_anime = $('input[name="_sn_wp_notice_anime"]:checked').val();
                let notice_type = $('#_sn_wp_notice_type input[name="_sn_wp_notice_type"]').val();
                let notice_color = $('input[name="_sn_wp_notice_color"]').val();
                let position = 'toast-container ' + $('#_sn_wp_position').val();
                let show_title = $('#_sn_wp_show_title').prop('checked');
                let auto_hide = $('#_sn_wp_auto_hide').prop('checked');
                let can_hide = $('#_sn_wp_can_hide').prop('checked');
                let duration = 'toast-container ' + $('#_sn_wp_duration').val();
                let duration_type = 'toast-container ' + $('#_sn_wp_duration_type').val();

                var class_name  = 'toast-custom-' + post_ID;

                var min_in_seconds = 60;

                if( duration_type === 'min' ) {
                    var seconds = duration * min_in_seconds;
                } else if( duration_type === 'hour' ) {
                    var seconds = duration * 60 * min_in_seconds;
                } else if( duration_type === 'day' ) {
                    var seconds = duration * 24 * 60 * min_in_seconds;
                }

                if( ! title.trim().length && ! message.trim().length ) {
                    return;
                }
                // console.log(notice_color);
                sn_wp_createClass(class_name,'background-color: ' + notice_color + ';');

                if(!show_title) {
                    title = null;
                }

                toastr.options = {
                    positionClass: position || 'toast-container toast-top-right',
                    closeButton: can_hide,
                    tapToDismiss: can_hide,
                    showDuration:300,
                    hideDuration:1000,
                    progressBar: auto_hide,
                    timeOut: auto_hide ? 5000 : 0,
                    extendedTimeOut: auto_hide ? 1000 : 0,
                    showMethod: 'fadeIn',
                    hideMethod: 'fadeOut',
                    closeMethod: 'fadeOut',
                    containerId: 'toast-container-' + post_ID,
                };

                var cookie_name = 'sn-wp-hide-' + post_ID;

                toastr.options.onHidden = function() {
                    if ( !Cookies.get(cookie_name) ) {
                        var date = new Date();
                        date.setTime(date.getTime() + (seconds * 1000)); //add seconds to current date-time 1s = 1000ms
                        Cookies.set(cookie_name, true, { expires: date, path: '' });
                    }
                }

                if(notice_anime === 'slide') {
                    toastr.options.showMethod = 'slideDown';
                    toastr.options.hideMethod = 'slideUp';
                    toastr.options.closeMethod = 'slideUp';
                }

                toastr[notice_type](message, title, {iconClass: 'toast-' + notice_type + ' ' + class_name}); // Wire up an event handler to a button in the toast, if it exists
                //$toastlast = $toast;
            }, 1500);
        });

        $(document).on('click', '.sn-wp-copy-code', function(e) {
            e.preventDefault();
            var $this = $(this);
            let element = $this.siblings('code');
            copyToClipboard(element);
            element.css({'background-color': 'yellow'});
            element.animate({'background-color': '#f0f0f1'}, 700 );
        });

        function copyToClipboard(element) {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($(element).text()).select();
            document.execCommand("copy");
            $temp.remove();
        }

        function sn_wp_createClass(class_name,rules){
            if( $('#'+class_name).length !== 0 ) {
                $('#'+class_name).remove();
            }

            $('<style>')
                .prop('type', 'text/css')
                .prop('id', class_name)
                .html( '.' + class_name + ' {' + rules + '}' )
                .appendTo('head');

        }

    });

})( jQuery );