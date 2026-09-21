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
                                        <img src="{{ asset('assets/images/logo-icon-3.png') }}" class="logo-icon"
                                            alt="logo icon">
                                    </div>
                                </center><Br>
                                <div class="text-center">
                                    <h4>Reset password</h4>
                                </div>
                                <form class="form-body row g-3">
                                    <div class="col-12">
                                        <label for="inputEmail" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="inputEmail"
                                            placeholder="abc@example.com">
                                    </div>
                                    <div class="col-12 col-lg-12">
                                        <a href="">
                                            <div class="d-grid">
                                                <button type="button" class="btn btn-primary">Send</button>
                                            </div>
                                        </a>
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
