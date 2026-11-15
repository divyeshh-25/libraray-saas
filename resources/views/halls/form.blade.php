@extends('layouts.app')
@section('title', $mode === 'edit' ? 'Edit hall' : 'Add hall')
@section('content')
@php $h = $hall ?? null; @endphp
<x-page-header :title="$mode === 'edit' ? 'Edit hall' : 'Add hall'" :back="['Halls', route('halls.index')]" />

{{-- Reached by a direct link/refresh, not the "Add/Edit hall" button. The button opens this same
     form as a right-side AJAX offcanvas instead (see halls/index.blade.php); this full-page
     version is the no-JS / direct-URL fallback so the route always renders something usable. --}}
<div class="card max-w-2xl">
    @include('halls._form')
</div>
@endsection
