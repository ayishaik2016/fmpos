@extends('layouts.app')
@section('title', __('raw_item.create'))

		@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<x-breadcrumb :langArray="[
											'raw_item.items',
											'raw_item.list',
											'raw_item.create',
										]"/>
				<div class="row">
					<div class="col-12 col-lg-12">
                        <div class="card">
                            <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                              <h5 class="mb-0">{{ __('raw_item.details') }}</h5>
                            </div>
                            <div class="card-body p-4">
                                <form class="row g-3 needs-validation" id="itemForm" action="{{ route('raw_items.store') }}" enctype="multipart/form-data">
                                    {{-- CSRF Protection --}}
                                    @csrf
                                    @method('POST')

                                    <input type="hidden" id="operation" name="operation" value="save">
                                    <input type="hidden" id="base_url" value="{{ url('/') }}">

                                    <div class="col-md-4">
                                        <x-label for="name" name="{{ __('app.name') }}" />
                                        <x-input type="text" name="name" :required="true" value=""/>
                                    </div>

                                    <div class="col-md-4">
                                        <x-label for="hsn" name="{{ __('raw_item.code') }}" />
                                        <div class="input-group mb-3">
                                            <x-input type="text" name="item_code" :required="true" value="{{ $data['sequence_no'] }}"/>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <x-label for="item_category_id" name="{{ __('raw_item.category.category') }}" />
                                        <div class="input-group">
                                            <x-dropdown-raw-item-category selected="" :isMultiple=false />
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <x-label for="price" name="{{ __('raw_item.price') }}" />
                                        <div class="input-group mb-3">
                                            <x-input type="text" name="price" :required="false" additionalClasses='cu_numeric fw-bold bg-light-success' value=""/>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <x-label for="price" name="{{ __('raw_item.opening_quantity') }}" />
                                        <div class="input-group mb-3">
                                            <x-input type="text" name="opening_quantity" :required="false" value=""/>
                                        </div>
                                    </div>

                                     <div class="col-md-4">
                                        <x-label for="tax_id" name="{{ __('tax.tax') }}" />
                                        <div class="input-group">
                                            <x-drop-down-taxes selected="" />
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <x-label for="primary" name="{{ __('unit.base') }}" />
                                        <x-dropdown-units selected="" dropdownName='base_unit_id'/>
                                    </div>

                                    <div class="col-md-4">
                                        <x-label for="description" name="{{ __('app.description') }}" />
                                        <x-textarea name="description" value=""/>
                                    </div>

                                    <div class="col-md-4 d-none">
                                        <x-label for="status" name="{{ __('app.status') }}" />
                                        <x-dropdown-status selected="" dropdownName='status'/>
                                    </div>

                                    <div class="col-md-8">
                                        <x-label for="picture" name="{{ __('app.image') }}" />
                                        <x-browse-image
                                                        src="{{ url('/noimage/') }}"
                                                        name='image'
                                                        imageid='uploaded-image-1'
                                                        inputBoxClass='input-box-class-1'
                                                        imageResetClass='image-reset-class-1'
                                                        />
                                    </div>

                                    <div class="col-md-12">
                                        <div class="d-md-flex d-grid align-items-center gap-3">
                                            <x-button type="submit" class="primary px-4" text="{{ __('app.submit') }}" />
                                            <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}" class="btn btn-light px-4" />
                                        </div>
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
<script src="{{ versionedAsset('custom/js/raw-items/item.js') }}"></script>
@endsection
