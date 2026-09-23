@extends('master.layout.layout')

@section('main_content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Member Assignment Report</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="javascript:;"><ion-icon name="home-outline"></ion-icon></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('master.batches.list') }}">Batches</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('master.batches.view', $batch->id) }}">{{ $batch->name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Assignment Report</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-success px-4 me-2">
                <i class="bi bi-file-earmark-excel me-1"></i> Export CSV Report
            </a>
            <a href="{{ route('master.batches.view', $batch->id) }}" class="btn btn-outline-secondary px-4">
                Back to Batch
            </a>
        </div>
    </div>
    <hr />

    <!-- Metrics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4">
        <div class="col">
            <div class="card radius-10 border-start border-0 border-3 border-info shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">Total Members</p>
                            <h4 class="my-1 text-info">{{ count($reportData) }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">Total Assignments</p>
                            <h4 class="my-1 text-primary">{{ count($assignmentsList) }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto">
                            <i class="bi bi-journal-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">Submissions Count</p>
                            <h4 class="my-1 text-success">{{ $totalSubmissionsCount }} / {{ $totalAssignmentsPossible }}</h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 border-start border-0 border-3 border-warning shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">Submission Rate</p>
                            <h4 class="my-1 text-warning">
                                {{ $totalAssignmentsPossible > 0 ? round(($totalSubmissionsCount / $totalAssignmentsPossible) * 100, 1) : 0 }}%
                            </h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto">
                            <i class="bi bi-bar-chart-line-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('master.batches.assignment-report', $batch->id) }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label font-weight-bold">Filter by Group</label>
                    <select name="group_id" class="form-select">
                        <option value="">-- All Groups --</option>
                        @foreach ($groups as $group)
                            <option value="{{ $group->id }}" {{ $selected_group_id == $group->id ? 'selected' : '' }}>
                                {{ $group->group_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label font-weight-bold">Filter by Session</label>
                    <select name="schedule_id" class="form-select">
                        <option value="">-- All Sessions --</option>
                        @foreach ($schedules as $sch)
                            <option value="{{ $sch->id }}" {{ $selected_schedule_id == $sch->id ? 'selected' : '' }}>
                                {{ $sch->name }} (Session {{ $sch->session_number }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-filter me-1"></i> Apply Filter
                    </button>
                    @if ($selected_group_id || $selected_schedule_id)
                        <a href="{{ route('master.batches.assignment-report', $batch->id) }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Main Report Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-transparent py-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-uppercase">Member Assignment Progress Matrix</h5>
                <span class="badge bg-light-primary text-primary font-14">
                    Batch: {{ $batch->name }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Member Name</th>
                            <th>Mobile</th>
                            <th>Group</th>
                            <th class="text-center">Submitted / Total</th>
                            <th class="text-center">Marks Obtained</th>
                            <th class="text-center">Overall %</th>
                            @foreach ($assignmentsList as $asgn)
                                <th class="text-center" style="min-width: 180px;">
                                    <span class="d-block font-12 text-muted">Session: {{ $asgn->batchSchedule?->name }}</span>
                                    <span title="{{ $asgn->question }}">Q: {{ Str::limit($asgn->question, 25) }}</span>
                                </th>
                            @endforeach
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reportData as $row)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $row['participant']->first_name }} {{ $row['participant']->last_name }}</strong>
                                </td>
                                <td>{{ $row['participant']->mobile }}</td>
                                <td>
                                    <span class="badge bg-light-info text-info">{{ $row['group_name'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success me-1">{{ $row['submitted_count'] }}</span> /
                                    <span class="badge bg-secondary ms-1">{{ $row['total_assignments'] }}</span>
                                </td>
                                <td class="text-center">
                                    <strong>{{ $row['total_marks_obtained'] }}</strong> / {{ $row['total_max_marks'] }}
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $row['percentage'] >= 75 ? 'bg-success' : ($row['percentage'] >= 50 ? 'bg-warning text-dark' : 'bg-danger') }} p-2">
                                        {{ $row['percentage'] }}%
                                    </span>
                                </td>
                                @foreach ($assignmentsList as $asgn)
                                    @php
                                        $details = $row['assignments'][$asgn->id] ?? null;
                                    @endphp
                                    <td class="text-center">
                                        @if ($details && $details['status'] == 'Submitted')
                                            <span class="badge bg-success mb-1">
                                                <i class="bi bi-check-lg me-1"></i> Submitted
                                            </span>
                                            <br>
                                            <small class="text-dark"><strong>Marks: {{ $details['mark'] }}/{{ $details['max_mark'] }}</strong></small>
                                            @if ($details['file'])
                                                <br>
                                                <a href="{{ asset('uploads/participants/assignment/' . $details['file']) }}" target="_blank" class="btn btn-link btn-sm p-0 text-decoration-none">
                                                    <i class="bi bi-paperclip"></i> File
                                                </a>
                                            @endif
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-lg me-1"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-center">
                                    @if (count($schedules) > 0)
                                        <a href="{{ route('master.participants.view-assignments', ['participant_id' => $row['participant']->id, 'batch_schedule_id' => $schedules->first()->id]) }}"
                                           class="btn btn-outline-primary btn-sm" title="View Detail Session Breakdown">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 8 + count($assignmentsList) }}" class="text-center py-4 text-muted">
                                    No member records found for the selected filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
