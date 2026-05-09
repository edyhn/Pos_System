@extends('layouts.app')
@section('title', 'Edit Pengguna')
@section('content')
    @livewire('user-form', ['id' => $id])
@endsection
