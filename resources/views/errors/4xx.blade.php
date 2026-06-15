@extends('errors.layout')

@section('title', 'Permintaan Tidak Dapat Diproses')
@section('code', isset($exception) && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : '4XX')
@section('heading', 'Permintaan tidak dapat diproses')
@section('message', 'Permintaan halaman atau aksi yang Anda lakukan tidak dapat diproses oleh sistem.')
@section('description', 'Silakan kembali ke dashboard, periksa alamat halaman, atau ulangi proses dari menu aplikasi.')

@section('actions')
<a href="{{ url('/dashboard') }}" class="btn btn-primary">Ke Dashboard</a>
<button type="button" onclick="window.history.back()" class="btn btn-secondary">Kembali</button>
@endsection