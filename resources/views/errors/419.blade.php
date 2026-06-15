@extends('errors.layout')

@section('title', 'Sesi Kedaluwarsa')
@section('code', '419')
@section('heading', 'Sesi halaman sudah kedaluwarsa')
@section('message', 'Halaman terlalu lama tidak digunakan atau token keamanan sudah tidak valid.')
@section('description', 'Muat ulang halaman, lalu ulangi proses input data. Jika masih gagal, silakan login kembali.')

@section('details')
Data yang belum disimpan kemungkinan perlu diinput ulang. Error ini biasanya terjadi saat form terlalu lama dibiarkan terbuka.
@endsection

@section('actions')
<a href="{{ url()->current() }}" class="btn btn-primary">Muat Ulang</a>
<a href="{{ url('/login') }}" class="btn btn-secondary">Login Ulang</a>
<a href="{{ url('/dashboard') }}" class="btn btn-secondary">Ke Dashboard</a>
@endsection