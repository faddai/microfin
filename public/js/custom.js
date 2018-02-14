/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 16/10/2016
 * Time: 05:10
 */

(function ($) {
    'use strict';

    microfin.formatNumber = function (number, format) {
        return numeral(number).format(format || '0,0.00');
    };

    $(document).ready(function (e) {

        // form submission via javascript
        $(document).on('click', 'button.js-submit-form', function(e) {
            e.preventDefault();

            $('form[role="form"]').submit();
        });

        // select2
        var $select2 = $('select[role="select2"]');

        $select2 && $select2.each(function() {
            var placeholder = $(this).data('placeholder');

            $select2.select2({
                placeholder: placeholder,
                width: '100%'
            });
        });

        // datepicker
        $('[role="datepicker"]').each(function () {
            $(this).datepicker();
        }).on('changeDate', function () {
            $(this).datepicker('hide');
        });

        // tooltip
        $('[data-toggle="tooltip"]').tooltip()

        // date range picker
        var $startDateInput = $('input#start-date'), $endDateInput = $('input#end-date');

        var start = moment($startDateInput.val());
        var end = moment($endDateInput.val());

        var dateRangeSelectedCallback = function(start, end) {
            $('[role="daterangepicker"] span').html(start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY'));

            $startDateInput.val(start.format('YYYY-MM-DD'));
            $endDateInput.val(end.format('YYYY-MM-DD'));
        };

        $('[role="daterangepicker"]').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, dateRangeSelectedCallback);

        dateRangeSelectedCallback(start, end);

        // sticky and routable tabs
        $('.nav-tabs, .tabs').stickyTabs();
    })
}(window.jQuery));