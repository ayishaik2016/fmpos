<!DOCTYPE html>
<html lang="en" dir="{{ $appDirection }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoiceData['name'] }}</title>
    <link href="{{ versionedAsset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ versionedAsset('custom/css/print/pos.css?v=' . config('constants.version')) }}"/>
</head>
<body>
    <div class="invoice-wrapper">
        <div class="container mt-3">
            <div class="invoice-header">
                 @php
                    $companyLogo = url('/company/getimage/'.app('company')['colored_logo']);
                @endphp
                <div class="invoice-logo"><img width="150" src="{{ $companyLogo }}" alt="Logo" class="company-logo"></div>
                <div class="invoice-title">
                    <span>{{ app('company')['name'] }}</span><br>
                </div>
                <div><span>(1447932-K)</span></div>
                <div>
                    {{ app('company')['address'] }}
                        <p>
                            @if(app('company')['mobile'])
                                <span>{{ app('company')['mobile'] ? 'Contact: '. app('company')['mobile'] : ''}}</span>
                            @endif
                        <br>
                            @if(app('company')['mobile'] || app('company')['email'])
                                <span>{{ app('company')['email'] ? ' Mail: '.app('company')['email'] : '' }}</span>
                            @endif
                            @if(app('company')['tax_number']!= '' && app('company')['tax_type'] != 'no-tax')
                                <br>{{ app('company')['tax_type'] == 'gst' ? 'GST:' : __('tax.tax') . ':' }} {{ app('company')['tax_number'] }}
                            @endif
                        </p>
                </div>
            </div>

            <div class="text-center"><h6>{{ $invoiceData['name'] }}</h6></div>

            <div class="row">
                <div class="col-6">
                    <div><b>{{ __('app.name') }}: {{ $sale->party->first_name.' '. $sale->party->last_name }}</b></div>
                    <!-- <div>{{ __('app.mobile') }}: {{ $sale->party->mobile }}</div> -->
                    <!-- <div>{{ __('sale.sales') }}: {{ $sale->user->first_name.' '. $sale->user->last_name }}</div> -->
                    {{-- Party Tax/GST Number --}}
                    @include('print.common.party-tax-details', ['model' => $sale, 'isPOSInvoice'    => true])

                </div>
                <div class="col-6 text-end">
                    <div><b>{{ __('sale.invoice') }}: #{{ $sale->sale_code  }}</b></div>
                    <div><b>{{ __('app.date') }}: {{ $sale->formatted_sale_date  }}</b></div>
                    <div><b>{{ __('app.time') }}: {{ $sale->format_created_time  }}</b></div>
                    @if(!empty($sale->reference_no))
                        <div>{{ __('app.reference_no') }}: {{ $sale->reference_no }}</div>
                    @endif
                </div>
            </div>

            <div class="row mt-2">
                <span><b>Sales Person</b></span><br>
                <div class="col-12">
                    <span>{{ __('sale.name') }}: {{ $sale->user->first_name.' '. $sale->user->last_name }}</span><br>
                    <span>{{ __('sale.mobile') }}: {{ $sale->user->mobile }}</span>
                </div>
            </div>

            <table class="table table-sm mt-2">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('app.description') }}</th>
                        <th class="text-end">{{ __('app.qty') }}</th>
                        <th class="text-end">{{ __('app.price_per_unit') }}</th>
                        <th class="text-end">{{ __('app.total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i=1;
                    @endphp

                    @foreach($sale->itemTransaction as $transaction)
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>
                            {{ $transaction->item->name }}
                            <small>{{ $transaction->description }}</small>
                            {{-- Show Brand Name --}}
                            @include('print.common.brand-details', ['model' => $transaction])
                            <small>
                                @if ($transaction->itemSerialTransaction->count() > 0)
                                    <br>{{ $transaction->itemSerialTransaction->pluck('itemSerialMaster.serial_code')->implode(',') }}<br>
                                @endif
                                @if($transaction->batch)
                                    <br>
                                    <i>{{ __('item.batch') }}</i>
                                    {{ $transaction->batch->itemBatchMaster->batch_no }}
                                    <!-- Check is expire_date exist then show -->
                                     @if($transaction->batch->itemBatchMaster->exp_date)
                                     ,<i>{{ __('item.exp') }}</i> {{ $formatDate->toUserDateFormat($transaction->batch->itemBatchMaster->exp_date) }}
                                     @endif
                                @endif

                            </small>
                        </td>
                        <td class="text-end bold-text">{{ $formatNumber->formatQuantity($transaction->quantity) }}</td>
                        <td class="text-end">{{ $formatNumber->formatWithPrecision($transaction->total/$transaction->quantity) }}</td>

                        {{--
                            Note:
                                Calculate Total = (Unit Price - Discount) + Tax
                                Here we are showing only Total, in below destriburted the discount and Tax
                        --}}
                        <td class="text-end bold-text">{{ $formatNumber->formatWithPrecision($transaction->total) }}</td>
                    </tr>
                    @endforeach

                    @php
                       $totalQty = $sale->itemTransaction->sum(function ($transaction) {
                            return $transaction->quantity;
                        });
                       $totalAmount = $sale->itemTransaction->sum(function ($transaction) {
                            return $transaction->total;
                        });
                    @endphp
                    @if($sale->return_sale == 1)
                        <tr class="fw-bold">
                            <td colspan="5">{{ __('app.total_quantity') }}: {{ $formatNumber->formatQuantity($totalQty) }}</td>
                        </tr> 
                        <tr class="text-end fw-bold">
                            <td colspan="5">{{ __('app.total') }}: {{ $formatNumber->formatWithPrecision($totalAmount) }}</td>
                        </tr>
                        <tr class="text-end fw-bold">
                            <td colspan="5">{{ __('sale.sale_return') }}: (-){{ $formatNumber->formatWithPrecision($sale->return_sale_amount) }}</td>
                        </tr>
                        <tr class="text-end fw-bold">
                            <td class="bold-foot" colspan="5">{{ __('sale.total_amount') }}: {{ $formatNumber->formatWithPrecision($sale->grand_total) }}</td>
                        </tr>
                    @else
                        <!-- <tr class="text-end fw-bold">
                            <td colspan="4">{{ __('app.total') }}</td>
                            <td>{{ $formatNumber->formatQuantity($totalQty) }}</td> 
                            <td>{{ $formatNumber->formatWithPrecision($sale->grand_total) }}</td>
                        </tr> -->
                        <tr class="fw-bold">
                            <td colspan="5">{{ __('app.total_quantity') }}: {{ $formatNumber->formatQuantity($totalQty) }}</td>
                            <!-- <td>{{ $formatNumber->formatQuantity($totalQty) }}</td>
                            <td>{{ $formatNumber->formatWithPrecision($sale->grand_total) }}</td> -->
                        </tr>
                        <tr class="text-end fw-bold">
                            <td class="bold-foot" colspan="5">{{ __('app.total_amount') }}: {{ $formatNumber->formatQuantity($sale->grand_total) }}</td>
                            <!-- <td>{{ $formatNumber->formatQuantity($totalQty) }}</td>
                            <td>{{ $formatNumber->formatWithPrecision($sale->grand_total) }}</td> -->
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
     <div class="container mt-3 mb-3 hide-print-btn">
        <a class="btn btn-success print-btn" href="{{ url('sale/invoice/list') }}" onclick="window.close();">Back</a>
    </div>
    <script type="text/javascript">
        window.print();
    </script>
</body>
</html>
