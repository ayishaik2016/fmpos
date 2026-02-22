$(function() {
	"use strict";

    const tableId = $('#datatable');



    const datatableForm = $("#datatableForm");

    /**
     *Server Side Datatable Records
    */
    window.loadDatatables = function() {
        //Delete previous data
        tableId.DataTable().destroy();

        /*var exportColumns = [1,2,3,4,5,6,7,8];//Index Starts from 0

        var table = tableId.DataTable({
            processing: true,
            serverSide: true,
            paging: false,        
            method:'get',
            ajax: {
                    url: baseURL+'/payment/in/datatable-list',
                    data:{
                            invoice_number : $('#invoice_number').val(),
                            party_id : $('#party_id').val(),
                            user_id : $('#user_id').val(),
                            from_date : $('input[name="from_date"]').val(),
                            to_date : $('input[name="to_date"]').val(),
                        },
                },
            columns: [
                {targets: 0, data:'id', orderable:true, visible:false},
                {data: 'transaction_date', name: 'transaction_date'},
                {data: 'reference_no', name: 'reference_no'},
                {data: 'invoice_date', name: 'invoice_date'},
                {data: 'sale_code', name: 'sale_code'},
                {data: 'party_name', name: 'party_name'},
                {data: 'payment_type', name: 'payment_type'},

                {data: 'payment', name: 'payment', className: 'text-end'},

                {data: 'username', name: 'username'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],

            dom: "<'row' "+
                    "<'col-sm-12' "+
                        "<'float-start' l"+
                        ">"+
                        "<'float-end' fr"+
                        ">"+
                        "<'float-end ms-2'"+
                            "<'card-body ' B >"+
                        ">"+
                    ">"+
                  ">"+
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

            buttons: [
                {
                    className: 'btn btn-outline-danger buttons-copy buttons-html5 multi_delete',
                    text: 'Delete',
                    action: function ( e, dt, node, config ) {
                        //Confirm user then trigger submit event
                       requestDeleteRecords();
                    }
                },
                // Apply exportOptions only to Copy button
                {
                    extend: 'copyHtml5',
                    exportOptions: {
                        columns: exportColumns
                    },
                    customizeData: function (data) {
                        data.body.push([
                            '',
                            'Cash',
                            footerTotals.Cash.toFixed(2),
                        ]);

                        data.body.push([
                            '',
                            'Cheque',
                            footerTotals.Cheque.toFixed(2),
                        ]);

                        data.body.push([
                            '',
                            'Online',
                            footerTotals.Online.toFixed(2),
                        ]);

                        data.body.push([
                            '',
                            'Bank',
                            footerTotals.Bank.toFixed(2),
                        ]);
                    }
                },
                // Apply exportOptions only to Excel button
                {
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: exportColumns,
                        customizeData: function (data) {
                            data.body.push([
                                '',
                                'Cash',
                                footerTotals.Cash.toFixed(2),
                            ]);

                            data.body.push([
                                '',
                                'Cheque',
                                footerTotals.Cheque.toFixed(2),
                            ]);

                            data.body.push([
                                '',
                                'Online',
                                footerTotals.Online.toFixed(2),
                            ]);

                            data.body.push([
                                '',
                                'Bank',
                                footerTotals.Bank.toFixed(2),
                            ]);
                        }
                    }
                },
                // Apply exportOptions only to CSV button
                {
                    extend: 'csvHtml5',
                    exportOptions: {
                        columns: exportColumns,
                        customizeData: function (data) {
                            data.body.push([
                                '',
                                'Cash',
                                footerTotals.Cash.toFixed(2),
                            ]);

                            data.body.push([
                                '',
                                'Cheque',
                                footerTotals.Cheque.toFixed(2),
                            ]);

                            data.body.push([
                                '',
                                'Online',
                                footerTotals.Online.toFixed(2),
                            ]);

                            data.body.push([
                                '',
                                'Bank',
                                footerTotals.Bank.toFixed(2),
                            ]);
                        }
                    }
                },
                // Apply exportOptions only to PDF button
                {
                    extend: 'pdfHtml5',
                    orientation: 'portrait',//or "landscape"
                    exportOptions: {
                        columns: exportColumns,
                        customize: function (doc) {
                            doc.content.push({
                                margin: [0, 10, 0, 0],
                                table: {
                                    widths: ['*', '*'],
                                    body: [
                                        ['Cash', footerTotals.Cash.toFixed(2)],
                                        ['Cheque', footerTotals.Cheque.toFixed(2)],
                                        ['Online', footerTotals.Online.toFixed(2)],
                                        ['Bank', footerTotals.Bank.toFixed(2)],
                                    ]
                                }
                            });
                        }
                    },
                },

            ],

            select: {
                style: 'os',
                selector: 'td:first-child'
            },
            order: [[0, 'desc']],
            drawCallback: function() {
                setTooltip();
            },
            footerCallback: function (row, data) {
                let footerTotals = {
                    Cash: 0,
                    Cheque: 0,
                    Online: 0,
                    Bank: 0
                };

                data.forEach(function (item) {
                    let amount = parseFloat(item.payment) || 0;
                    switch (item.payment_type_id) {
                        case 1:
                            footerTotals.Cash += amount;
                            break;

                        case 2:
                            footerTotals.Cheque += amount;
                            break;

                        case 3:
                            footerTotals.Online += amount;
                            break;
                        default: 
                            footerTotals.Bank += amount;
                            break;
                    }
                });

                let html = `
                    Cash - ${footerTotals.Cash.toFixed(2)} |
                    Cheque - ${footerTotals.Cheque.toFixed(2)} |
                    Online - ${footerTotals.Online.toFixed(2)} |
                    Bank - ${footerTotals.Bank.toFixed(2)}
                `;

                $('#payment_type_total').html(html);
            }
        });*/

        // ---------- GLOBAL TOTAL STORAGE ----------
        var footerTotals = {
            Cash: 0,
            Cheque: 0,
            Online: 0,
            Bank: 0
        };

        // Export column indexes (starts from 0)
        var exportColumns = [1,2,3,4,5,6,7,8];

        var table = tableId.DataTable({
            processing: true,
            serverSide: true,
            paging: false,
            method: 'get',

            ajax: {
                url: baseURL + '/payment/in/datatable-list',
                data: {
                    invoice_number : $('#invoice_number').val(),
                    party_id       : $('#party_id').val(),
                    payment_type_id: $('#payment_type_id').val(),
                    user_id        : $('#user_id').val(),
                    from_date      : $('input[name="from_date"]').val(),
                    to_date        : $('input[name="to_date"]').val(),
                }
            },

            columns: [
                { data: 'id', visible: false },
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'reference_no', name: 'reference_no' },
                { data: 'invoice_date', name: 'invoice_date' },
                { data: 'sale_code', name: 'sale_code' },
                { data: 'party_name', name: 'party_name' },
                { data: 'payment_type', name: 'payment_type' },
                { data: 'payment', name: 'payment', className: 'text-end' },
                { data: 'username', name: 'username' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', orderable: false, searchable: false }
            ],

            dom:
                "<'row'<'col-sm-12'<'float-start'l><'float-end'fr><'float-end ms-2'B>>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

            buttons: [

                // DELETE BUTTON
                {
                    className: 'btn btn-outline-danger multi_delete',
                    text: 'Delete',
                    action: function () {
                        requestDeleteRecords();
                    }
                },

                // COPY
                {
                    extend: 'copyHtml5',
                    exportOptions: { columns: exportColumns },
                    customizeData: function (data) {
                        appendFooterTotals(data);
                    }
                },

                // EXCEL
                {
                    extend: 'excelHtml5',
                    exportOptions: { columns: exportColumns },
                    customizeData: function (data) {
                        appendFooterTotals(data);
                    }
                },

                // CSV
                {
                    extend: 'csvHtml5',
                    exportOptions: { columns: exportColumns },
                    customizeData: function (data) {
                        appendFooterTotals(data);
                    }
                },

                // PDF
                {
                    extend: 'pdfHtml5',
                    orientation: 'portrait',
                    exportOptions: { columns: exportColumns },
                    customize: function (doc) {

                        doc.content.push({
                            margin: [0, 10, 0, 0],
                            table: {
                                widths: ['*', '*'],
                                body: [
                                    ['Cash', footerTotals.Cash.toFixed(2)],
                                    ['Cheque', footerTotals.Cheque.toFixed(2)],
                                    ['Online', footerTotals.Online.toFixed(2)],
                                    ['Bank', footerTotals.Bank.toFixed(2)]
                                ]
                            }
                        });
                    }
                }
            ],

            order: [[0, 'desc']],

            drawCallback: function () {
                setTooltip();
            },

            footerCallback: function (row, data) {

                footerTotals = { Cash: 0, Cheque: 0, Online: 0, Bank: 0 };

                data.forEach(function (item) {
                    let amount = parseFloat(item.payment) || 0;

                    switch (item.payment_type_id) {
                        case 1: footerTotals.Cash += amount; break;
                        case 2: footerTotals.Cheque += amount; break;
                        case 3: footerTotals.Online += amount; break;
                        default: footerTotals.Bank += amount; break;
                    }
                });

                $('#payment_type_total').html(
                    `Cash - ${footerTotals.Cash.toFixed(2)} |
                    Cheque - ${footerTotals.Cheque.toFixed(2)} |
                    Online - ${footerTotals.Online.toFixed(2)} |
                    Bank - ${footerTotals.Bank.toFixed(2)}`
                );
            }
        });


        // ---------- HELPER FUNCTION ----------
        function appendFooterTotals(data) {

            data.body.push(['', 'Cash', footerTotals.Cash.toFixed(2)]);
            data.body.push(['', 'Cheque', footerTotals.Cheque.toFixed(2)]);
            data.body.push(['', 'Online', footerTotals.Online.toFixed(2)]);
            data.body.push(['', 'Bank', footerTotals.Bank.toFixed(2)]);
        }


        table.on('click', '.deleteRequest', function () {
              let deleteId = $(this).attr('data-delete-id');

              deleteRequest(deleteId);

        });

        //Adding Space on top & bottom of the table attributes
        $('.dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate').wrap("<div class='card-body py-3'>");
    }

    // Handle header checkbox click event
    tableId.find('thead').on('click', '.row-select', function() {
        var isChecked = $(this).prop('checked');
        tableId.find('tbody .row-select').prop('checked', isChecked);
    });

    /**
     * @return count
     * How many checkbox are checked
    */
   function countCheckedCheckbox(){
        var checkedCount = $('input[name="record_ids[]"]:checked').length;
        return checkedCount;
   }

   /**
    * Validate checkbox are checked
    */
   async function validateCheckedCheckbox(){
        const confirmed = await confirmAction();//Defined in ./common/common.js
        if (!confirmed) {
            return false;
        }
        if(countCheckedCheckbox() == 0){
            iziToast.error({title: 'Warning', layout: 2, message: "Please select at least one record to delete"});
            return false;
        }
        return true;
   }
    /**
     * Caller:
     * Function to single delete request
     * Call Delete Request
    */
    async function deleteRequest(id) {
        const confirmed = await confirmAction();//Defined in ./common/common.js
        if (confirmed) {
            deleteRecord(id);
        }
    }

    /**
     * Create Ajax Request:
     * Multiple Data Delete
    */
   async function requestDeleteRecords(){
        //validate checkbox count
        const confirmed = await confirmAction();//Defined in ./common/common.js
        if (confirmed) {
            //Submit delete records
            datatableForm.trigger('submit');
        }
   }
    // datatableForm.on("submit", function(e) {
    //     e.preventDefault();

    //         //Form posting Functionality
    //         const form = $(this);
    //         const formArray = {
    //             formId: form.attr("id"),
    //             csrf: form.find('input[name="_token"]').val(),
    //             _method: form.find('input[name="_method"]').val(),
    //             url: form.closest('form').attr('action'),
    //             formObject : form,
    //             formData : new FormData(document.getElementById(form.attr("id"))),
    //         };
    //         ajaxRequest(formArray); //Defined in ./common/common.js

    // });

    /**
     * Create AjaxRequest:
     * Single Data Delete
    */
    function deleteRecord(id){
        const form = datatableForm;
        const formArray = {
            formId: form.attr("id"),
            csrf: form.find('input[name="_token"]').val(),
            _method: form.find('input[name="_method"]').val(),
            url: baseURL + '/payment/sale-invoice/delete/'+id,
            formObject : form,
            formData: new FormData() // Create a new FormData object
        };
        // Append the 'id' to the FormData object
        formArray.formData.append('record_ids[]', id);
        ajaxRequest(formArray); //Defined in ./common/common.js
    }

    /**
    * Ajax Request
    */
    function ajaxRequest(formArray){
        var jqxhr = $.ajax({
            type: formArray._method,
            url: formArray.url,
            data: formArray.formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': formArray.csrf
            },
            beforeSend: function() {
                // Actions to be performed before sending the AJAX request
                if (typeof beforeCallAjaxRequest === 'function') {
                    // Action Before Proceeding request
                }
            },
        });
        jqxhr.done(function(data) {

            iziToast.success({title: 'Success', layout: 2, message: data.message});
        });
        jqxhr.fail(function(response) {
                var message = response.responseJSON.message;
                iziToast.error({title: 'Error', layout: 2, message: message});
        });
        jqxhr.always(function() {
            // Actions to be performed after the AJAX request is completed, regardless of success or failure
            if (typeof afterCallAjaxResponse === 'function') {
                afterCallAjaxResponse(formArray.formObject);
            }
        });
    }

    function afterCallAjaxResponse(formObject){
        loadDatatables();
    }

    $(document).ready(function() {
        //Load Datatable
        loadDatatables();

        /**
         * Modal payment type, reinitiate initSelect2PaymentType() for modal
         * Call because modal won't support ajax search input box cursor.
         * by this code it works
         * */
        //initSelect2PaymentType({ dropdownParent: $('#invoicePaymentModal') });
	} );

    $(document).on("change", '#payment_type_id, #party_id, #user_id, input[name="from_date"], input[name="to_date"]', function function_name(e) {
        loadDatatables();
    });

    $(document).on("blur", '#invoice_number', function function_name(e) {
        loadDatatables();
    });

});
