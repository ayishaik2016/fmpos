<!DOCTYPE html>
<html lang="en" dir="{{ $appDirection }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoiceData['name'] }}</title>
    <link href="{{ versionedAsset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ versionedAsset('custom/css/print/pos.css') }}?v"/>
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
                    <span>(1447932-K)</span>
                </div>
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
                    <div>{{ __('app.name') }}: {{ $sale->party->first_name.' '. $sale->party->last_name }}</div>
                    <div>{{ __('app.mobile') }}: {{ $sale->party->mobile }}</div>
                    @include('print.common.party-tax-details', ['model' => $sale, 'isPOSInvoice'    => true])

                </div>
                <div class="col-6 text-end">
                    <div>{{ __('sale.invoice') }}: #{{ $sale->sale_code  }}</div>
                    <div>{{ __('app.date') }}: {{ $sale->formatted_sale_date  }}</div>
                    <div>{{ __('app.time') }}: {{ $sale->format_created_time  }}</div>
                    @if(!empty($sale->reference_no))
                        <div>{{ __('app.reference_no') }}: {{ $sale->reference_no }}</div>
                    @endif
                </div>
            </div>

            <!-- <div class="row mt-2">
                <span><b>Sales Person</b></span><br>
                <div class="col-6"><span>{{ __('sale.name') }}: {{ $sale->user->first_name.' '. $sale->user->last_name }}</span><br></div>
                <div class="col-6 text-end"><span>{{ __('sale.mobile') }}: {{ $sale->user->mobile }}</span></div>
            </div> -->

            <div class="row mt-2">
                <span><b>Sales Person</b></span><br>
                <div class="col-12">
                    <span>{{ __('sale.name') }}: {{ $sale->user->first_name.' '. $sale->user->last_name }}</span><br>
                    <span>{{ __('sale.mobile') }}: {{ $sale->user->mobile }}</span>
                </div>
            </div>

            <div class="row text-center mt-3">
                @php
                    $subtotal = $sale->itemTransaction->sum(function ($transaction) {
                        $unitPrice = $transaction->unit_price;
                        return $unitPrice * $transaction->quantity;
                    });

                    $taxAmount = $sale->itemTransaction->sum(function ($transaction) {
                                return $transaction->tax_amount;
                            });
                @endphp

                <div class="col-8"><strong>{{ __('Total Amount') }}({{$currencyDetail->symbol}})</strong></div>
                <div class="col-4 text-start">{{ $formatNumber->formatWithPrecision($sale->grand_total) }}</div>

                <div class="col-8"><strong>{{ __('Paid Amount') }}({{$currencyDetail->symbol}})</strong></div>
                <div class="col-4 text-start">{{ $formatNumber->formatWithPrecision($sale->paid_amount) }}</div>

                <div class="col-8"><strong>{{ __('Balance Payment') }}({{$currencyDetail->symbol}})</strong></div>
                <div class="col-4 text-start">{{ $formatNumber->formatWithPrecision($sale->grand_total - $sale->paid_amount) }}</div>

            </div>

            @if($invoiceData['summary'] == 1)
                @if($sale->paymentTransaction()->exists())
                    <div class="row text-center mt-3">
                        <p><b>Payment Summary</b></p>
                        <div class="col-12">
                            <table class="table table-bordered" id="payment-history-table">
                                <thead>
                                    <tr>
                                        <th class="text-center">Date</th>
                                        <th class="text-center">Type</th>
                                        <th class="text-center">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sale->paymentTransaction as $paymentTransaction)
                                        <tr>
                                            <td>{{ $paymentTransaction->getFormattedTransactionDateAttribute() }}</td>
                                            <td>{{ $paymentTransaction->paymentType->name }}</td>
                                            <td class="text-end">{{ $formatNumber->formatWithPrecision($paymentTransaction->amount) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div> 
                @endif        
            @endif
        </div>
    </div>
     <div class="container mt-3 mb-3 hide-print-btn">
        <a class="btn btn-success print-btn" href="{{ url('sale/invoice/list') }}">Back</a>
    </div>
    <script type="text/javascript">
        window.print();
    </script>
</body>
</html>
