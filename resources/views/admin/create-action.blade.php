@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Create New Action</h1>
    <form action="{{ route('action.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>Ends At</label>
            <input type="datetime-local" name="ends_at" class="form-control" required>
        </div>
        <button class="btn btn-success">Create</button>
    </form>
</div>
@endsection
