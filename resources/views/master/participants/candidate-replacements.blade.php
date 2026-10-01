@extends('master.layout.layout')
@section('main_content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Participants</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Candidate Replacements</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-1 text-dark font-weight-bold">Candidate Replacements</h5>
                    <p class="mb-0 text-muted small">Participant-initiated replacement requests ("Same Enrollment, Same Batch, Same Payment").</p>
                </div>
                <div class="btn-group" role="group">
                    <a href="{{ route('master.participants.candidate-replacements', ['status' => 'pending']) }}"
                       class="btn btn-outline-warning btn-sm {{ $status === 'pending' ? 'active' : '' }}">
                        <i class="bx bx-time-five me-1"></i> Pending
                        <span class="badge bg-warning text-dark ms-1">{{ $pendingCount }}</span>
                    </a>
                    <a href="{{ route('master.participants.candidate-replacements', ['status' => 'approved']) }}"
                       class="btn btn-outline-success btn-sm {{ $status === 'approved' ? 'active' : '' }}">
                        <i class="bx bx-check-circle me-1"></i> Approved
                        <span class="badge bg-success ms-1">{{ $approvedCount }}</span>
                    </a>
                    <a href="{{ route('master.participants.candidate-replacements', ['status' => 'rejected']) }}"
                       class="btn btn-outline-danger btn-sm {{ $status === 'rejected' ? 'active' : '' }}">
                        <i class="bx bx-x-circle me-1"></i> Rejected
                        <span class="badge bg-danger ms-1">{{ $rejectedCount }}</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bx bx-error me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>Req #</th>
                            <th>Batch</th>
                            <th>Original Participant</th>
                            <th>Replacement Candidate</th>
                            <th>Transferred Entitlement</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($replacements as $item)
                            <tr>
                                <td>
                                    <span class="badge bg-dark">REP-{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td>
                                    <strong>{{ $item->batch->name ?? 'N/A' }}</strong>
                                    @if($item->batch && $item->batch->course)
                                        <div class="small text-muted">{{ $item->batch->course->name }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($item->originalParticipant)
                                        <div class="fw-bold">{{ $item->originalParticipant->first_name }} {{ $item->originalParticipant->last_name }}</div>
                                        <div class="small text-muted"><i class="bx bx-phone"></i> {{ $item->originalParticipant->mobile }}</div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">{{ $item->candidate_first_name }} {{ $item->candidate_last_name }}</div>
                                    <div class="small text-muted"><i class="bx bx-phone"></i> {{ $item->candidate_mobile }}</div>
                                    @if($item->candidate_email)
                                        <div class="small text-muted"><i class="bx bx-envelope"></i> {{ $item->candidate_email }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-success">₹{{ number_format($item->total_amount_transferred, 2) }}</div>
                                    <div class="small text-muted">
                                        Reg: ₹{{ number_format($item->registration_fee_transferred, 2) }} |
                                        Sessions: {{ count($item->paid_sessions_transferred ?? []) }}
                                    </div>
                                </td>
                                <td>
                                    {{ $item->created_at->format('d-M-Y') }}
                                    <div class="small text-muted">{{ $item->created_at->format('H:i A') }}</div>
                                </td>
                                <td>
                                    @if($item->status === 'pending')
                                        <span class="badge bg-warning text-dark"><i class="bx bx-time"></i> Pending</span>
                                    @elseif($item->status === 'approved')
                                        <span class="badge bg-success"><i class="bx bx-check"></i> Approved</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bx bx-x"></i> Rejected</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#viewModal{{ $item->id }}">
                                        <i class="bx bx-show"></i> View
                                    </button>

                                    @if($item->status === 'pending')
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $item->id }}" title="Approve">
                                            <i class="bx bx-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $item->id }}" title="Reject">
                                            <i class="bx bx-x"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            <!-- View Details Modal -->
                            <div class="modal fade" id="viewModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title">Replacement Details - REP-{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6 border-end">
                                                    <h6 class="text-primary border-bottom pb-2 font-weight-bold">Original Participant</h6>
                                                    <p class="mb-1"><strong>Name:</strong> {{ $item->originalParticipant->first_name ?? '' }} {{ $item->originalParticipant->last_name ?? '' }}</p>
                                                    <p class="mb-1"><strong>Mobile:</strong> {{ $item->originalParticipant->mobile ?? 'N/A' }}</p>
                                                    <p class="mb-1"><strong>Email:</strong> {{ $item->originalParticipant->email ?? 'N/A' }}</p>
                                                    <p class="mb-1"><strong>Batch:</strong> {{ $item->batch->name ?? 'N/A' }}</p>
                                                    <p class="mb-1"><strong>Paid Amount:</strong> ₹{{ number_format($item->total_amount_transferred, 2) }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="text-success border-bottom pb-2 font-weight-bold">Replacement Candidate</h6>
                                                    <p class="mb-1"><strong>Name:</strong> {{ $item->candidate_first_name }} {{ $item->candidate_last_name }}</p>
                                                    <p class="mb-1"><strong>Mobile:</strong> {{ $item->candidate_mobile }}</p>
                                                    <p class="mb-1"><strong>Email:</strong> {{ $item->candidate_email ?? 'N/A' }}</p>
                                                    @if(!empty($item->candidate_data['city']))
                                                        <p class="mb-1"><strong>City:</strong> {{ $item->candidate_data['city'] }}</p>
                                                    @endif
                                                    @if(!empty($item->candidate_data['address']))
                                                        <p class="mb-1"><strong>Address:</strong> {{ $item->candidate_data['address'] }}</p>
                                                    @endif
                                                </div>
                                                <div class="col-12">
                                                    <div class="p-3 bg-light rounded">
                                                        <h6 class="font-weight-bold mb-1">Reason for Replacement:</h6>
                                                        <p class="mb-0 text-muted">{{ $item->reason }}</p>
                                                    </div>
                                                </div>
                                                @if($item->status === 'approved')
                                                    <div class="col-12">
                                                        <div class="alert alert-success mb-0">
                                                            <strong>Approved By:</strong> {{ $item->approvedByUser->name ?? 'Admin' }} on {{ $item->approved_at ? $item->approved_at->format('d-M-Y H:i') : 'N/A' }}
                                                            @if($item->notes)
                                                                <div class="mt-1 small"><strong>Notes:</strong> {{ $item->notes }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @elseif($item->status === 'rejected')
                                                    <div class="col-12">
                                                        <div class="alert alert-danger mb-0">
                                                            <strong>Rejected By:</strong> {{ $item->approvedByUser->name ?? 'Admin' }} on {{ $item->approved_at ? $item->approved_at->format('d-M-Y H:i') : 'N/A' }}
                                                            <div class="mt-1"><strong>Rejection Reason:</strong> {{ $item->rejection_reason }}</div>
                                                            @if($item->notes)
                                                                <div class="mt-1 small"><strong>Notes:</strong> {{ $item->notes }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($item->status === 'pending')
                                <!-- Approve Modal -->
                                <div class="modal fade" id="approveModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('master.participants.approve-candidate-replacement', $item->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title text-white">Approve Candidate Replacement</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-warning">
                                                        <i class="bx bx-info-circle me-1"></i>
                                                        <strong>Same Enrollment, Same Payment Principle:</strong>
                                                        <ul class="mb-0 mt-2 ps-3 small">
                                                            <li>No refund will be generated for <strong>{{ $item->originalParticipant->first_name ?? 'Participant' }}</strong>.</li>
                                                            <li>No duplicate payment will be charged to <strong>{{ $item->candidate_first_name }} {{ $item->candidate_last_name }}</strong>.</li>
                                                            <li>Transferred entitlement: <strong>₹{{ number_format($item->total_amount_transferred, 2) }}</strong>.</li>
                                                            <li>Unused future session QR codes will be regenerated for <strong>{{ $item->candidate_first_name }}</strong>.</li>
                                                            <li>Historical attendance and assignment submissions remain attached to <strong>{{ $item->originalParticipant->first_name ?? 'Original' }}</strong>.</li>
                                                        </ul>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label font-weight-bold">Internal Approval Notes (Optional)</label>
                                                        <textarea name="notes" class="form-control" rows="3" placeholder="Enter any internal administrative notes..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="bx bx-check"></i> Confirm & Approve Replacement
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('master.participants.reject-candidate-replacement', $item->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title text-white">Reject Candidate Replacement</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="text-muted">
                                                        Rejecting this request will keep <strong>{{ $item->originalParticipant->first_name ?? 'the original participant' }}</strong> as the active candidate in Batch <strong>{{ $item->batch->name ?? '' }}</strong>. Nothing will be transferred.
                                                    </p>
                                                    <div class="mb-3">
                                                        <label class="form-label font-weight-bold text-danger">Rejection Reason <span class="text-danger">*</span></label>
                                                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="State why this replacement request is rejected (this reason will be visible to the participant)..." required></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label font-weight-bold">Internal Notes (Optional)</label>
                                                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional internal notes..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="bx bx-x"></i> Reject Request
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bx bx-folder-open font-24 mb-2 d-block"></i>
                                    No candidate replacement requests found for this status.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
