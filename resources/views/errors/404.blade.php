@extends('errors.layout')

@section('title', 'Halaman Tidak Ditemukan')
@section('code', '404')
@section('heading', 'Halaman tidak ditemukan')
@section('message', 'Halaman yang Anda buka tidak tersedia atau alamatnya sudah berubah.')
@section('description', 'Silakan periksa kembali alamat halaman, gunakan menu aplikasi, atau kembali ke dashboard.')

@section('actions')
<a href="{{ url('/dashboard') }}" class="btn btn-primary">Ke Dashboard</a>
<a href="{{ url('/') }}" class="btn btn-secondary">Ke Halaman Utama</a>
<button type="button" onclick="window.history.back()" class="btn btn-secondary">Kembali</button>
@endsection