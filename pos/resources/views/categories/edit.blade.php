@extends('layouts.app')
@section('title', 'Edit Kategori')
@section('content')
    @livewire('category-form', ['id' => $category->id])
@endsection
