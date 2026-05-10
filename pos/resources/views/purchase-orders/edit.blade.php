@extends('layouts.app')
@section('title', 'Edit PO')
@section('content')
    @livewire('purchase-order-form', ['id' => $purchase_order->id])
@endsection
