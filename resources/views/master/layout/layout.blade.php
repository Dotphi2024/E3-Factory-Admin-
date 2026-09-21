@extends('master.layout.wrapper')

@section('body')

    <body>
        <div class="wrapper">
            @include('master.partials.sidebar')

            @include('master.partials.header')

            <div class="page-content-wrapper">

                <div class="page-content">

                    @yield('main_content')

                    @include('master.partials.footer')
                </div>

            </div>

        </div>

        @yield('style')

        @yield('js-files')

        @include('layouts.shared.js')

    </body>
@endsection
