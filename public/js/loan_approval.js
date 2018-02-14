/**
 * Created by faddai on 19/11/2016.
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
        approval.init();
    });

    var approval = (function () {

        var submitForm = function (e) {
            e.preventDefault();
            $('form[name="loan-approval-form"]').attr('action', $(this).attr('href')).submit();
        };

        var registerListeners = function () {
            $(document.body).on('click', '.js-loan-approval', submitForm);
        };

        return {
            init: registerListeners
        }
    })();
})(window.jQuery);