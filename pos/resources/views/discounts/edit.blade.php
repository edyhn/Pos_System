@extends('layouts.app')
@section('title', 'Edit Diskon')
@section('content')
    @livewire('discount-form', ['discount' => $discount])
@endsection
