@extends('errors.layout')

@section('title', 'Terlalu Banyak Permintaan')
@section('code', '429')
@section('heading', 'Terlalu banyak permintaan')
@section('message', 'Sistem menerima terlalu banyak permintaan dalam waktu singkat.')
@section('description', 'Tunggu beberapa saat, lalu coba akses kembali halaman ini.')

@section('actions')
<button type="button" onclick="window.location.reload()" class="btn btn-primary">Coba Lagi</button>
<a href="{{ url('/dashboard') }}" class="btn btn-secondary">Ke Dashboard</a>
@endsection