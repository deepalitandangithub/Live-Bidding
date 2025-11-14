@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Actions</h1>
    <a href="{{ route('action.create') }}" class="btn btn-primary mb-3">New Action</a>

    <table class="table table-bordered" id="actions-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Ends At</th>
                <th>Status</th>
                <th>Participants</th>
                <th>Highest Bid</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
@endsection



@push('scripts')
<script>
$(function() {
    $('#actions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("index") }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'title', name: 'title' },
            { data: 'ends_at', name: 'ends_at' },
            { data: 'status', name: 'status' },
            { data: 'participants', name: 'participants' },
            { data: 'highest_bid', name: 'highest_bid', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ]
    });
});
</script>
@endpush