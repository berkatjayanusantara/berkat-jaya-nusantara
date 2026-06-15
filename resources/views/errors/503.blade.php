@extends('errors.layout')

@section('title', 'Layanan Sementara Tidak Tersedia')
@section('code', '503')
@section('heading', 'Layanan sementara tidak tersedia')
@section('message', 'Sistem sedang dalam perawatan, server sedang sibuk, atau koneksi layanan sementara bermasalah.')
@section('description', 'Silakan tunggu beberapa saat, lalu coba kembali. Data yang sudah tersimpan sebelumnya tetap aman.')

@section('actions')
<button type="button" onclick="window.location.reload()" class="btn btn-primary">Coba Lagi</button>
<a href="{{ url('/dashboard') }}" class="btn btn-secondary">Ke Dashboard</a>
@endsection