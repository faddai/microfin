/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 24/03/2017
 * Time: 17:46
 */
(function ($) {
    'use strict';

    var selectors = {
        addEntryButton: 'button#js-add-entry',
        removeEntryButton: 'a.remove-entry-btn',
        addGLEntryModal: '#add-gl-entry-modal',
        postLedgerTransactionButton: '#add-gl-entry-form button[type="submit"]',
        selectedEntryType: 'select#type option:selected',
        drInput: 'input.dr',
        crInput: 'input.cr',
        entry: {
            ledger: 'select#ledger-id',
            value: 'input#value',
            narration: 'input#narration',
            counter: 'td.count'
        },
        glEntriesTable: 'table#gl-entries tbody',
        glEntriesTotals: 'table#gl-entries tbody #entries-totals',
    };

    var appendEntryToTable = function () {
        var value = $(selectors.entry.value).val(),
            selectedType = $(selectors.selectedEntryType).val(),
            selectedLedger = $(selectors.entry.ledger).select2('data')[0],
            narration = $(selectors.entry.narration).val() || '';

        if (! selectedLedger.id) {
            alert('You must select a ledger to post the entry to');
            return false;
        }

        if (! selectedType) {
            alert('You must indicate whether the entry is a Debit or Credit entry');
            return false;
        }

        if (! narration) {
            alert('You must add a narration for the entry');
            return false;
        }

        var dr = selectedType === 'dr' ? value : 0,
            cr = selectedType === 'cr' ? value : 0;

        var lastRowIndex = parseInt($(selectors.glEntriesTable + ' tr:not('+ selectors.glEntriesTotals +'):last').data('row-index'));
        var nextIndex = !isNaN(lastRowIndex) ? lastRowIndex + 1 : 0;
        var nameInputPrefix = 'entries['+ nextIndex +']';

        var tableRow = '<tr data-row-index="'+ nextIndex +'">'+
            '           <td class="count">' + parseInt(nextIndex + 1) +'</td>' +
            '           <input type="hidden" name="'+ nameInputPrefix +'[ledger_id]" value="'+ selectedLedger.id +'">'+
            '           <input type="hidden" name="'+ nameInputPrefix +'[ledger_display_name]" value="'+ selectedLedger.text +'">'+
            '           <td>' + selectedLedger.text +'</td>' +
            '           <td width="40%">' +
            '               <input type="text" name="'+ nameInputPrefix +'[narration]" class="form-control" value="'+ narration + '" >' +
            '           </td>' +
            '           <td width="20%">' +
            '               <input type="number" name="'+ nameInputPrefix +'[dr]" class="form-control dr" value="'+ dr + '" >' +
            '           </td>' +
            '           <td width="20%">' +
            '               <input type="number" name="'+ nameInputPrefix +'[cr]" class="form-control cr" value="'+ cr + '" >' +
            '           </td>' +
            '           <td>' +
            '               <a href="#" class="btn btn-danger btn-sm remove-entry-btn"><i class="fa fa-trash"></i></a>' +
            '           </td>' +
            '       </tr>';

        $(selectors.glEntriesTotals).before($(tableRow));

        return true; // so that the modal will get hidden
    };

    var addEntry = function () {
        appendEntryToTable() && $(selectors.addGLEntryModal).modal('hide');
        updateEntriesTotals();
        transactionIsValidToBePosted();
    };

    var removeEntry = function () {
        $(this).parents('tr').remove();
        updateEntriesTotals();
    };

    var updateEntriesTotals = function () {
        var sumDr = 0,
            sumCr = 0;

        $(selectors.glEntriesTable).find('tr:not(tr:last)').each(function (i, entry) {
            sumDr += parseFloat($(entry).find('td input.dr').val());
            sumCr += parseFloat($(entry).find('td input.cr').val());
        });

        $(selectors.glEntriesTotals +' #dr-sum').text(sumDr);
        $(selectors.glEntriesTotals +' #cr-sum').text(sumCr);
    };

    var transactionIsValidToBePosted = function () {

        var dr = parseFloat($(selectors.glEntriesTotals +' #dr-sum').text());
        var cr = parseFloat($(selectors.glEntriesTotals +' #cr-sum').text());

        if (dr === 0 && cr === 0) {
            return false;
        }

        if (dr === cr) {
            $(selectors.postLedgerTransactionButton).removeClass('hide').prop('disabled', false);
        } else {
            $(selectors.postLedgerTransactionButton).addClass('hide').prop('disabled', true);
        }
    };

    $(document.body).on('click', selectors.addEntryButton, addEntry);
    $(document.body).on('click', selectors.removeEntryButton, removeEntry);
    $(document.body).on('keyup', [selectors.drInput, selectors.crInput], updateEntriesTotals);
    $(document.body).on('keyup', [selectors.drInput, selectors.crInput], transactionIsValidToBePosted);
    $(document).ready(transactionIsValidToBePosted);

})(window.jQuery);

