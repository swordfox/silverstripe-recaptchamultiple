var _recaptchaMultipleFields = _recaptchaMultipleFields || [];
var recaptchaMultipleFormID;

(function($) {
    $(window).bind('load', function() {
        var _grecaptchafields = $('div.g-recaptcha');
        var i = 0;
        var reverse = false;

        _grecaptchafields.each(function() {
            _recaptchaMultipleFields.push($(this).attr('id'));

            if (!$(this).is(':visible') && i == 0) {
                reverse = true;
            }

            // Set the form id of the form being submitted
            $(this).closest('form').submit(function(event) {
                event.preventDefault();
                recaptchaMultipleFormID = $(this).attr('id');

                if (!$(this).hasClass('submitted')) {
                    $(this).addClass('submitted').submit();
                }
            });

            i++;
        });

        // reverse the order if there are multiple recaptcha widgets on the page & the 1st is hidden
        if (_recaptchaMultipleFields.length > 1 && reverse) {
            _recaptchaMultipleFields = _recaptchaMultipleFields.reverse();
        }

        $.getScript(('https:' == document.location.protocol ? 'https://www' : 'http://www') + '.google.com/recaptcha/api.js?render=explicit&hl=en&onload=recaptchaMultipleFieldRender');
    });
})(jQuery);


function recaptchaMultipleFieldRender() {

    var submitListener = function(e) {
        e.preventDefault();

        let widgetID = e.target.querySelectorAll('.g-recaptcha')[0].getAttribute('data-widgetid');
        grecaptcha.execute(widgetID);
    };

    for (var i = 0; i < _recaptchaMultipleFields.length; i++) {
        var field = document.getElementById(_recaptchaMultipleFields[i]);

        if (field.getAttribute("data-widgetid") == null) {


            //For the invisible captcha we need to setup some callback listeners
            if (field.getAttribute('data-size') == 'invisible') {
                var form = document.getElementById(field.getAttribute('data-form'));
                var superHandler = false;

                if (typeof jQuery != 'undefined' && typeof jQuery.fn.validate != 'undefined') {
                    var formValidator = jQuery(form).data('validator');
                    var superHandler = formValidator.settings.submitHandler;
                    formValidator.settings.submitHandler = function(form) {
                        grecaptcha.execute();
                    };
                } else {
                    if (form && form.addEventListener) {
                        form.addEventListener('submit', submitListener);
                    } else if (form && form.attachEvent) {
                        window.attachEvent('onsubmit', submitListener);
                    } else if (console.error) {
                        console.error('Could not attach event to the form');
                    }
                }

                window[_recaptchaMultipleFields[i]] = function() {
                    if (typeof jQuery != 'undefined' && typeof jQuery.fn.validate != 'undefined' && superHandler) {
                        superHandler(form);
                    } else {
                        var form = document.getElementById(recaptchaMultipleFormID);
                        form.submit();
                    }
                };
            }

            var options = {
                'sitekey': field.getAttribute('data-sitekey'),
                'theme': field.getAttribute('data-theme'),
                'type': field.getAttribute('data-type'),
                'size': field.getAttribute('data-size'),
                'badge': field.getAttribute('data-badge'),
                'callback': (field.getAttribute('data-callback') ? field.getAttribute('data-callback') : _recaptchaMultipleFields[i])
            };

            var widget_id = grecaptcha.render(field, options);
            field.setAttribute("data-widgetid", widget_id);
        }
    }
}
