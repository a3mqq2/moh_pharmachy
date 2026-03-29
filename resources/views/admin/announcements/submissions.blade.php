@extends('layouts.app')

@section('title', __('announcements.submissions') . ' - ' . $announcement->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.announcements.index') }}">{{ __('announcements.announcements') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.announcements.show', $announcement) }}">{{ Str::limit($announcement->title, 20) }}</a></li>
    <li class="breadcrumb-item active">{{ __('announcements.submissions') }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>{{ __('announcements.submissions') }}
                <span class="badge bg-primary ms-2">{{ $submissions->total() }}</span>
            </h5>
            <a href="{{ route('admin.announcements.show', $announcement) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i>{{ __('general.back') }}
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($submissions->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                <p>{{ __('announcements.no_submissions') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('announcements.submitted_by') }}</th>
                            <th>{{ __('general.email') }}</th>
                            <th>{{ __('announcements.submitted_at') }}</th>
                            <th>{{ __('general.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($submissions as $submission)
                        <tr>
                            <td>{{ $loop->iteration + ($submissions->currentPage() - 1) * $submissions->perPage() }}</td>
                            <td>{{ $submission->representative->full_name ?? '-' }}</td>
                            <td>{{ $submission->representative->email ?? '-' }}</td>
                            <td>{{ $submission->submitted_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.announcements.submissions.show', [$announcement, $submission]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>{{ __('general.view') }}
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
