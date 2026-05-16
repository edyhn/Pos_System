@extends('layouts.app')
@section('title', 'Edit Produk')
@section('content')
    @livewire('product-form', ['id' => $product->id])
@endsection
