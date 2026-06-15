@extends('errors.layout')

@section('title', 'Belum Login')
@section('code', '401')
@section('heading', 'Sesi login diperlukan')
@section('message', 'Anda perlu login terlebih dahulu untuk mengakses halaman ini.')
@section('description', 'Silakan masuk kembali menggunakan akun admin yang terdaftar di sistem.')

@section('actions')
<a href="{{ url('/login') }}" class="btn btn-primary">Login</a>
<a href="{{ url('/') }}" class="btn btn-secondary">Ke Halaman Utama</a>
@endsection