@extends('errors.layout')

@section('title', 'Akses Ditolak')
@section('code', '403')
@section('heading', 'Akses ditolak')
@section('message', 'Anda tidak memiliki izin untuk membuka halaman atau menjalankan aksi tersebut.')
@section('description', 'Gunakan menu yang tersedia di aplikasi. Apabila seharusnya Anda memiliki akses, hubungi admin sistem.')

@section('actions')
<a href="{{ url('/dashboard') }}" class="btn btn-primary">Ke Dashboard</a>
<button type="button" onclick="window.history.back()" class="btn btn-secondary">Kembali</button>
@endsection