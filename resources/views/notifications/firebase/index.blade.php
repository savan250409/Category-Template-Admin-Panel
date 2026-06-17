@extends('partials.layout')
@section('title', 'Firebase Projects')
@section('container')
    <style>
        .fb-head-card {
            background: linear-gradient(135deg, #4e73df 0%, #6f42c1 100%);
            color: #fff; border-radius: .75rem; padding: 1.5rem 1.75rem; margin-bottom: 1.75rem;
            box-shadow: 0 .3rem 1.2rem rgba(78,115,223,.35);
        }
        .fb-head-card h1 { font-size: 1.6rem; font-weight: 700; margin: 0; }
        .fb-head-card p { margin: .35rem 0 0; opacity: .9; font-size: .9rem; max-width: 640px; }
        .fb-count-pill { background: rgba(255,255,255,.2); border-radius: 2rem; padding: .35rem .9rem; font-weight: 600; font-size: .85rem; }

        .fb-card {
            background: #fff; border: 1px solid #e3e6f0; border-radius: .75rem;
            padding: 1.1rem 1.2rem; height: 100%;
            box-shadow: 0 .15rem .6rem rgba(58,59,69,.08);
            transition: box-shadow .15s, transform .15s; position: relative; overflow: hidden;
        }
        .fb-card:hover { box-shadow: 0 .4rem 1.2rem rgba(58,59,69,.18); transform: translateY(-2px); }
        .fb-card.is-default { border-color: #1cc88a; }
        .fb-card.is-default::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #1cc88a; }

        .fb-card-head { display: flex; align-items: center; gap: .75rem; margin-bottom: 1rem; }
        .fb-icon {
            width: 44px; height: 44px; flex: 0 0 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            background: #fff3e0; color: #fd7e14; font-size: 1.3rem;
        }
        .fb-name { font-weight: 700; font-size: 1.02rem; color: #2e3650; line-height: 1.2; }
        .fb-key { font-size: .78rem; color: #e83e8c; }
        .fb-badges { margin-left: auto; display: flex; flex-direction: column; gap: .25rem; align-items: flex-end; }
        .fb-badges .badge { font-size: .68rem; font-weight: 600; }
        .badge-default { background: #1cc88a; color: #fff; }
        .min-w-0 { min-width: 0; flex: 1; }

        .fb-rows { border-top: 1px dashed #e3e6f0; padding-top: .75rem; }
        .fb-row { display: flex; justify-content: space-between; align-items: center; gap: .5rem; padding: .28rem 0; font-size: .85rem; }
        .fb-row > span { color: #858796; flex: 0 0 auto; }
        .fb-row > b { color: #3a3b45; font-weight: 600; text-align: right; max-width: 62%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .fb-row code { color: #3a3b45; }

        .fb-foot { display: flex; gap: .5rem; margin-top: 1rem; }
        .fb-foot .btn { flex: 1; font-weight: 600; }

        .fb-empty {
            text-align: center; padding: 3.5rem 1rem; color: #858796;
            background: #fff; border: 1px dashed #d1d3e2; border-radius: .75rem;
        }
        .fb-empty i { font-size: 2.4rem; color: #c9ccdb; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="fb-head-card d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-fire me-2"></i> Firebase Projects</h1>
                <p>Manage FCM credentials for every app this panel serves. Each notification broadcasts to all active projects' topics.</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="fb-count-pill"><i class="bi bi-collection me-1"></i>{{ $projects->count() }} project{{ $projects->count() === 1 ? '' : 's' }}</span>
                <a href="{{ route('firebase-projects.create') }}" class="btn btn-light fw-semibold">
                    <i class="bi bi-plus-lg me-1"></i> Add Project
                </a>
            </div>
        </div>

        @if ($projects->isEmpty())
            <div class="fb-empty">
                <i class="bi bi-fire d-block mb-2"></i>
                <h5 class="mb-1">No Firebase projects yet</h5>
                <p class="mb-3">Add your first app to start sending notifications.</p>
                <a href="{{ route('firebase-projects.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Add Firebase Project
                </a>
            </div>
        @else
            <div class="row g-3">
                @foreach ($projects as $p)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="fb-card {{ $p->is_default ? 'is-default' : '' }}">
                            <div class="fb-card-head">
                                <div class="fb-icon"><i class="bi bi-fire"></i></div>
                                <div class="min-w-0">
                                    <div class="fb-name text-truncate" title="{{ $p->display_name ?: $p->key }}">{{ $p->display_name ?: $p->key }}</div>
                                    <code class="fb-key">{{ $p->key }}</code>
                                </div>
                                <div class="fb-badges">
                                    @if ($p->is_default)
                                        <span class="badge badge-default"><i class="bi bi-star-fill me-1"></i>DEFAULT</span>
                                    @endif
                                    @if ($p->isUsable())
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">No JSON</span>
                                    @endif
                                </div>
                            </div>

                            <div class="fb-rows">
                                <div class="fb-row"><span>Project ID</span><b title="{{ $p->project_id }}">{{ $p->project_id ?: '—' }}</b></div>
                                <div class="fb-row"><span>Sender ID</span><b title="{{ $p->sender_id }}">{{ $p->sender_id ?: '—' }}</b></div>
                                <div class="fb-row"><span>Topic</span><b title="{{ $p->topic }}"><code>{{ $p->topic ?: '—' }}</code></b></div>
                            </div>

                            <div class="fb-foot">
                                <a href="{{ route('firebase-projects.edit', $p->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-fill me-1"></i> Edit
                                </a>
                                <form action="{{ route('firebase-projects.destroy', $p->id) }}" method="POST" class="js-delete d-grid">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash-fill me-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('success'))
            Swal.fire({ toast:true, position:'top-end', icon:'success',
                title:@json(session('success')), showConfirmButton:false, timer:4000, timerProgressBar:true });
            @endif

            document.querySelectorAll('.js-delete').forEach(function (f) {
                f.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title:'Delete this Firebase project?',
                        text:'Its .env keys and stored service-account file will be removed.',
                        icon:'warning', showCancelButton:true,
                        confirmButtonColor:'#e74a3b', confirmButtonText:'Yes, delete'
                    }).then(r => { if (r.isConfirmed) f.submit(); });
                });
            });
        });
    </script>
@endsection
