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

    recaptchaMultipleFieldRender = function() {
        for (var i = 0; i < _recaptchaMultipleFields.length; i++) {
            var field = $('#' + _recaptchaMultipleFields[i]);

            if (field.data("widgetid") == null) {
                //For the invisible captcha we need to setup some callback listeners
                if (field.data('size') == 'invisible') {
                    var form = $('#' + field.data('form'));

                    form.on('submit', function(e) {
                        e.preventDefault();

                        let widgetID = form.find('.g-recaptcha').data('widgetid');
                        grecaptcha.execute(widgetID);
                    });

                    window[_recaptchaMultipleFields[i]] = function() {
                        return new Promise(function(resolve, reject) {
                            form.submit();
                            resolve();
                        });
                    };
                }

                var options = {
                    'sitekey': field.data('sitekey'),
                    'theme': field.data('theme'),
                    'type': field.data('type'),
                    'size': field.data('size'),
                    'badge': field.data('badge'),
                    'callback': (field.data('callback') ? field.data('callback') : _recaptchaMultipleFields[i])
                };

                var widget_id = grecaptcha.render(field[0], options);
                field.data("widgetid", widget_id);
            }
        }
    }
})(jQuery);
