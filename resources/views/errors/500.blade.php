@extends('errors.layout')

@section('title', 'Gangguan Sistem')
@section('code', '500')
@section('heading', 'Terjadi gangguan pada sistem')
@section('message', 'Sistem belum dapat memproses permintaan karena terjadi gangguan internal, koneksi server, atau koneksi database.')
@section('description', 'Silakan coba beberapa saat lagi. Jika masalah tetap muncul, hubungi admin sistem untuk pengecekan server dan database.')

@section('details')
Untuk keamanan, detail teknis error tidak ditampilkan kepada pengguna. Detail lengkap tetap bisa dicek developer melalui file log Laravel di folder <strong>storage/logs</strong>.
@endsection

@section('actions')
<button type="button" onclick="window.location.reload()" class="btn btn-primary">Muat Ulang</button>
<a href="{{ url('/dashboard') }}" class="btn btn-secondary">Ke Dashboard</a>
@endsection