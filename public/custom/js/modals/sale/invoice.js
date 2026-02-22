$(function() {
	"use strict";

    $(document).on('click', '#preview_sale', function () {
        let $previewBody = $('#preview_sale_table tbody');
        $previewBody.empty();
        
        $($('#invoiceItemsTable tbody tr').get().reverse()).each(function () {
            let itemName = $(this).find('label.form-label').text().trim();
            let qty = $(this).find('input[name^="quantity"]').val();
            let unit = $(this).find('select[name^="unit_id"] option:selected').text();
            let price = $(this).find('input[name^="sale_price"]').val();
            let total = $(this).find('input[name^="total"]').val();

            if (!itemName) return; // skip empty rows

            let row = `
                <tr>
                    <td>${itemName}</td>
                    <td class="text-center">${qty}</td>
                    <td>${unit}</td>
                    <td class="text-end">${parseFloat(price).toFixed(2)}</td>
                    <td class="text-end">${parseFloat(total).toFixed(2)}</td>
                </tr>
            `;

            $previewBody.append(row);
        });

        let grandTotal = $('.grand_total').val();
        $('#preview_sale_table tfoot').html(`<tr><td colspan="4" class="text-end">Total Amount:</td><td>${grandTotal}</td></tr>`);

        $('#previewSaleModal').modal('show');
    });

    $(document).on('click', '#preview_sale_close',  function(e) {
        e.preventDefault();

        $('#previewSaleModal').modal('hide');
        $('#preview_sale_table tbody').html('');
        
    });
});//main function
