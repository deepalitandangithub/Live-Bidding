@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
<div class="container text-center mt-5">
    <h1 class="display-4">404</h1>
    <p class="lead">Oops! The page you are looking for does not exist.</p>
    <a href="{{ url('/') }}" class="btn btn-primary mt-3">Go to Home</a>
</div>
@endsection
