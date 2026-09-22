@extends('master.layout.layout')
@section('main_content')
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <h6 class="mb-0 text-uppercase fw-bold"><i class="bi bi-megaphone-fill me-2 text-primary"></i> Value Posts Calendar</h6>
        <a href="{{ route('master.value-posts.add') }}" class="btn btn-primary px-4">
            <i class="bi bi-plus-lg me-1"></i> Add New / Schedule Post
        </a>
    </div>
    <hr />

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card radius-10 border-0 shadow-sm">
        <div class="card-header bg-transparent py-3">
            <ul class="nav nav-pills card-header-pills" id="postViewTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold px-4" id="table-tab" data-bs-toggle="tab" data-bs-target="#table-view" type="button" role="tab">
                        <i class="bi bi-list-ul me-2"></i> Table View
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold px-4" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-view" type="button" role="tab">
                        <i class="bi bi-calendar3 me-2"></i> Schedule Calendar View
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body py-3">
            <div class="tab-content" id="postViewTabsContent">
                {{-- Table View Tab --}}
                <div class="tab-pane fade show active" id="table-view" role="tabpanel">
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Title</th>
                                    <th>Message</th>
                                    <th>Image</th>
                                    <th>Status</th>
                                    <th>Schedule / Sent Date</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($posts as $key => $post)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><strong>{{ $post->title }}</strong></td>
                                        <td>{{ Str::limit($post->message, 80) }}</td>
                                        <td>
                                            @if ($post->image)
                                                <a href="{{ asset('uploads/value-posts/' . $post->image) }}" target="_blank">
                                                    <img src="{{ asset('uploads/value-posts/' . $post->image) }}" width="50" height="50" style="object-fit: cover; border-radius: 4px;">
                                                </a>
                                            @else
                                                <span class="badge bg-secondary font-11">No Image</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($post->status === 'scheduled')
                                                <span class="badge bg-primary font-12"><i class="bi bi-clock-history me-1"></i> Scheduled</span>
                                            @else
                                                <span class="badge bg-success font-12"><i class="bi bi-check-circle me-1"></i> Sent</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($post->status === 'scheduled' && $post->scheduled_at)
                                                <span class="fw-semibold text-primary font-13">{{ $post->scheduled_at->format('d-m-Y') }}</span>
                                            @else
                                                <span class="text-secondary font-13">{{ $post->created_at->format('d-m-Y H:i') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $post->addedBy ? $post->addedBy->name : 'Admin' }}</td>
                                        <td>
                                            @if ($post->status === 'scheduled')
                                                <span class="badge bg-light-primary text-primary font-12"><i class="bi bi-robot me-1"></i> Cron Automated</span>
                                            @else
                                                <span class="badge bg-light-success text-success font-12"><i class="bi bi-check-circle me-1"></i> Sent</span>
                                            @endif
                                            &nbsp;
                                            <a href="{{ route('master.value-posts.delete', $post->id) }}" 
                                               class="btn btn-sm btn-danger" 
                                               title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this Value Post?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Calendar View Tab --}}
                <div class="tab-pane fade py-2" id="calendar-view" role="tabpanel">
                    <div class="d-flex align-items-center justify-content-between mb-3 bg-light p-2 rounded border">
                        <small class="text-secondary font-12 fw-medium">
                            <span class="badge bg-primary me-2">Blue</span> Scheduled Posts (Automated via Cron) &nbsp;&nbsp;|&nbsp;&nbsp;
                            <span class="badge bg-success me-2">Green</span> Sent Posts &nbsp;&nbsp;|&nbsp;&nbsp;
                            💡 <em>Click any date to schedule a post for that day!</em>
                        </small>
                    </div>
                    <div id="calendar" style="min-height: 600px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Post Details Modal for Calendar Events --}}
    <div class="modal fade" id="postModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="postModalTitle">Post Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 d-flex align-items-center justify-content-between">
                        <span id="postModalStatus"></span>
                        <span class="text-secondary font-13" id="postModalTime"></span>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-secondary font-12 d-block mb-1">Message Content:</label>
                        <div class="p-3 bg-light border rounded font-14" style="white-space: pre-wrap;" id="postModalMessage"></div>
                    </div>
                    <div class="mb-3" id="postModalImageDiv" style="display: none;">
                        <label class="fw-bold text-secondary font-12 d-block mb-1">Attached Image:</label>
                        <img id="postModalImage" src="" class="img-fluid rounded border" style="max-height: 250px; object-fit: contain;">
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-between">
                    <a id="postModalDeleteBtn" href="#" class="btn btn-danger btn-sm" onclick="return confirm('Delete this Value Post?')">
                        <i class="bi bi-trash me-1"></i> Delete Post
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom_js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const eventsData = @json($events);

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                themeSystem: 'bootstrap5',
                events: eventsData,
                selectable: true,
                selectHelper: true,
                dateClick: function(info) {
                    // Navigate to add post form with selected date
                    const selectedDate = info.dateStr;
                    window.location.href = "{{ route('master.value-posts.add') }}?date=" + selectedDate;
                },
                eventClick: function(info) {
                    const props = info.event.extendedProps;
                    
                    $('#postModalTitle').text(props.title);
                    $('#postModalMessage').text(props.message);

                    if (props.status === 'scheduled') {
                        $('#postModalStatus').html('<span class="badge bg-primary font-13"><i class="bi bi-clock-history me-1"></i> Scheduled for ' + props.scheduled_at + ' (Cron Automated)</span>');
                    } else {
                        $('#postModalStatus').html('<span class="badge bg-success font-13"><i class="bi bi-whatsapp me-1"></i> Sent to ' + props.sent_count + ' participants</span>');
                    }

                    $('#postModalTime').text('Created: ' + props.created_at + ' by ' + props.created_by);

                    if (props.image) {
                        $('#postModalImage').attr('src', props.image);
                        $('#postModalImageDiv').show();
                    } else {
                        $('#postModalImageDiv').hide();
                    }

                    $('#postModalDeleteBtn').attr('href', props.delete_url);

                    const postModal = new bootstrap.Modal(document.getElementById('postModal'));
                    postModal.show();
                }
            });

            // Render calendar properly when tab is shown
            const calendarTab = document.getElementById('calendar-tab');
            calendarTab.addEventListener('shown.bs.tab', function () {
                calendar.render();
            });
        });
    </script>
@endsection
