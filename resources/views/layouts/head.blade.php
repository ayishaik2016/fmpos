<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href='{{ url("/fevicon/" . $fevicon) }}'  type="image/png" />
	<!--plugins-->
	<link href="{{ versionedAsset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.css?v=' . config('constants.version')) }}" rel="stylesheet" />
	<link href="{{ versionedAsset('assets/plugins/simplebar/css/simplebar.css?v=' . config('constants.version')) }}" rel="stylesheet" />
	<link href="{{ versionedAsset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css?v=' . config('constants.version')) }}" rel="stylesheet" />
	<link href="{{ versionedAsset('assets/plugins/metismenu/css/metisMenu.min.css?v=' . config('constants.version')) }}" rel="stylesheet" />
	
	<!--PWA Extension-->
	<link rel="manifest" href="{{ versionedAsset('manifest.json') }}">
	<meta name="theme-color" content="#0d6efd">
	<link rel="apple-touch-icon" href="{{ versionedAsset('assets/images/icons/icon-192x192.png') }}">
	<meta name="apple-mobile-web-app-capable" content="yes">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ versionedAsset('custom/libraries/select2-theme/select2-4.1.0-rc.0/dist/css/select2.min.css?v=' . config('constants.version')) }}">
    <link rel="stylesheet" href="{{ versionedAsset('custom/libraries/select2-theme/select2-bootstrap-5-theme-1.3.0/dist/select2-bootstrap-5-theme.min.css?v=' . config('constants.version')) }}">
	<!-- loader-->
	<script src="{{ versionedAsset('assets/js/pace.min.js?v=' . config('constants.version')) }}"></script>
	<link href="{{ versionedAsset('assets/css/pace.min.css?v=' . config('constants.version')) }}" rel="stylesheet" />
	<!-- Bootstrap CSS -->
	@if($appDirection=='ltr')
	<link href="{{ versionedAsset('assets/css/bootstrap.min.css?v=' . config('constants.version')) }}" rel="stylesheet">
	<link href="{{ versionedAsset('assets/css/bootstrap-extended.css?v=' . config('constants.version')) }}" rel="stylesheet">
	<link href="{{ versionedAsset('assets/css/app.css?v=' . config('constants.version')) }}" rel="stylesheet">
	@else
	<link href="{{ versionedAsset('assets/rtl/css/bootstrap.min.css?v=' . config('constants.version')) }}" rel="stylesheet">
	<link href="{{ versionedAsset('assets/rtl/css/bootstrap-extended.css?v=' . config('constants.version')) }}" rel="stylesheet">
	<link href="{{ versionedAsset('assets/rtl/css/app.css?v=' . config('constants.version')) }}" rel="stylesheet">
	@endif
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="{{ versionedAsset('assets/css/icons.css?v=' . config('constants.version')) }}" rel="stylesheet">
	<!-- Notification Toast -->
    <link rel="stylesheet" href="{{ versionedAsset('custom/libraries/iziToast/dist/css/iziToast.min.css?v=' . config('constants.version')) }}">
    <!-- Date & Time Picker -->
    <link rel="stylesheet" href="{{ versionedAsset('custom/libraries/flatpickr/flatpickr.min.css?v=' . config('constants.version')) }}">
    <!-- Autocomplete -->
    <link href="{{ versionedAsset('assets/plugins/jquery-ui/jquery-ui.css?v=' . config('constants.version')) }}" rel="stylesheet" />
	<!-- Theme Style CSS -->
	<link rel="stylesheet" href="{{ versionedAsset('assets/css/dark-theme.css?v=' . config('constants.version')) }}"/>
	<link rel="stylesheet" href="{{ versionedAsset('assets/css/semi-dark.css?v=' . config('constants.version')) }}"/>
	<link rel="stylesheet" href="{{ versionedAsset('assets/css/header-colors.css?v=' . config('constants.version')) }}"/>
	<!-- Flags CSS -->
	<link rel="stylesheet" href="{{ versionedAsset('custom/libraries/flag-icons-main/css/flag-icons.min.css?v=' . config('constants.version')) }}">
	<!-- Custom CSS -->
	<link rel="stylesheet" href="{{ versionedAsset('custom/css/custom.css?v=' . config('constants.version')) }}">
	@yield('css')
	<title>@yield('title', app('company')['name'])</title>
</head>
