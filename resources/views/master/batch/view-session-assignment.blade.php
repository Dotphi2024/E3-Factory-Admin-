@extends('master.layout.layout')
@section('main_content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Session Assignments</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="javascript:;"><ion-icon name="home-outline"></ion-icon></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('master.batches.list') }}">Batches</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('master.batches.view', $batch_schedule->batch_id) }}">{{ $batch_schedule->batch ? $batch_schedule->batch->name : 'Batch' }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $batch_schedule->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('master.batches.view', $batch_schedule->batch_id) }}" class="btn btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i> Back to Batch
            </a>
        </div>
    </div>
    <hr />

    <!-- Auto-Release Schedule Info Card -->
    <div class="card mb-3 border-0 shadow-sm radius-10" style="background: linear-gradient(135deg, #f8f9fc 0%, #eef2f7 100%);">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width: 46px; height: 46px; font-size: 22px;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-dark font-weight-bold">Automated 6:00 PM Assignment Release Policy</h6>
                        <p class="mb-0 text-secondary font-13">
                            Assignments for this session automatically release at <strong>6:00 PM (18:00)</strong> on the day of the session (<strong>{{ $autoReleaseInfo['session_date_formatted'] ?? $batch_schedule->date }}</strong>).
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge {{ $autoReleaseInfo['badge_class'] ?? 'bg-info' }} px-3 py-2 font-13 shadow-sm rounded-pill">
                        @if(!empty($autoReleaseInfo['is_due']))
                            <i class="bi bi-check-circle-fill me-1"></i>
                        @else
                            <i class="bi bi-hourglass-split me-1"></i>
                        @endif
                        {{ $autoReleaseInfo['label'] ?? '6:00 PM Release Policy' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm radius-10">
        <div class="card-header bg-transparent py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="mb-0 text-dark">
                        <i class="bi bi-journal-text text-primary me-2"></i>Assignments for {{ $batch_schedule->name }}
                        <span class="badge bg-light-primary text-primary font-14 ms-2">{{ $batch_schedule->assignments->count() }} Total</span>
                    </h5>
                    <span class="text-muted font-13">Batch: <strong>{{ $batch_schedule->batch ? $batch_schedule->batch->name : '' }}</strong> | Session Date: <strong>{{ $batch_schedule->date }}</strong></span>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2">
                    @can('batch edit')
                        <button type="button" class="btn btn-outline-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#copy-assignments-modal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-copy me-1" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/>
                            </svg>
                            Copy Assignments
                        </button>
                    @endcan
                    @can('batch add')
                        <a href="{{ route('master.batches.add-session-assignment', $batch_schedule->id) }}" class="btn btn-primary btn-sm px-3 shadow-sm">
                            <i class="bi bi-plus-lg me-1"></i> Add New Assignment
                        </a>
                    @endcan
                    @can('batch edit')
                        <a href="{{ route('master.batches.start-all-session-assignment', $batch_schedule->id) }}" class="btn btn-outline-success btn-sm px-3" title="Manually Release/Start All">
                            <i class="bi bi-play-fill me-1"></i> Start All
                        </a>
                        <a href="{{ route('master.batches.stop-all-session-assignment', $batch_schedule->id) }}" class="btn btn-outline-danger btn-sm px-3" title="Manually Stop All">
                            <i class="bi bi-stop-fill me-1"></i> Stop All
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Question</th>
                            <th style="width: 120px;">Option Type</th>
                            <th>Options with Marks</th>
                            <th class="text-center" style="width: 140px;">Status</th>
                            <th class="text-center" style="width: 130px;">File Upload</th>
                            <th style="width: 110px;">Created At</th>
                            <th class="text-center" style="width: 130px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batch_schedule->assignments as $key => $assignment)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $assignment->question }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-light-primary text-primary font-12 text-uppercase">{{ $assignment->option_type }}</span>
                                </td>
                                <td>
                                    @if ($assignment->option_type == 'options')
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($assignment->options as $assignment_option)
                                                <span class="badge bg-light text-dark border">
                                                    {{ $assignment_option->option }} <strong class="text-primary">({{ $assignment_option->mark }} pts)</strong>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted font-12">Manual Text Response</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($assignment->is_started)
                                        <span class="badge bg-success font-12">
                                            <i class="bi bi-check-circle-fill me-1"></i> Released
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark font-12" title="Will auto-release at 6:00 PM on session day">
                                            <i class="bi bi-clock me-1"></i> Scheduled (6 PM)
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($assignment->is_file_upload)
                                        <span class="badge bg-light-success text-success"><i class="bi bi-check-lg me-1"></i> Yes</span>
                                    @else
                                        <span class="badge bg-light-secondary text-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $assignment->created_at->format('d-m-Y') }}</td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        @can('batch edit')
                                            <!-- Duplicate Single Assignment -->
                                            <a href="{{ route('master.batches.duplicate-session-assignment', $assignment->id) }}"
                                               class="text-secondary font-18"
                                               title="Duplicate / Copy this Assignment"
                                               onclick="return confirm('Do you want to duplicate this assignment?')">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-copy" viewBox="0 0 16 16">
                                                    <path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/>
                                                </svg>
                                            </a>

                                            <!-- Edit Assignment -->
                                            <a href="{{ route('master.batches.edit-session-assignment', $assignment->id) }}" class="text-primary font-18" title="Edit Assignment">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2">
                                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                                </svg>
                                            </a>

                                            <!-- Start / Stop Toggle -->
                                            <a href="{{ route('master.batches.start-stop-session-assignment', $assignment->id) }}"
                                                class="{{ $assignment->is_started ? 'text-danger' : 'text-success' }} font-18"
                                                title="{{ $assignment->is_started ? 'Stop Assignment (Manual Override)' : 'Start Assignment (Manual Release)' }}">
                                                @if ($assignment->is_started)
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-stop-circle">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <rect x="9" y="9" width="6" height="6"></rect>
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-play">
                                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                                    </svg>
                                                @endif
                                            </a>
                                        @endcan
                                        @can('batch delete')
                                            <a href="{{ route('master.batches.delete-session-assignment', $assignment->id) }}" class="text-danger font-18" title="Delete Assignment" onclick="return confirm('Are you sure you want to delete this assignment?')">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-delete">
                                                    <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path>
                                                    <line x1="18" y1="9" x2="12" y2="15"></line>
                                                    <line x1="12" y1="9" x2="18" y2="15"></line>
                                                </svg>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <div class="mb-2">
                                        <i class="bi bi-inbox fs-2 text-secondary"></i>
                                    </div>
                                    <p class="mb-2">No assignments found for this session yet.</p>
                                    @can('batch edit')
                                        <button type="button" class="btn btn-outline-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#copy-assignments-modal">
                                            <i class="bi bi-copy me-1"></i> Copy from Another Session
                                        </button>
                                    @endcan
                                    @can('batch add')
                                        <a href="{{ route('master.batches.add-session-assignment', $batch_schedule->id) }}" class="btn btn-primary btn-sm">
                                            <i class="bi bi-plus-lg me-1"></i> Add New Assignment
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Copy Assignments Modal -->
    <div class="modal fade" id="copy-assignments-modal" tabindex="-1" aria-labelledby="copyAssignmentsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold" id="copyAssignmentsModalLabel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-copy text-primary me-2" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/>
                        </svg>
                        Copy Session Assignments
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Nav Tabs for Copy Direction -->
                <ul class="nav nav-tabs nav-fill px-3 pt-2" id="copyTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active font-weight-bold" id="copy-from-tab" data-bs-toggle="tab" data-bs-target="#copy-from-pane" type="button" role="tab">
                            <i class="bi bi-download me-1 text-primary"></i> Copy FROM Another Session (Import Here)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link font-weight-bold" id="copy-to-tab" data-bs-toggle="tab" data-bs-target="#copy-to-pane" type="button" role="tab">
                            <i class="bi bi-upload me-1 text-success"></i> Copy TO Another Session (Export)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="copyTabContent">
                    <!-- Tab 1: Copy FROM Another Session INTO Current Schedule -->
                    <div class="tab-pane fade show active p-3" id="copy-from-pane" role="tabpanel">
                        <form action="{{ route('master.batches.copy-session-assignments', $batch_schedule->id) }}" method="POST" id="copyFromForm">
                            @csrf
                            <input type="hidden" name="target_schedule_id" value="{{ $batch_schedule->id }}">

                            <div class="alert alert-info py-2 px-3 mb-3 font-13">
                                <strong>Target Session:</strong> {{ $batch_schedule->name }} (Batch: {{ $batch_schedule->batch ? $batch_schedule->batch->name : '' }})
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">1. Select Source Batch</label>
                                <select class="form-select" id="copy_from_batch_id" required>
                                    <option value="">-- Choose Source Batch --</option>
                                    @if(isset($allBatches))
                                        @foreach ($allBatches as $b)
                                            <option value="{{ $b->id }}" {{ $b->id == $batch_schedule->batch_id ? 'selected' : '' }}>
                                                {{ $b->name }} ({{ $b->schedules->count() }} Sessions)
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">2. Select Source Session / Schedule</label>
                                <select class="form-select" name="source_schedule_id" id="copy_from_schedule_id" required>
                                    <option value="">-- Select Batch First --</option>
                                </select>
                                <small class="text-muted" id="copy_from_schedule_hint">Select a session containing assignments to copy.</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label font-weight-bold">3. Copy Action Mode</label>
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio" name="action_type" id="mode_append_from" value="append" checked>
                                    <label class="form-check-label" for="mode_append_from">
                                        <strong>Add to existing</strong> (keep current assignments and append new copies)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action_type" id="mode_replace_from" value="replace">
                                    <label class="form-check-label text-danger" for="mode_replace_from">
                                        <strong>Replace all</strong> (delete current assignments in this session and replace with copies)
                                    </label>
                                </div>
                            </div>

                            <div class="modal-footer px-0 pb-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary px-4" id="submitCopyFromBtn">
                                    <i class="bi bi-copy me-1"></i> Copy Assignments Here
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab 2: Copy THIS Schedule's Assignments TO Another Schedule -->
                    <div class="tab-pane fade p-3" id="copy-to-pane" role="tabpanel">
                        <form action="{{ route('master.batches.copy-session-assignments', $batch_schedule->id) }}" method="POST" id="copyToForm">
                            @csrf
                            <input type="hidden" name="source_schedule_id" value="{{ $batch_schedule->id }}">

                            <div class="alert alert-success py-2 px-3 mb-3 font-13">
                                <strong>Source Session:</strong> {{ $batch_schedule->name }} ({{ $batch_schedule->assignments->count() }} assignments available to copy)
                            </div>

                            @if ($batch_schedule->assignments->isEmpty())
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i> This current session has no assignments to copy. Please add assignments first or use the "Copy FROM" tab.
                                </div>
                            @else
                                <div class="mb-3">
                                    <label class="form-label font-weight-bold">1. Select Target Batch</label>
                                    <select class="form-select" id="copy_to_batch_id" required>
                                        <option value="">-- Choose Target Batch --</option>
                                        @if(isset($allBatches))
                                            @foreach ($allBatches as $b)
                                                <option value="{{ $b->id }}">
                                                    {{ $b->name }} ({{ $b->schedules->count() }} Sessions)
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label font-weight-bold">2. Select Target Session / Schedule</label>
                                    <select class="form-select" name="target_schedule_id" id="copy_to_schedule_id" required>
                                        <option value="">-- Select Target Batch First --</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label font-weight-bold">3. Copy Action Mode</label>
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="radio" name="action_type" id="mode_append_to" value="append" checked>
                                        <label class="form-check-label" for="mode_append_to">
                                            <strong>Add to existing</strong> (keep target session's assignments and append new copies)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="action_type" id="mode_replace_to" value="replace">
                                        <label class="form-check-label text-danger" for="mode_replace_to">
                                            <strong>Replace all</strong> (overwrite target session's existing assignments)
                                        </label>
                                    </div>
                                </div>

                                <div class="modal-footer px-0 pb-0">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success px-4" id="submitCopyToBtn">
                                        <i class="bi bi-upload me-1"></i> Copy All to Target Session
                                    </button>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script for Dynamic Batch Schedule Fetching in Modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentScheduleId = {{ $batch_schedule->id }};
            const currentBatchId = {{ $batch_schedule->batch_id }};

            // Pre-load batches data if available
            const batchesData = @json(isset($allBatches) ? $allBatches : []);

            function populateScheduleSelect(batchId, selectElementId, excludeCurrentSchedule = false, filterWithAssignments = false) {
                const selectEl = document.getElementById(selectElementId);
                if (!selectEl) return;

                selectEl.innerHTML = '<option value="">-- Loading Sessions... --</option>';

                const batch = batchesData.find(b => b.id == batchId);
                if (batch && batch.schedules) {
                    renderSchedules(batch.schedules, selectEl, excludeCurrentSchedule, filterWithAssignments);
                } else {
                    // Fetch via AJAX if not in pre-loaded data
                    fetch(`/master/batches/get-batch-sessions-for-copy/${batchId}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.success && data.schedules) {
                                renderSchedules(data.schedules, selectEl, excludeCurrentSchedule, filterWithAssignments);
                            } else {
                                selectEl.innerHTML = '<option value="">-- No Sessions Found --</option>';
                            }
                        })
                        .catch(err => {
                            console.error('Error fetching schedules:', err);
                            selectEl.innerHTML = '<option value="">-- Error Loading Sessions --</option>';
                        });
                }
            }

            function renderSchedules(schedules, selectEl, excludeCurrentSchedule, filterWithAssignments) {
                selectEl.innerHTML = '<option value="">-- Select Session --</option>';

                let count = 0;
                schedules.forEach(sch => {
                    if (excludeCurrentSchedule && sch.id == currentScheduleId) {
                        return;
                    }

                    const asgnCount = sch.assignments_count !== undefined ? sch.assignments_count : (sch.assignments ? sch.assignments.length : 0);
                    const opt = document.createElement('option');
                    opt.value = sch.id;
                    opt.textContent = `${sch.name} (Session #${sch.session_number}) — [${asgnCount} Assignment(s)]`;

                    if (asgnCount === 0 && filterWithAssignments) {
                        opt.textContent += ' (Empty)';
                    }

                    selectEl.appendChild(opt);
                    count++;
                });

                if (count === 0) {
                    selectEl.innerHTML = '<option value="">-- No Other Sessions in this Batch --</option>';
                }
            }

            // Init "Copy From" batch dropdown
            const copyFromBatchSelect = document.getElementById('copy_from_batch_id');
            if (copyFromBatchSelect) {
                copyFromBatchSelect.addEventListener('change', function() {
                    const batchId = this.value;
                    if (batchId) {
                        populateScheduleSelect(batchId, 'copy_from_schedule_id', true, true);
                    } else {
                        document.getElementById('copy_from_schedule_id').innerHTML = '<option value="">-- Select Batch First --</option>';
                    }
                });

                // Auto populate on page load for current batch
                if (copyFromBatchSelect.value) {
                    populateScheduleSelect(copyFromBatchSelect.value, 'copy_from_schedule_id', true, true);
                }
            }

            // Init "Copy To" batch dropdown
            const copyToBatchSelect = document.getElementById('copy_to_batch_id');
            if (copyToBatchSelect) {
                copyToBatchSelect.addEventListener('change', function() {
                    const batchId = this.value;
                    if (batchId) {
                        populateScheduleSelect(batchId, 'copy_to_schedule_id', true, false);
                    } else {
                        document.getElementById('copy_to_schedule_id').innerHTML = '<option value="">-- Select Target Batch First --</option>';
                    }
                });
            }
        });
    </script>
@endsection
