@extends('layouts.app')
@section('title', __('raw_item.list'))

@section('css')
<link href="{{ versionedAsset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection
		@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
					<x-breadcrumb :langArray="[
											'raw_item.items',
											'raw_item.list',
										]"/>

                    <div class="card">

					<div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
					    <!-- Other content on the left side -->
					    <div>
					    	<h5 class="mb-0 text-uppercase">{{ __('raw_item.list') }}</h5>
					    </div>
					    <div class="d-flex gap-2">
						    @can('item.create')
						    <!-- Button pushed to the right side -->
						    <x-anchor-tag href="{{ route('raw_items.create') }}" text="{{ __('raw_item.create') }}" class="btn btn-primary px-5" />
						    @endcan
						</div>
					</div>

					<div class="card-body">
                        <form class="row g-3 needs-validation" id="datatableForm" action="{{ route('raw_items.delete') }}" enctype="multipart/form-data">
                            {{-- CSRF Protection --}}
                            @csrf
                            @method('POST')
							<div class="table-responsive">
								<table class="table table-striped table-bordered border w-100" id="datatable">
									<thead>
										<tr>
											<th class="d-none"><!-- Which Stores ID & it is used for sorting --></th>
											<th><input class="form-check-input row-select" type="checkbox"></th>
											<th>{{ __('app.name') }}</th>
											<th>{{ __('raw_item.code') }}</th>
                                            <th>{{ __('raw_item.category.category') }}</th>
											<th>{{ __('raw_item.price') }}</th>
											<th>{{ __('raw_item.quantity') }}</th>
											<th>{{ __('app.created_by') }}</th>
											<th>{{ __('app.created_at') }}</th>
											<th>{{ __('app.action') }}</th>
										</tr>
									</thead>
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
<script src="{{ versionedAsset('custom/js/raw-items/item-list.js?v=' . config('constants.version')) }}"></script>
@endsection
