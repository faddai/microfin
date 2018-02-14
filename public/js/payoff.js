/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 11/06/2017
 * Time: 20:27
 */

(function ($) {
    'use strict';

    var selectors = {
        'declineLoanPayoffModal': '#decline-payoff-loan-modal',
        'loanPayoffModal': '#payoff-loan-modal',
        'loanPayoffModalContentIntro': '#payoff-intro',
        'declinePayoffButton': '#decline-payoff-loan-modal button[type="submit"]',
        'reasonForDecliningPayoff': '#decline-reason',
        'principal': 'input#principal',
        'interest': 'input#interest',
        'fees': 'input#fees',
        'penalty': 'input#penalty',
        'remarks': 'textarea#remarks',
        'maturityTd': 'td#maturity',
        'loanNumberTd': 'td#loan-number',
        'clientNameTd': 'td#client',
        'creatorTd': 'td#creator',
        'createdAtTd': 'td#created-at',
        'payoffAmount': 'span#payoff-amount',
        'loanBalance': 'span#outstanding-loan-amount',
    };

    var createOrApproveLoanPayoff = function (evt) {
        var payoff = $(evt.relatedTarget).data(),
            $modal = $(this);

        if (payoff.id) {
            $modal.find('table').removeClass('hide');
            $modal.find(selectors.principal).val(payoff.principal);
            $modal.find(selectors.interest).val(payoff.interest);
            $modal.find(selectors.fees).val(payoff.fees);
            $modal.find(selectors.penalty).val(payoff.penalty);
            $modal.find(selectors.remarks).val(payoff.remarks);

            $modal.find(selectors.maturityTd).text(payoff.maturity);
            $modal.find(selectors.loanNumberTd).text(payoff.loanNumber);
            $modal.find(selectors.clientNameTd).text(payoff.client);
            $modal.find(selectors.creatorTd).text(payoff.creator);
            $modal.find(selectors.createdAtTd).text(payoff.createdAt);

            $modal.find('button[type="submit"]').text('Approve Payoff');
            $modal.find(selectors.loanBalance).text(payoff.loanBalance);
            $modal.find(selectors.payoffAmount).text(payoff.amount);

            $modal.find('form').attr('action', payoff.action);
        }
    };

    var declineLoanPayoff = function (evt) {
        var payoff = $(evt.relatedTarget).data(),
            $modal = $(this);

        // toggle decline button
        $(selectors.reasonForDecliningPayoff).on('keyup', toggleDeclinePayoffButton);

        $modal.find('form').attr('action', payoff.action);
    };

    var calculateLoanPayoffAmount = function (evt) {
        var principal = parseFloat($(selectors.principal).val().replace(',', '')) || 0,
            interest = parseFloat($(selectors.interest).val().replace(',', '')) || 0,
            fees = parseFloat($(selectors.fees).val().replace(',', '')) || 0,
            total = interest + principal + fees;

        $(selectors.payoffAmount).text(microfin.formatNumber(total));
    };

    var toggleDeclinePayoffButton = function () {

        var chars = $(selectors.reasonForDecliningPayoff).val().length;

        if (chars && chars > 10) {
            $(selectors.declinePayoffButton).prop('disabled', false);
        } else {
            $(selectors.declinePayoffButton).prop('disabled', true);
        }
    };

    $(document.body).on('show.bs.modal', selectors.loanPayoffModal, createOrApproveLoanPayoff);
    $(document.body).on('show.bs.modal', selectors.declineLoanPayoffModal, declineLoanPayoff);
    $('input#principal, input#interest, input#fees').on('keyup', calculateLoanPayoffAmount);

}(window.jQuery));