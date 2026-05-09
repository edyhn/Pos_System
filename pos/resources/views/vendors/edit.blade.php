@extends('layouts.app')
@section('title', 'Edit Vendor')
@section('content')
    @livewire('vendor-form', ['id' => $id])
@endsection
