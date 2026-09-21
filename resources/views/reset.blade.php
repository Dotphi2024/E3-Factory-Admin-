@include('layouts.shared.css')
@include('layouts.shared.js')

<body class="bg-white">
    <!--start wrapper-->
    <div class="wrapper">
        <div class="">
            <div class="row g-0 m-0">
                <div class="col-xl-6 col-lg-12">
                    <div class="login-cover-wrapper">
                        <div class="card shadow-none">
                            <div class="card-body p-4">
                                <center>
                                    <div>
                                        <img src="{{ asset('assets/images/logo.png') }}" class="logo-icon"
                                            alt="logo icon" style="width: 110px; height: 110px; object-fit: contain;">
                                    </div>
                                </center><br>
                                <div class="text-center">
                                    <h4>Reset password</h4>
                                </div>
                                @if (session('status'))
                                    <div class="alert alert-success" role="alert">
                                        {{ session('status') }}
                                    </div>
                                @endif
                                <form class="form-body row g-3" action="{{ route('password.email') }}" method="POST">
                                    @csrf
                                    <div class="col-12">
                                        <label for="inputEmail" class="form-label">Email</label>
                                        <input id="email" type="email"
                                            class="form-control @error('email') is-invalid @enderror" name="email"
                                            value="{{ old('email') }}" required autocomplete="email" autofocus>

                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-lg-12">
                                        {{-- <a href="{{ route('confirm') }}"> --}}
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">Send</button>
                                        </div>
                                        {{-- </a> --}}
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-12">
                    <div
                        class="position-absolute top-0 h-100 d-xl-block d-none login-cover-img au-reset-password-cover">
                        <div class="text-white p-5 w-100">

                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->
        </div>
    </div>
    <!--end wrapper-->


</body>
