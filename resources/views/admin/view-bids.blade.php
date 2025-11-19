@extends('layouts.app')
@section('title', 'View Bids')

@section('content')
<div class="container mt-4">
    <h2>Bids for Action: {{ $action->title ?? 'N/A' }}</h2> 
    <a href="{{ route('index') }}" class="btn btn-secondary mt-3">Back to Actions</a>
    <table class="table table-bordered table-striped mt-3" id="bids-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Bidder Name</th>
                <th>Bid Amount</th>
                <th>Bid Time</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#bids-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('action.bids', $action->id) }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'bidder_name', name: 'bidder_name' },
            { data: 'amount', name: 'amount' },
            { data: 'created_at', name: 'created_at' }
        ],
        order: [[3, 'desc']] 
    });
});
</script>
@endpush
