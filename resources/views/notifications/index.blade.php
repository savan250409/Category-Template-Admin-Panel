@extends('partials.layout')
@section('title', 'Notifications')
@section('container')
    <style>
        .form-card {
            background:#fff; border-radius:.5rem;
            box-shadow:0 .15rem 1.75rem 0 rgba(58,59,69,.15);
            padding:1.5rem; margin-bottom:2rem;
        }
        .status-badge { font-size:.75rem; padding:.35em .7em; border-radius:.4rem; font-weight:600; }
        .status-sent    { background:#1cc88a; color:#fff; }
        .status-failed  { background:#e74a3b; color:#fff; }
        .status-pending { background:#f6c23e; color:#fff; }
        .notif-title { font-weight:700; }
        .notif-desc  { color:#6c757d; font-size:.85rem; }

        /* DataTables sort arrows — plain Unicode so they always render */
        table.dataTable thead th.sorting,
        table.dataTable thead th.sorting_asc,
        table.dataTable thead th.sorting_desc {
            background-image: none !important;
            padding-right: 1.5rem !important;
            position: relative;
            cursor: pointer;
        }
        table.dataTable thead th.sorting::after,
        table.dataTable thead th.sorting_asc::after,
        table.dataTable thead th.sorting_desc::after {
            position: absolute;
            right: .4rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: .78rem;
            line-height: 1;
        }
        table.dataTable thead th.sorting::after      { content: '↕'; color: #adb5bd; }
        table.dataTable thead th.sorting_asc::after  { content: '↑'; color: #0d6efd; font-weight: 700; }
        table.dataTable thead th.sorting_desc::after { content: '↓'; color: #0d6efd; font-weight: 700; }

        /* Hide DataTables controls we don't need — we have our own */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate { display: none !important; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-start flex-wrap mb-4">
            <div>
                <h1 class="page-title text-primary">
                    <i class="bi bi-bell-fill me-2"></i> Notifications
                </h1>
                <p class="page-subtitle mb-0">
                    Push notifications across all apps &mdash; Total: <strong>{{ $total }}</strong>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('notifications.test') }}" class="btn btn-outline-primary">
                    <i class="bi bi-phone me-1"></i> Test a device
                </a>
                <a href="{{ route('notifications.global') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Send Global Notification
                </a>
            </div>
        </div>

        <div class="form-card">
            <form method="GET" class="row g-3 align-items-end mb-3">
                <div class="col-auto" style="flex:0.02 2 auto;">
                    <label class="form-label mb-1">Show</label>
                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                        @foreach ([10, 25, 50, 100] as $n)
                            <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto" style="flex:0.04 2 auto;">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        @foreach (['pending' => 'Pending', 'sent' => 'Sent', 'failed' => 'Failed'] as $val => $label)
                            <option value="{{ $val }}" {{ $status === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control"
                           placeholder="Search title or description...">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table id="notifTable" class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Module</th>
                            <th>Scheduled for</th>
                            <th>Sent at</th>
                            <th>Status</th>
                            <th class="no-sort text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notifications as $n)
                            <tr>
                                <td class="fw-bold">{{ $n->id }}</td>
                                <td>
                                    <div class="notif-title">{{ $n->title }}</div>
                                    <div class="notif-desc">{{ \Illuminate\Support\Str::limit($n->description, 60) }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark">{{ $moduleLabels[$n->module] ?? ($n->module === 'global' ? 'Global' : $n->module) }}</span></td>
                                <td>{{ $n->scheduled_at ? $n->scheduled_at->format('d M Y, h:i A') : '—' }}</td>
                                <td>{{ $n->sent_at ? $n->sent_at->format('d M Y, h:i A') : '—' }}</td>
                                <td>
                                    <span class="status-badge status-{{ $n->status }}">{{ ucfirst($n->status) }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('notifications.show', $n->id) }}" class="btn btn-sm btn-primary" title="View">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No notifications yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Init DataTables for sort arrows only — pagination/search/info are hidden via CSS
            $('#notifTable').DataTable({
                paging:   false,
                searching: false,
                info:     false,
                ordering: true,
                order:    [],           // no default sort — respect server order
                columnDefs: [
                    { orderable: false, targets: -1 }  // Action column not sortable
                ]
            });

            @if (session('success'))
            Swal.fire({ toast:true, position:'top-end', icon:'success',
                title:@json(session('success')), showConfirmButton:false, timer:5000, timerProgressBar:true });
            @endif
        });
    </script>
@endsection
