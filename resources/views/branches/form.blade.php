@extends('layouts.app')
@section('title', $mode === 'edit' ? 'Edit branch' : 'Add branch')
@section('content')
@php $b = $branch ?? null; @endphp
<x-page-header :title="$mode === 'edit' ? 'Edit branch' : 'Add branch'" :subtitle="$b ? $b->tenant?->library_name.' · '.$b->name : null" :back="['Branches', route('branches.index')]" />

{{-- Reached by a direct link/refresh, not the "Add/Edit branch" button. The button opens this same
     form as a right-side AJAX offcanvas instead (see branches/index.blade.php); this full-page
     version is the no-JS / direct-URL fallback so the route always renders something usable. --}}
<div class="card max-w-2xl">
    @include('branches._form')
</div>
@endsection
