@extends('layouts.layout.wrapper')

@section('body')

    <body>
        <div class="wrapper">
            @include('layouts.partials.sidebar')

            @include('layouts.partials.header')

            <div class="page-content-wrapper">

                <div class="page-content">

                    @yield('main_content')



                    @include('layouts.partials.footer')
                </div>

            </div>

        </div>

        @yield('style')

        @yield('js-files')

        @include('layouts.shared.js')

    </body>
@endsection
