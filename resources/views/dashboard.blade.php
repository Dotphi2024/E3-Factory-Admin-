@extends('layouts.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Dashboard</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="javascript:;"><ion-icon name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">eCommerce</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <div class="btn-group">
                <button type="button" class="btn btn-outline-primary">Settings</button>
                <button type="button"
                    class="btn btn-outline-primary split-bg-primary dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown"> <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end"> <a class="dropdown-item"
                        href="javascript:;">Action</a>
                    <a class="dropdown-item" href="javascript:;">Another action</a>
                    <a class="dropdown-item" href="javascript:;">Something else here</a>
                    <div class="dropdown-divider"></div> <a class="dropdown-item" href="javascript:;">Separated link</a>
                </div>
            </div>
        </div>
    </div>
    <!--end breadcrumb-->


    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 row-cols-xxl-3">
        <div class="col">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon-2 bg-gradient-info text-white">
                            <ion-icon name="bag-handle-sharp"></ion-icon>
                        </div>
                        <div class="fs-5 ms-auto"><ion-icon name="ellipsis-horizontal-sharp"></ion-icon></div>
                    </div>
                    <h5 class="my-3">Total patients</h5>
                    <div class="progress mt-1" style="height: 5px;">
                        <div class="progress-bar bg-gradient-info" role="progressbar" style="width: 65%"></div>
                    </div>
                    <p class="mb-0 mt-2">48,256<span class="float-end text-danger">-2.8%</span></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon-2 bg-gradient-danger text-white">
                            <ion-icon name="card-sharp"></ion-icon>
                        </div>
                        <div class="fs-5 ms-auto"><ion-icon name="ellipsis-horizontal-sharp"></ion-icon></div>
                    </div>
                    <h5 class="my-3">Total Appointments</h5>
                    <div class="progress mt-1" style="height: 5px;">
                        <div class="progress-bar bg-gradient-danger" role="progressbar" style="width: 65%"></div>
                    </div>
                    <p class="mb-0 mt-2">$98,246<span class="float-end text-success">+5.9%</span></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon-2 bg-gradient-success text-white">
                            <ion-icon name="wallet-sharp"></ion-icon>
                        </div>
                        <div class="fs-5 ms-auto"><ion-icon name="ellipsis-horizontal-sharp"></ion-icon></div>
                    </div>
                    <h5 class="my-3">Total Staffs</h5>
                    <div class="progress mt-1" style="height: 5px;">
                        <div class="progress-bar bg-gradient-success" role="progressbar" style="width: 65%"></div>
                    </div>
                    <p class="mb-0 mt-2">$58,653,24<span class="float-end text-success">+5.9%</span></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon-2 bg-gradient-purple text-white">
                            <ion-icon name="people-sharp"></ion-icon>
                        </div>
                        <div class="fs-5 ms-auto"><ion-icon name="ellipsis-horizontal-sharp"></ion-icon></div>
                    </div>
                    <h5 class="my-3">Total Faculties</h5>
                    <div class="progress mt-1" style="height: 5px;">
                        <div class="progress-bar bg-gradient-purple" role="progressbar" style="width: 65%"></div>
                    </div>
                    <p class="mb-0 mt-2">87,532,16<span class="float-end text-success">+8.5%</span></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon-2 bg-gradient-branding text-white">
                            <ion-icon name="pie-chart-sharp"></ion-icon>
                        </div>
                        <div class="fs-5 ms-auto"><ion-icon name="ellipsis-horizontal-sharp"></ion-icon></div>
                    </div>
                    <h5 class="my-3">Total Detaparment</h5>
                    <div class="progress mt-1" style="height: 5px;">
                        <div class="progress-bar bg-gradient-branding" role="progressbar" style="width: 65%"></div>
                    </div>
                    <p class="mb-0 mt-2">24.3%<span class="float-end text-danger">-5.6%</span></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon-2 bg-gradient-warning text-white">
                            <ion-icon name="logo-facebook" role="img" class="md hydrated"
                                aria-label="logo facebook"></ion-icon>
                        </div>
                        <div class="fs-5 ms-auto"><ion-icon name="ellipsis-horizontal-sharp"></ion-icon></div>
                    </div>
                    <h5 class="my-3">Followers</h5>
                    <div class="progress mt-1" style="height: 5px;">
                        <div class="progress-bar bg-gradient-warning" role="progressbar" style="width: 65%"></div>
                    </div>
                    <p class="mb-0 mt-2">1,328<span class="float-end text-success">+7.6%</span></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card radius-10 overflow-hidden w-100">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <h6 class="mb-0">Monthly Orders</h6>
                <div class="dropdown options ms-auto">
                    <div class="dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
                        <ion-icon name="ellipsis-horizontal-sharp"></ion-icon>
                    </div>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                        <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                        <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
                    </ul>
                </div>
            </div>
            <div class="chart-container1">
                <canvas id="chart1"></canvas>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-12 col-xl-6">
            <div class="card radius-10 w-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <h6 class="mb-0">Top Categories</h6>
                        <div class="dropdown options ms-auto">
                            <div class="dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
                                <ion-icon name="ellipsis-horizontal-sharp"></ion-icon>
                            </div>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                                <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                                <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="row row-cols-1 row-cols-md-2 g-3 mt-2 align-items-center">
                        <div class="col-lg-7 col-xl-7 col-xxl-8">
                            <div class="chart-container6">
                                <div class="piechart-legend">
                                    <h2 class="mb-1">68%</h2>
                                    <h6 class="mb-0">Total Sales</h6>
                                </div>
                                <canvas id="chart2"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-5 col-xl-5 col-xxl-4">
                            <div class="">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 d-flex align-items-center gap-2">
                                        <ion-icon name="ellipse-sharp"
                                            class="text-info"></ion-icon><span>Electronics</span>
                                    </li>
                                    <li class="list-group-item border-0 d-flex align-items-center gap-2">
                                        <ion-icon name="ellipse-sharp"
                                            class="text-danger"></ion-icon><span>Furniture</span>
                                    </li>
                                    <li class="list-group-item border-0 d-flex align-items-center gap-2">
                                        <ion-icon name="ellipse-sharp"
                                            class="text-success"></ion-icon><span>Fashion</span>
                                    </li>
                                    <li class="list-group-item border-0 d-flex align-items-center gap-2">
                                        <ion-icon name="ellipse-sharp"
                                            class="text-primary"></ion-icon><span>Accessories</span>
                                    </li>
                                    <li class="list-group-item border-0 d-flex align-items-center gap-2">
                                        <ion-icon name="ellipse-sharp"
                                            class="text-warning"></ion-icon><span>Mobiles</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-12 col-xl-6">
            <div class="card radius-10 w-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <h6 class="mb-0">Customers</h6>
                        <div class="dropdown options ms-auto">
                            <div class="dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
                                <ion-icon name="ellipsis-horizontal-sharp"></ion-icon>
                            </div>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                                <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                                <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="row row-cols-1 row-cols-md-2 g-3 mt-2 align-items-center">
                        <div class="col-lg-7 col-xl-7 col-xxl-7">
                            <div class="chart-container6">
                                <div class="piechart-legend">
                                    <h2 class="mb-1">48K</h2>
                                    <h6 class="mb-0">Customers</h6>
                                </div>
                                <canvas id="chart3"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-5 col-xl-5 col-xxl-5">
                            <div class="">
                                <div class="d-flex align-items-start gap-2 mb-3">
                                    <div><ion-icon name="ellipse-sharp" class="text-info"></ion-icon></div>
                                    <div>
                                        <p class="mb-1">Current Customers</p>
                                        <p class="mb-0 h5">66%</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-2 mb-3">
                                    <div><ion-icon name="ellipse-sharp" class="text-danger"></ion-icon></div>
                                    <div>
                                        <p class="mb-1">New Customers</p>
                                        <p class="mb-0 h5">48%</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <div><ion-icon name="ellipse-sharp" class="text-success"></ion-icon></div>
                                    <div>
                                        <p class="mb-1">Retargeted Customers</p>
                                        <p class="mb-0 h5">25%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!--end row-->


    <div class="card radius-10">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <h6 class="mb-0">Recent Orders</h6>
                <div class="fs-5 ms-auto dropdown">
                    <div class="dropdown-toggle dropdown-toggle-nocaret cursor-pointer" data-bs-toggle="dropdown">
                        <div class="fs-5 ms-auto"><ion-icon name="ellipsis-horizontal-sharp"></ion-icon></div>
                    </div>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Action</a></li>
                        <li><a class="dropdown-item" href="#">Another action</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#">Something else here</a></li>
                    </ul>
                </div>
            </div>
            <div class="table-responsive mt-2">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#ID</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#89742</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="product-box border">
                                        <img src="assets/images/products/11.png" alt="">
                                    </div>
                                    <div class="product-info">
                                        <h6 class="product-name mb-1">Smart Mobile Phone</h6>
                                    </div>
                                </div>
                            </td>
                            <td>2</td>
                            <td>$214</td>
                            <td><span class="badge alert-success">Completed</span></td>
                            <td>Apr 8, 2021</td>
                            <td>
                                <div class="d-flex align-items-center gap-3 fs-6">
                                    <a href="javascript:;" class="text-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="View detail"
                                        aria-label="Views"><ion-icon name="eye-sharp"></ion-icon></a>
                                    <a href="javascript:;" class="text-warning" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="Edit info"
                                        aria-label="Edit"><ion-icon name="pencil-sharp"></ion-icon></a>
                                    <a href="javascript:;" class="text-danger" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="Delete"
                                        aria-label="Delete"><ion-icon name="trash-sharp"></ion-icon></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#68570</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="product-box border">
                                        <img src="assets/images/products/07.png" alt="">
                                    </div>
                                    <div class="product-info">
                                        <h6 class="product-name mb-1">Sports Time Watch</h6>
                                    </div>
                                </div>
                            </td>
                            <td>1</td>
                            <td>$185</td>
                            <td><span class="badge alert-success">Completed</span></td>
                            <td>Apr 9, 2021</td>
                            <td>
                                <div class="d-flex align-items-center gap-3 fs-6">
                                    <a href="javascript:;" class="text-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="View detail"
                                        aria-label="Views"><ion-icon name="eye-sharp"></ion-icon></a>
                                    <a href="javascript:;" class="text-warning" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="Edit info"
                                        aria-label="Edit"><ion-icon name="pencil-sharp"></ion-icon></a>
                                    <a href="javascript:;" class="text-danger" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="Delete"
                                        aria-label="Delete"><ion-icon name="trash-sharp"></ion-icon></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#38567</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="product-box border">
                                        <img src="assets/images/products/17.png" alt="">
                                    </div>
                                    <div class="product-info">
                                        <h6 class="product-name mb-1">Women Red Heals</h6>
                                    </div>
                                </div>
                            </td>
                            <td>3</td>
                            <td>$356</td>
                            <td><span class="badge alert-danger">Cancelled</span></td>
                            <td>Apr 10, 2021</td>
                            <td>
                                <div class="d-flex align-items-center gap-3 fs-6">
                                    <a href="javascript:;" class="text-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="View detail"
                                        aria-label="Views"><ion-icon name="eye-sharp"></ion-icon></a>
                                    <a href="javascript:;" class="text-warning" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="Edit info"
                                        aria-label="Edit"><ion-icon name="pencil-sharp"></ion-icon></a>
                                    <a href="javascript:;" class="text-danger" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="Delete"
                                        aria-label="Delete"><ion-icon name="trash-sharp"></ion-icon></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#48572</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="product-box border">
                                        <img src="assets/images/products/04.png" alt="">
                                    </div>
                                    <div class="product-info">
                                        <h6 class="product-name mb-1">Yellow Winter Jacket</h6>
                                    </div>
                                </div>
                            </td>
                            <td>1</td>
                            <td>$149</td>
                            <td><span class="badge alert-success">Completed</span></td>
                            <td>Apr 11, 2021</td>
                            <td>
                                <div class="d-flex align-items-center gap-3 fs-6">
                                    <a href="javascript:;" class="text-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="View detail"
                                        aria-label="Views"><ion-icon name="eye-sharp"></ion-icon></a>
                                    <a href="javascript:;" class="text-warning" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="Edit info"
                                        aria-label="Edit"><ion-icon name="pencil-sharp"></ion-icon></a>
                                    <a href="javascript:;" class="text-danger" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="Delete"
                                        aria-label="Delete"><ion-icon name="trash-sharp"></ion-icon></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#96857</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="product-box border">
                                        <img src="assets/images/products/10.png" alt="">
                                    </div>
                                    <div class="product-info">
                                        <h6 class="product-name mb-1">Orange Micro Headphone</h6>
                                    </div>
                                </div>
                            </td>
                            <td>2</td>
                            <td>$199</td>
                            <td><span class="badge alert-danger">Cancelled</span></td>
                            <td>Apr 15, 2021</td>
                            <td>
                                <div class="d-flex align-items-center gap-3 fs-6">
                                    <a href="javascript:;" class="text-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="View detail"
                                        aria-label="Views"><ion-icon name="eye-sharp"></ion-icon></a>
                                    <a href="javascript:;" class="text-warning" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="Edit info"
                                        aria-label="Edit"><ion-icon name="pencil-sharp"></ion-icon></a>
                                    <a href="javascript:;" class="text-danger" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="Delete"
                                        aria-label="Delete"><ion-icon name="trash-sharp"></ion-icon></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>#96857</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="product-box border">
                                        <img src="assets/images/products/12.png" alt="">
                                    </div>
                                    <div class="product-info">
                                        <h6 class="product-name mb-1">Pro Samsung Laptop</h6>
                                    </div>
                                </div>
                            </td>
                            <td>1</td>
                            <td>$699</td>
                            <td><span class="badge alert-warning">Pending</span></td>
                            <td>Apr 18, 2021</td>
                            <td>
                                <div class="d-flex align-items-center gap-3 fs-6">
                                    <a href="javascript:;" class="text-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="View detail"
                                        aria-label="Views"><ion-icon name="eye-sharp"></ion-icon></a>
                                    <a href="javascript:;" class="text-warning" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="Edit info"
                                        aria-label="Edit"><ion-icon name="pencil-sharp"></ion-icon></a>
                                    <a href="javascript:;" class="text-danger" data-bs-toggle="tooltip"
                                        data-bs-placement="bottom" title="" data-bs-original-title="Delete"
                                        aria-label="Delete"><ion-icon name="trash-sharp"></ion-icon></a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
