@php
$namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
$alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
$teleponPerusahaan = '(021) 5664892, 5676277';

$pageTitle = trim($__env->yieldContent('title', 'Terjadi Kesalahan'));
$errorCode = trim($__env->yieldContent('code', 'ERROR'));
$heading = trim($__env->yieldContent('heading', 'Terjadi Kesalahan'));
$message = trim($__env->yieldContent('message', 'Maaf, permintaan Anda belum dapat diproses oleh sistem.'));
$description = trim($__env->yieldContent('description', 'Silakan kembali ke dashboard atau muat ulang halaman beberapa saat lagi.'));
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $errorCode }} | {{ $pageTitle }} - Berkat Jaya Nusantara</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.14), transparent 35%),
                linear-gradient(135deg, #f8fafc 0%, #eef2ff 45%, #f9fafb 100%);
        }

        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        .wrapper {
            width: 100%;
            max-width: 760px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 24px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.10);
            overflow: hidden;
        }

        .company-bar {
            display: flex;
            gap: 16px;
            align-items: center;
            padding: 22px 24px;
            border-bottom: 1px solid #e5e7eb;
            background: #ffffff;
        }

        .logo-wrap {
            width: 58px;
            height: 58px;
            border-radius: 16px;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }

        .logo-wrap img {
            max-width: 48px;
            max-height: 48px;
            object-fit: contain;
        }

        .logo-fallback {
            display: none;
            width: 100%;
            height: 100%;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 800;
            color: #1d4ed8;
        }

        .company-name {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            color: #111827;
            letter-spacing: .02em;
        }

        .company-meta {
            margin: 4px 0 0;
            font-size: 13px;
            line-height: 1.5;
            color: #6b7280;
        }

        .content {
            padding: 34px 24px 28px;
            text-align: center;
        }

        .code-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            height: 46px;
            padding: 0 18px;
            border-radius: 999px;
            background: #111827;
            color: #ffffff;
            font-weight: 800;
            letter-spacing: .08em;
            margin-bottom: 18px;
        }

        h1 {
            margin: 0;
            font-size: 28px;
            line-height: 1.25;
            color: #111827;
        }

        .message {
            max-width: 580px;
            margin: 12px auto 0;
            font-size: 15px;
            line-height: 1.7;
            color: #4b5563;
        }

        .description {
            max-width: 580px;
            margin: 8px auto 0;
            font-size: 14px;
            line-height: 1.6;
            color: #6b7280;
        }

        .note {
            max-width: 620px;
            margin: 22px auto 0;
            padding: 14px 16px;
            text-align: left;
            border-radius: 14px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            color: #4b5563;
            font-size: 13px;
            line-height: 1.6;
        }

        .actions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 26px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 16px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: all .15s ease;
            border: 1px solid transparent;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-primary {
            background: #2563eb;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #ffffff;
            color: #374151;
            border-color: #d1d5db;
        }

        .btn-secondary:hover {
            background: #f9fafb;
        }

        .btn-danger {
            background: #dc2626;
            color: #ffffff;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .help {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 13px;
            line-height: 1.6;
        }

        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            margin-top: 18px;
        }

        @media (max-width: 640px) {
            .company-bar {
                align-items: flex-start;
                padding: 18px;
            }

            .content {
                padding: 28px 18px 24px;
            }

            h1 {
                font-size: 24px;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <main class="page">
        <div class="wrapper">
            <section class="card">
                <div class="company-bar">
                    <div class="logo-wrap">
                        <img src="{{ asset('assets/img/logo-bjn.png') }}"
                            alt="Logo Berkat Jaya Nusantara"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="logo-fallback">BJN</div>
                    </div>

                    <div>
                        <p class="company-name">{{ $namaPerusahaan }}</p>
                        <p class="company-meta">
                            {{ $alamatPerusahaan }}<br>
                            Telp: {{ $teleponPerusahaan }}
                        </p>
                    </div>
                </div>

                <div class="content">
                    <div class="code-badge">{{ $errorCode }}</div>

                    <h1>{{ $heading }}</h1>

                    <p class="message">
                        {{ $message }}
                    </p>

                    @if ($description !== '')
                    <p class="description">
                        {{ $description }}
                    </p>
                    @endif

                    @hasSection('details')
                    <div class="note">
                        @yield('details')
                    </div>
                    @endif

                    <div class="actions">
                        @hasSection('actions')
                        @yield('actions')
                        @else
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary">Ke Dashboard</a>
                        <a href="{{ url('/') }}" class="btn btn-secondary">Ke Halaman Utama</a>
                        <button type="button" onclick="window.history.back()" class="btn btn-secondary">Kembali</button>
                        @endif
                    </div>

                    <div class="help">
                        Apabila masalah tetap muncul, hubungi admin sistem dan sampaikan kode error
                        <strong>{{ $errorCode }}</strong> agar bisa diperiksa lebih cepat.
                    </div>
                </div>
            </section>

            <div class="footer">
                &copy; {{ date('Y') }} {{ $namaPerusahaan }}. Sistem Manajemen Administrasi.
            </div>
        </div>
    </main>
</body>

</html>