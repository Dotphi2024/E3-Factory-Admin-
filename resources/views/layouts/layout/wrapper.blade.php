<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('site_title')</title>
    <meta name="robots" content="index, follow" />
    <meta name="description" content="@yield('site_description')">
    <meta name="keywords" content="@yield('site_keyword')">
    @yield('head_content')
    {{-- <link rel="shortcut icon" type="image/x-icon" href="{{ asset('uploads/' . $setting->favicon_icon) }}"> --}}
    @include('layouts.shared.css')
</head>
@yield('body')

</html>
