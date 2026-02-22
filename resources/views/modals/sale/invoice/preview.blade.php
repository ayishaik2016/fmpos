<!-- Invoice Preview Modal: start -->
<div class="modal fade" id="previewSaleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" >{{ __('sale.invoice') }}</h5>
            </div>
            <div class="modal-body row g-3">
                <div class="mb-0">
                    <div class="row g-3">
                        <table class="table table-bordered" id="preview_sale_table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">QTY</th>
                                    <th>Unit</th>
                                    <th class="text-end">Price / Unit</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="preview_sale_close" data-bs-dismiss="modal">{{ __('app.close') }}</button>
            </div>
        </div>
    </div>
</div>
<!-- Tax Modal: end -->
