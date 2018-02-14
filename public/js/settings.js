/**
 * Created by faddai on 14/11/2016.
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
        // zones
        $('#edit-zone-modal').on('show.bs.modal', function (e) {
            var $button = $(e.relatedTarget),
                zone = $button.data();

            var $modal = $(this);
            var $form = $modal.find('form[name="edit-zone-form"]');

            var actionUrl = '/settings/zones/'+ zone.zoneId;

            if (! zone.zoneId) { // new zone is being added
                actionUrl = '/settings/zones/store';
                $modal.find('.modal-title').text('Add zone');
                $modal.find('input[name="_method"]').val('POST');
            }

            $modal.find('input[name="name"]').val(zone.zoneName);
            $form.attr('action', actionUrl);
        });

        $('#delete-zone-modal').on('show.bs.modal', function (e) {
            var $button = $(e.relatedTarget),
                zone = $button.data();

            var $modal = $(this);
            var $form = $modal.find('form[name="delete-zone-form"]');
            var actionUrl = '/settings/zones/'+ zone.zoneId;

            $form.attr('action', actionUrl);
        });

        // users
        $('#delete-user-modal').on('show.bs.modal', function (e) {
            var $button = $(e.relatedTarget),
                user = $button.data();

            var $modal = $(this);
            var actionUrl = '/users/'+ user.id +'/delete';

            var $form = $modal.find('#delete-user-form');

            $modal.find('.username').text(user.name);

            $form.attr('action', actionUrl);
        });

        $('.js-activate-user').click(function (e) {
            e.preventDefault();

            var formAction = $(this).attr('href');

            var $form = $(document).find('#js-activate-user-form');
            $form.attr('action', formAction);

            $form.submit();
        });

    });

    /**
     * loan fees setup
     */
    (function () {
        var selectors = {
            feeName: 'input[name="name"]',
            feeRate: 'input[name="rate"]',
            feeType: 'select#fee-type',
            feeIsPaidUpfront: 'input[name="is_paid_upfront"]',
            editFeeModal: '#edit-fee-modal',
            editFeeForm: 'form[name="edit-fee-form"]',
            editFeeFormMethod: 'input[name="_method"]',
            modalTitle: '.modal-title',
            deleteFeeModal: '#delete-fee-modal',
            deleteFeeButtonText: 'button span.fee-name',
            deleteFeeForm: 'form[name="delete-fee-form"]',
            receivableLedger: 'select[name="receivable_ledger_id"]',
            incomeLedger: 'select[name="income_ledger_id"]',
        };

        var addOrUpdate = function (event) {
            var fee = $(event.relatedTarget).data(),
                $modal = $(this);

            if (! fee.id) {
                $modal.find(selectors.modalTitle).text('Add fee');
                $modal.find(selectors.editFeeFormMethod).val('post');
            } else {
                $modal.find(selectors.editFeeFormMethod).val('put');
                $modal.find(selectors.modalTitle).text('Edit fee');
            }

            $modal.find(selectors.feeName).val(fee.name);
            $modal.find(selectors.feeRate).val(fee.rate);
            $modal.find(selectors.feeType).val(fee.type);
            $modal.find(selectors.receivableLedger).val(fee.receivableLedger);
            $modal.find(selectors.incomeLedger).val(fee.incomeLedger);
            $modal.find(selectors.feeIsPaidUpfront).prop('checked', fee.isPaidUpfront);
            $modal.find(selectors.editFeeForm).attr('action', fee.url);

            toggleFeeAmountInput(event, fee.type);
            toggleReceivableLedger(event, fee.isPaidUpfront);

            $modal.find('select[role="select2"]').trigger('change');
        };

        var remove = function (event) {
            var fee = $(event.relatedTarget).data(),
                $modal = $(this);

            $modal.find(selectors.deleteFeeButtonText).text(fee.name);
            $modal.find(selectors.deleteFeeForm).attr('action', fee.url);
        };

        var toggleFeeAmountInput = function (event, selectedFeeType) {
            selectedFeeType = selectedFeeType || $(this).val();

            // fee can be a fixed amount or a percentage of the loan amount
            if (selectedFeeType === 'percentage') {
                $(selectors.feeRate)
                    .parents('.fee-amount')
                    .find('label')
                    .text('Applicable Rate (%)')
                    .parent()
                    .removeClass('hide');
            } else if (selectedFeeType === 'fixed') {
                $(selectors.feeRate)
                    .parents('.fee-amount')
                    .find('label')
                    .text('Amount ('+ microfin.currency +')')
                    .parent()
                    .removeClass('hide');
            } else {
                $(selectors.feeRate).parents('.fee-amount').addClass('hide');
            }
        };

        // upfront fees don't require a receivable ledger
        var toggleReceivableLedger = function (event, checkedValue) {
            checkedValue = checkedValue || $(this).prop('checked');

            if (checkedValue) {
                $(selectors.receivableLedger).parents('.receivables').addClass('hide');
            } else {
                $(selectors.receivableLedger).parents('.receivables').removeClass('hide');
            }
        };

        $(document.body).on('show.bs.modal', selectors.editFeeModal, addOrUpdate);
        $(document.body).on('show.bs.modal', selectors.deleteFeeModal, remove);
        $(document.body).on('change', selectors.feeType, toggleFeeAmountInput);
        $(document.body).on('change', selectors.feeIsPaidUpfront, toggleReceivableLedger);
    }());


    /////////////////// Loan Products setup ///////////////////////////////
    (function () {

        var selectors = {
            editProductModal: '#edit-product-modal',
            removeProductModal: '#delete-product-modal',
            modalTitle: '.modal-title',
            editProductForm: 'form[name="edit-product-form"]',
            deleteProductForm: 'form[name="delete-product-form"]',
            formMethod: 'input[name="_method"]',
            productCode: 'input[name="code"]',
            productName: 'input[name="name"]',
            productDescription: 'textarea[name="description"]',
            productMinLoanAmount: 'input[name="min_loan_amount"]',
            productMaxLoanAmount: 'input[name="max_loan_amount"]',
            nameOfProductToRemove: 'button span.product-name',
            principalLedger: 'select[name="principal_ledger_id"]',
            interestLedger: 'select[name="interest_ledger_id"]',
            interestIncomeLedger: 'select[name="interest_income_ledger_id"]',
        };

        /**
         * Edit loan product information
         * @param event
         */
        var edit = function (event) {
            var product = $(event.relatedTarget).data(),
                $modal  = $(this);

            if (! product.id) {
                $modal.find(selectors.modalTitle).text('Add Loan Product');
                $(selectors.formMethod).val('post');
            } else {
                $modal.find(selectors.modalTitle).text('Edit Loan Product');
                $(selectors.formMethod).val('put');
            }

            $modal.find(selectors.productCode).val(product.code);
            $modal.find(selectors.productName).val(product.name);
            $modal.find(selectors.productDescription).val(product.description);
            $modal.find(selectors.productMinLoanAmount).val(product.min);
            $modal.find(selectors.productMaxLoanAmount).val(product.max);
            $modal.find(selectors.principalLedger).val(product.principalLedger);
            $modal.find(selectors.interestLedger).val(product.interestLedger);
            $modal.find(selectors.interestIncomeLedger).val(product.interestIncomeLedger);
            $modal.find(selectors.editProductForm).attr('action', product.url);

            $modal.find('select[role="select2"]').trigger('change');
        };

        /**
         * Delete loan product
         * @param event
         */
        var remove = function (event) {
            var product = $(event.relatedTarget).data(),
                $modal  = $(this);

            $modal.find(selectors.nameOfProductToRemove).text(product.name);
            $modal.find(selectors.deleteProductForm).attr('action', product.url);
        };

        $(document.body).on('shown.bs.modal', selectors.editProductModal, edit);
        $(document.body).on('show.bs.modal', selectors.removeProductModal, remove)

    }())
}(window.jQuery));