@extends('errors.layout')

@section('title', 'Gangguan Server')
@section('code', isset($exception) && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : '5XX')
@section('heading', 'Terjadi gangguan pada server')
@section('message', 'Sistem sedang mengalami gangguan sehingga permintaan belum dapat diproses.')
@section('description', 'Silakan coba beberapa saat lagi. Jika masalah tetap terjadi, hubungi admin sistem untuk pengecekan.')

@section('actions')
<button type="button" onclick="window.location.reload()" class="btn btn-primary">Muat Ulang</button>
<a href="{{ url('/dashboard') }}" class="btn btn-secondary">Ke Dashboard</a>
@endsection