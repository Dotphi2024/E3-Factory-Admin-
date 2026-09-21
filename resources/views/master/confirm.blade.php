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
                                        <img src="{{ asset('assets/images/logo-icon-3.png') }}" class="logo-icon"
                                            alt="logo icon">
                                    </div>
                                </center>
                                <form class="form-body row g-3">
                                    <div class="col-12">
                                        <label for="inputName" class="form-label">Password</label>
                                        <input type="text" class="form-control" id="inputName">
                                    </div>
                                    <div class="col-12">
                                        <label for="inputEmail" class="form-label">Confirm Password</label>
                                        <input type="email" class="form-control" id="inputEmail">
                                    </div>
                                    <div class="col-12 col-lg-12">
                                        <div class="d-grid">
                                            <button type="button" class="btn btn-warning">Sign In</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-12">
                    <div class="position-absolute top-0 h-100 d-xl-block d-none register-cover-img">
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