(function ($) {
    'use strict';

    var selectors = {
        transactionDate: 'td.date',
        valueDate: 'td.value-date',
        createdBy: 'td.created-by',
        entriesTable: 'table.entries',
        viewTransactionModal: '#view-transaction-modal',
        transactionForm: 'form#transaction-form',
        entriesHiddenInput: 'input[name="entries"]',
        unapprovedTransactionIdHiddenInput: 'input[name="unapproved_transaction_id"]',
        cancelTransactionButton: 'a.quick-cancel-transaction',
        approveTransactionButton: 'a.quick-approve-transaction'
    };

    var viewTransaction = function (event) {
        var transaction = $(event.relatedTarget).data(),
            $modal = $(this),
            entries = '';

        transaction.entries.forEach(function (entry, k) {

            var ledger = entry.ledger_display_name !== undefined ? entry.ledger_display_name : entry.ledger_id;

            entries += '<tr>' +
            '               <td>'+ parseInt(k + 1) +'</td>' +
            '               <td>'+ entry.narration +'</td>' +
            '               <td>'+ ledger +'</td>' +
            '               <td>'+ entry.dr +'</td>' +
            '               <td>'+ entry.cr +'</td>' +
            '           </tr>';
        });

        entries += '<tr>' +
            '           <td colspan="3">&nbsp;'+
            '           <td class="bg-warning"><strong>'+ microfin.formatNumber(transaction.drSubtotal) +'</strong></td>' +
            '           <td class="bg-warning"><strong>'+ microfin.formatNumber(transaction.crSubtotal) +'</strong></td>' +
            '       </tr>';

        $modal.find(selectors.transactionDate).text(transaction.date);
        $modal.find(selectors.valueDate).text(transaction.valueDate);
        $modal.find(selectors.createdBy).text(transaction.createdBy);
        $modal.find(selectors.entriesTable).find('tbody').empty().append(entries);
        $modal.find(selectors.transactionForm).find(selectors.unapprovedTransactionIdHiddenInput).val(transaction.id);
        $modal.find(selectors.transactionForm).find(selectors.entriesHiddenInput).val(JSON.stringify(transaction.entries));
    };

    var cancelTransaction = function (event) {

        ! $(selectors.unapprovedTransactionIdHiddenInput).val() &&
        $(selectors.unapprovedTransactionIdHiddenInput).val($(this).data('transactionId'));

        // set appropriate form method, action and submit form to delete the transaction
        $(selectors.transactionForm)
            .attr('action', $(this).attr('href'))
            .append('<input type="hidden" name="_method" value="delete">')
            .submit();

        event.preventDefault();

    };

    var approveTransaction = function (event) {

        ! $(selectors.unapprovedTransactionIdHiddenInput).val() &&
        $(selectors.unapprovedTransactionIdHiddenInput).val($(this).data('transactionId'));

        $(selectors.transactionForm).submit();

        event.preventDefault();
    };

    $(document.body).on('show.bs.modal', selectors.viewTransactionModal, viewTransaction);
    $(document.body).on('click', selectors.cancelTransactionButton, cancelTransaction);
    $(document.body).on('click', selectors.approveTransactionButton, approveTransaction);

})(window.jQuery);