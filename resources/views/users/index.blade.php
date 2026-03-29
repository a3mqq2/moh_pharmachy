@extends('layouts.app')

@section('title', __('users.users'))

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        {{ __('users.user_management') }}
                        <span class="badge bg-primary ms-2">{{ $users->total() }}</span>
                    </h5>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> {{ __('users.add_user') }}
                    </a>
                </div>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="{{ __('users.search_placeholder') }}" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">{{ __('users.all_statuses') }}</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('general.active') }}</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('general.inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="role" class="form-select">
                                <option value="">{{ __('users.all_roles') }}</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                        {{ $role->display_name ?? $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="sort" class="form-select">
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>{{ __('users.latest') }}</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>{{ __('users.oldest') }}</option>
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>{{ __('general.name') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-search me-1"></i> {{ __('general.search') }}
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i> {{ __('users.reset') }}
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-primary">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">{{ __('users.user') }}</th>
                                <th width="20%">{{ __('general.email') }}</th>
                                <th width="15%">{{ __('general.role') }}</th>
                                <th width="10%">{{ __('general.status') }}</th>
                                <th width="10%">{{ __('general.created_at') }}</th>
                                <th width="15%" class="text-center">{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;font-size:14px;">
                                                {{ strtoupper(mb_substr($user->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <strong>{{ $user->name }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                                            {{ $user->email }}
                                        </a>
                                    </td>
                                    <td>
                                        @forelse($user->roles as $role)
                                            <span class="badge bg-info">{{ $role->display_name ?? $role->name }}</span>
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    </td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>{{ __('general.active') }}</span>
                                        @else
                                            <span class="badge bg-danger"><i class="fas fa-times me-1"></i>{{ __('general.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $user->created_at->format('Y/m/d') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-warning" title="{{ __('general.edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.users.toggle', $user) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-{{ $user->is_active ? 'secondary' : 'success' }}" title="{{ $user->is_active ? __('users.deactivate') : __('users.activate') }}">
                                                    <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $user->id }}" title="{{ __('general.delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>{{ __('general.confirm_delete') }}</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center py-4">
                                                        <i class="fas fa-user-times fa-3x text-danger mb-3"></i>
                                                        <p class="mb-1">{{ __('users.confirm_delete_user') }}</p>
                                                        <h5 class="text-primary">{{ $user->name }}</h5>
                                                        <small class="text-muted">{{ $user->email }}</small>
                                                    </div>
                                                    <div class="modal-footer justify-content-center">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            <i class="fas fa-times me-1"></i>{{ __('general.cancel') }}
                                                        </button>
                                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">
                                                                <i class="fas fa-trash me-1"></i>{{ __('general.delete') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                        <h5 class="text-muted">{{ __('users.no_users') }}</h5>
                                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary mt-2">
                                            <i class="fas fa-plus me-1"></i> {{ __('users.add_new_user_btn') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            {{ __('users.showing_results', ['first' => $users->firstItem(), 'last' => $users->lastItem(), 'total' => $users->total()]) }}
                        </div>
                        <div>
                            {{ $users->withQueryString()->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
