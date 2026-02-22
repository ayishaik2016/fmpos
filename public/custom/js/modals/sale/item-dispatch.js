$(function() {
	"use strict";

    $(document).on('click', '#preview_sale', function () {
        let $previewBody = $('#preview_sale_table tbody');
        $previewBody.empty();

        /*let invoiceItemTable = $('#invoiceItemsTable tbody tr');
        if($('#operation').val() == 'save') {
            invoiceItemTable = $('#invoiceItemsTable tbody tr').get().reverse();
        }
        $(invoiceItemTable).each(function () {*/

        $($('#invoiceItemsTable tbody tr').get().reverse()).each(function () {

            let itemName = $(this).find('label.form-label').text().trim();
            let qty = $(this).find('input[name^="quantity"]').val();
            let stock = $(this).find('input[name^="stockInUnit"]').val();
            let unit = $(this).find('select[name^="unit_id"] option:selected').text();

            if (!itemName) return; // skip empty rows

            let row = `
                <tr>
                    <td>${itemName}</td>
                    <td>${stock}</td>
                    <td class="text-center">${qty}</td>
                    <td>${unit}</td>
                </tr>
            `;

            $previewBody.append(row);
        });

        let grandTotal = $('#total_quantity').val();
        $('#preview_sale_table tfoot').html(`<tr><td colspan="3" class="text-end">Total Quantity:</td><td>${grandTotal}</td></tr>`);

        $('#previewSaleModal').modal('show');
    });

    $(document).on('click', '#preview_sale_close',  function(e) {
        e.preventDefault();

        $('#previewSaleModal').modal('hide');
        $('#preview_sale_table tbody').html('');
        
    });
});//main function
