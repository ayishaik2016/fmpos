@extends('layouts.app')
@section('title', __('payment.payment_in'))
@php
$roles = config('constants.roles');
@endphp

@section('css')
<link href="{{ versionedAsset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection
        @section('content')

        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">
                    <x-breadcrumb :langArray="[
                                            'sale.sale',
                                            'payment.payment_in',
                                        ]"/>

                    <div class="card">

                    <div class="card-header px-4 py-3 d-flex justify-content-between">
                        <!-- Other content on the left side -->
                        <div>
                            <h5 class="mb-0 text-uppercase">{{ __('payment.payment_in') }}</h5>
                        </div>

                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <x-label for="party_id" name="{{ __('sale.invoice_no') }}" />
                                <input type="text" class="form-control" name="invoice_number" value="" id="invoice_number"/>
                            </div>
                            <div class="col-md-3">
                                <x-label for="party_id" name="{{ __('customer.customers') }}" />

                                <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Search by name, mobile, phone, whatsApp, email"><i class="fadeIn animated bx bx-info-circle"></i></a>

                                <select class="party-ajax form-select" data-party-type='customer' data-placeholder="Select Customer" id="party_id" name="party_id"></select>
                            </div>
                            <div class="col-md-3">
                                <x-label for="user_id" name="{{ __('user.user') }}" />
                                <x-dropdown-user selected="{{ ((auth()->user()->role_id == $roles['DRIVER']) ? auth()->user()->id : '')  }}" :showOnlyUsername='false' />
                            </div>
                            <div class="col-md-3">
                                <x-label for="payment_type_id" name="{{ __('payment.type') }}" />
                                <select id="payment_type_id" class="form-select select2 payment-type-ajax" name="payment_type_id" data-placeholder="Choose one thing">
                                </select>
                            </div>
                            <div class="col-md-3">
                                <x-label for="from_date" name="{{ __('app.from_date') }}" />
                                <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Filter by Payment Out Date"><i class="fadeIn animated bx bx-info-circle"></i></a>
                                <div class="input-group mb-3">
                                    <x-input type="text" additionalClasses="datepicker" name="from_date" :required="true" value=""/>
                                    <span class="input-group-text" id="input-near-focus" role="button"><i class="fadeIn animated bx bx-calendar-alt"></i></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <x-label for="to_date" name="{{ __('app.to_date') }}" />
                                <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Filter by Payment Out Date"><i class="fadeIn animated bx bx-info-circle"></i></a>
                                <div class="input-group mb-3">
                                    <x-input type="text" additionalClasses="datepicker" name="to_date" :required="true" value=""/>
                                    <span class="input-group-text" id="input-near-focus" role="button"><i class="fadeIn animated bx bx-calendar-alt"></i></span>
                                </div>
                            </div>
                        </div>
                        
                        <form class="row g-3 needs-validation" id="datatableForm" action="" enctype="multipart/form-data">
                            {{-- CSRF Protection --}}
                            @csrf
                            @method('GET')
                            <input type="hidden" id="base_url" value="{{ url('/') }}">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered border w-100" id="datatable">
                                    <thead>
                                        <tr>
                                            <th class="d-none"><!-- Which Stores ID & it is used for sorting --></th>
                                            <th>{{ __('app.date') }}</th>
                                            <th>{{ __('app.reference_no') }}</th>
                                            <th>{{ __('sale.invoice_date') }}</th>
                                            <th>{{ __('sale.invoice_no') }}</th>
                                            <th>{{ __('customer.customer') }}</th>
                                            <th>{{ __('payment.payment_type') }}</th>
                                            <th>{{ __('payment.paid') }}</th>
                                            <th>{{ __('user.user') }}</th>
                                            <th>{{ __('app.created_at') }}</th>
                                            <th>{{ __('app.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tfoot>  
                                        <tr>
                                            <th colspan="11" class="text-end fw-bold">
                                                <span id="payment_type_total"></span>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
                    </div>
                </div>
                <!--end row-->
            </div>
        </div>

        @endsection
@section('js')
<script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js?v=' . config('constants.version')) }}"></script>
<script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js?v=' . config('constants.version')) }}"></script>
<script src="{{ versionedAsset('custom/js/common/common.js?v=' . config('constants.version')) }}"></script>
<script src="{{ versionedAsset('custom/js/sale/sale-payment-list.js?v=' . config('constants.version')) }}"></script>

<script>
    @if(auth()->user()->role_id == $roles['DRIVER']) 
        $('#user_id').attr('disabled', 'disabled');
    @endif
</script>
@endsection
