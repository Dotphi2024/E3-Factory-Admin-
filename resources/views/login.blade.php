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
                            <div class="card-body">
                                <center>
                                    <div>
                                        <img src="{{ asset('assets/images/logo.png') }}" class="logo-icon"
                                            alt="logo icon" style="width: 110px; height: 110px; object-fit: contain;">
                                    </div>
                                </center><br>
                                <form class="form-body row g-3" action="{{ route('login-post') }}" method="POST">
                                    @csrf
                                    <div class="col-12">
                                        <label for="inputEmail" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="inputEmail" name="email"
                                            value="{{ old('email') }}">
                                        @error('email')
                                            <span class="invalid-email text-danger" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="inputPassword" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="inputPassword" name="password">
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="flexSwitchCheckRemember">
                                            <label class="form-check-label" for="flexSwitchCheckRemember">Remember
                                                Me</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-6 text-end">
                                        <a href="{{ route('password.request') }}">Forgot Password?</a>
                                    </div>
                                    <div class="col-12 col-lg-12">

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">Sign In</button>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-12">
                    <div class="position-absolute top-0 h-100 d-xl-block d-none login-cover-img">
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
