/**
 * Created by faddai on 19/11/2016.
 */
(function ($) {
    'use strict';

    (function () {

        var selectors = {
            loanAmountInput: 'input[name="amount"]',
            formattedAmount: '#formatted-amount'
        };

        var formatLoanAmountForHumans = function () {
            var loanAmount = $(selectors.loanAmountInput).val(),
                currency   = $(selectors.formattedAmount).data('currency');

            $(selectors.formattedAmount).val(currency + microfin.formatNumber(loanAmount));
        };

        $(document.body).on('keyup change', selectors.loanAmountInput, formatLoanAmountForHumans);
        $(document).ready(formatLoanAmountForHumans);
    }());

    // Guarantor
    (function () {
        var prefix = ".guarantor-",
            selectors = {
                container: prefix + 'container',
                template: '#guarantor-template',
                addCloneBtn: '#add-guarantor',
                removeCloneBtn: '.remove-guarantor',
                name: prefix + 'name',
                relationship: prefix + 'relationship',
                workPhone: prefix + 'tel-work',
                personalPhone: prefix + 'tel-mobile',
                yearsKnown: prefix + 'years-known',
                employer: prefix + 'employer',
                jobRole: prefix + 'job-role'
            };

        var addClone = function() {
            var clone = $($(selectors.template).html()),
                lastIndex = parseInt($(selectors.container + ':last').data('row-id')),
                cloneIndex = !isNaN(lastIndex) ? lastIndex + 1 : 1,
                inputNamePrefix = "guarantors["+cloneIndex+"]";

            clone.attr('data-row-id', cloneIndex);
            clone.find(selectors.name).attr('name', inputNamePrefix + "[name]");
            clone.find(selectors.relationship).attr('name', inputNamePrefix + "[relationship]");
            clone.find(selectors.workPhone).attr('name', inputNamePrefix + "[work_phone]");
            clone.find(selectors.personalPhone).attr('name', inputNamePrefix + "[personal_phone]");
            clone.find(selectors.yearsKnown).attr('name', inputNamePrefix + "[years_known]");
            clone.find(selectors.employer).attr('name', inputNamePrefix + "[employer]");
            clone.find(selectors.jobRole).attr('name', inputNamePrefix + "[job_role]");

            $(selectors.container + ':last').after(clone);
        };

        var removeClone = function() {
            $(this).parents(selectors.container + ':first').remove();
        };

        $(document.body).on('click', selectors.addCloneBtn, addClone);
        $(document.body).on('click', selectors.removeCloneBtn, removeClone);
    }());

    // Collateral
    (function () {

        var prefix = ".collateral-",
            selectors = {
                container: prefix + 'container',
                template: '#collateral-template',
                addCloneBtn: '#add-collateral',
                removeCloneBtn: '.remove-collateral',
                label: prefix + 'title',
                value: prefix + 'value'
            };

        var addCollateralClone = function() {
            var clone = $($(selectors.template).html()),
                lastIndex = parseInt($(selectors.container + ':last').data('row-id')),
                cloneIndex = !isNaN(lastIndex) ? lastIndex + 1 : 1,
                inputNamePrefix = "collaterals["+cloneIndex+"]";

            clone.attr('data-row-id', cloneIndex);

            clone.find(selectors.label).attr('name', inputNamePrefix + "[label]");
            clone.find(selectors.value).attr('name', inputNamePrefix + "[market_value]");

            $(selectors.container + ':last').after(clone);
        };

        var removeCollateralClone = function() {
            $(this).parents(selectors.container + ':first').remove();
        };

        $(document.body).on('click', selectors.addCloneBtn, addCollateralClone);
        $(document.body).on('click', selectors.removeCloneBtn, removeCollateralClone);
    }());

    // Fees
    (function () {

        var selectors = {
            removeButton: '.js-remove-fee',
            rateInputs: 'tr input.js-fee',
            fixedRateInputs: 'tr input.js-fee-fixed',
            loanAmountInput: '#amount',
            computedFeeAmount: '.js-computed-fee-amount',
            computedFeeTotalAmount: '#js-computed-fee-total-amount'
        };

        var remove = function () {
            $(this).parents('tr').remove();
            $(selectors.computedFeeTotalAmount).text(microfin.formatNumber(0));
            calculateFees();
            return false;
        };

        var calculateFees = function () {
            var totalFees = 0, loanAmount = parseFloat($(selectors.loanAmountInput).val() || 0);

            // calculate and sum all percentage fees
            $(selectors.rateInputs).length && $(selectors.rateInputs).each(function (index, rate) {
                var $rate = $(rate);
                var fee = parseFloat(($rate.val() / 100) * loanAmount);
                totalFees += parseFloat(fee);

                $rate.parents('tr').find(selectors.computedFeeAmount).text(microfin.formatNumber(fee));
            });

            // sum all fixed fees
            $(selectors.fixedRateInputs).length && $(selectors.fixedRateInputs).each(function (index, rate) {
                totalFees += parseFloat($(rate).val());
                $(rate).parents('tr').find(selectors.computedFeeAmount).text(microfin.formatNumber($(rate).val()));
            });

            $(selectors.computedFeeTotalAmount).text(microfin.formatNumber(totalFees));
        };

        $(document.body).on('click', selectors.removeButton, remove);
        $(document.body).on('keyup', [selectors.loanAmountInput, selectors.rateInputs], calculateFees);
        $(document).ready(calculateFees);
    }());
})(window.jQuery);