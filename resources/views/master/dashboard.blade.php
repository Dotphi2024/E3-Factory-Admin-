@extends('master.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Dashboard</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="javascript:;"><ion-icon name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Admin Dashboard</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->


    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 row-cols-xxl-5 g-3">
        <div class="col">
            <div class="card radius-10 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="">
                            <p class="mb-1">Total Active Batches</p>
                            <h4 class="mb-0 text-primary">{{ $batches->where('is_active', 1)->count() }}</h4>
                        </div>
                        <div class="ms-auto text-primary fs-2">
                            <ion-icon name="bag-handle-sharp"></ion-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="">
                            <p class="mb-1">Total Courses</p>
                            <h4 class="mb-0 text-danger">{{ $courses->count() }}</h4>
                        </div>
                        <div class="ms-auto text-danger fs-2">
                            <ion-icon name="pie-chart-sharp"></ion-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="">
                            <p class="mb-1">Total Users</p>
                            <h4 class="mb-0 text-success">{{ $users->count() }}</h4>
                        </div>
                        <div class="ms-auto text-success fs-2">
                            <ion-icon name="wallet-sharp"></ion-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="">
                            <p class="mb-1">Total Participants</p>
                            <h4 class="mb-0 text-info">{{ $participants->count() }}</h4>
                        </div>
                        <div class="ms-auto text-info fs-2">
                            <ion-icon name="people-sharp"></ion-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card radius-10 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="">
                            <p class="mb-1">Total Coaches</p>
                            <h4 class="mb-0 text-warning">{{ $participants->where('is_coach', 1)->count() }}</h4>
                        </div>
                        <div class="ms-auto text-warning fs-2">
                            <ion-icon name="chatbubbles-sharp"></ion-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!--end row-->

    {{-- Batch Analytics & Financial Summary Section --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card radius-10 border-0 shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-bar-chart-fill me-2 text-primary"></i> Batch Summary & Financial Analytics</h6>
                        <small class="text-muted font-12">Select a batch to view participant count and financial breakdown</small>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4">
                        <label for="dashboard_batch_select" class="form-label fw-semibold mb-1 text-secondary font-13">Select Batch:</label>
                        <select id="dashboard_batch_select" class="form-select border-primary font-14">
                            <option value="all">-- All Batches --</option>
                            @foreach ($batches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body py-3">
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
                        <div class="col">
                            <div class="p-3 border rounded-3 bg-light-primary text-center">
                                <span class="badge bg-primary mb-2 font-11">Participants</span>
                                <p class="mb-1 text-secondary font-13 fw-medium" id="label_participants_count">Batch Participants Count</p>
                                <h5 class="mb-0 fw-bold text-primary" id="stat_participants_count">0</h5>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 border rounded-3 bg-light-info text-center">
                                <span class="badge bg-info text-dark mb-2 font-11">Total Fees</span>
                                <p class="mb-1 text-secondary font-13 fw-medium">Total Amount</p>
                                <h5 class="mb-0 fw-bold text-info" id="stat_total_amount">₹0.00</h5>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 border rounded-3 bg-light-success text-center">
                                <span class="badge bg-success mb-2 font-11">Collected</span>
                                <p class="mb-1 text-secondary font-13 fw-medium">Paid Amount</p>
                                <h5 class="mb-0 fw-bold text-success" id="stat_paid_amount">₹0.00</h5>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 border rounded-3 bg-light-danger text-center">
                                <span class="badge bg-danger mb-2 font-11">Pending</span>
                                <p class="mb-1 text-secondary font-13 fw-medium">Remaining / Due Amount</p>
                                <h5 class="mb-0 fw-bold text-danger" id="stat_due_amount">₹0.00</h5>
                                <button type="button" id="btn_export_pending_fees" class="btn btn-outline-danger btn-sm font-11 mt-2 w-100 py-1">
                                    <i class="bi bi-file-earmark-arrow-down-fill me-1"></i> Export Pending Fees CSV
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 text-secondary opacity-25">

                    <div class="mb-2">
                        <h6 class="fw-bold text-dark font-14 mb-1"><i class="bi bi-people-fill me-2 text-primary"></i> Reference Source Summary</h6>
                        <small class="text-muted font-12">Breakdown of participant counts by reference</small>
                    </div>

                    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-8 g-2 mt-1" id="reference_breakdown_container">
                        <!-- Dynamically populated reference cards -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom_js')
    <script>
        const batchStats = @json($batchStats);

        function updateBatchStats() {
            const batchId = $('#dashboard_batch_select').val() || 'all';

            const stats = batchStats[batchId] || {
                participants_count: 0,
                total_amount: 0,
                paid_amount: 0,
                due_amount: 0,
                references: {},
                pending_participants: []
            };

            $('#stat_participants_count').text(stats.participants_count.toLocaleString('en-IN'));
            $('#label_participants_count').text('Batch Participants Count');

            $('#stat_total_amount').text('₹' + stats.total_amount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#stat_paid_amount').text('₹' + stats.paid_amount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#stat_due_amount').text('₹' + stats.due_amount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

            const refs = stats.references || {};
            let refHtml = '';

            const refBadgeColors = {
                'Member': 'bg-primary',
                'Coach': 'bg-warning text-dark',
                'Head Coach': 'bg-danger',
                'Offline Orientation': 'bg-info text-dark',
                'Online Orientation': 'bg-success',
                'Social Media': 'bg-primary text-white',
                'Advertisement': 'bg-secondary'
            };

            for (const [refName, refCount] of Object.entries(refs)) {
                const colorClass = refBadgeColors[refName] || 'bg-secondary';

                refHtml += `
                    <div class="col">
                        <div class="p-2 border rounded-3 bg-light text-center h-100 shadow-sm border opacity-75">
                            <span class="badge ${colorClass} font-10 mb-1 text-wrap d-inline-block">${refName}</span>
                            <h6 class="mb-0 fw-bold text-dark font-15">${refCount.toLocaleString('en-IN')}</h6>
                        </div>
                    </div>
                `;
            }
            $('#reference_breakdown_container').html(refHtml);
        }

        $(document).ready(function() {
            updateBatchStats();
            $('#dashboard_batch_select').on('change', function() {
                updateBatchStats();
            });

            // Export Pending Fees CSV button handler
            $('#btn_export_pending_fees').on('click', function() {
                const batchId = $('#dashboard_batch_select').val() || 'all';
                const batchName = $('#dashboard_batch_select option:selected').text().replace(/--/g, '').trim();
                const stats = batchStats[batchId] || {};
                const pendingList = stats.pending_participants || [];

                if (pendingList.length === 0) {
                    alert('No pending fee records found for ' + batchName);
                    return;
                }

                var csv = [];
                csv.push('"Sr. No","Participant Name","Mobile","Batch Name","Reference","Total Amount (INR)","Paid Amount (INR)","Pending Due Amount (INR)","City"');

                pendingList.forEach(function(item, index) {
                    var row = [
                        '"' + (index + 1) + '"',
                        '"' + item.name.replace(/"/g, '""') + '"',
                        '"' + item.mobile + '"',
                        '"' + item.batch_name.replace(/"/g, '""') + '"',
                        '"' + item.reference.replace(/"/g, '""') + '"',
                        '"' + item.total_amount + '"',
                        '"' + item.paid_amount + '"',
                        '"' + item.due_amount + '"',
                        '"' + item.city.replace(/"/g, '""') + '"'
                    ];
                    csv.push(row.join(','));
                });

                var csvFile = new Blob([csv.join("\n")], {type: "text/csv;charset=utf-8;"});
                var downloadLink = document.createElement("a");
                var filename = 'Pending_Fees_' + batchName.replace(/[^a-zA-Z0-9]/g, '_') + '_' + new Date().toISOString().slice(0,10) + '.csv';
                
                downloadLink.download = filename;
                downloadLink.href = window.URL.createObjectURL(csvFile);
                downloadLink.style.display = "none";
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            });
        });
    </script>
@endsection
