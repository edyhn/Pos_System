@extends('layouts.app')
@section('title', 'Edit Produk')
@section('content')
    @livewire('product-form', ['id' => $id])
@endsection
