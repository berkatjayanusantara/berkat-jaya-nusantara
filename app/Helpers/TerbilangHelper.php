<?php

/**
 * Konversi angka integer ke teks terbilang Bahasa Indonesia.
 *
 * Penggunaan:
 *   terbilang(1500000)          // → "satu juta lima ratus ribu"
 *   terbilang_rupiah(75000)     // → "tujuh puluh lima ribu rupiah"
 */

if (! function_exists('terbilang')) {
    function terbilang(int $nilai): string
    {
        $nilai = abs($nilai);

        $huruf = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima',
            'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas',
        ];

        if ($nilai < 12) {
            return $huruf[$nilai];
        }
        if ($nilai < 20) {
            return terbilang($nilai - 10) . ' belas';
        }
        if ($nilai < 100) {
            return terbilang((int) floor($nilai / 10)) . ' puluh ' . terbilang($nilai % 10);
        }
        if ($nilai < 200) {
            return 'seratus ' . terbilang($nilai - 100);
        }
        if ($nilai < 1_000) {
            return terbilang((int) floor($nilai / 100)) . ' ratus ' . terbilang($nilai % 100);
        }
        if ($nilai < 2_000) {
            return 'seribu ' . terbilang($nilai - 1_000);
        }
        if ($nilai < 1_000_000) {
            return terbilang((int) floor($nilai / 1_000)) . ' ribu ' . terbilang($nilai % 1_000);
        }
        if ($nilai < 1_000_000_000) {
            return terbilang((int) floor($nilai / 1_000_000)) . ' juta ' . terbilang($nilai % 1_000_000);
        }
        if ($nilai < 1_000_000_000_000) {
            return terbilang((int) floor($nilai / 1_000_000_000)) . ' miliar ' . terbilang($nilai % 1_000_000_000);
        }

        return terbilang((int) floor($nilai / 1_000_000_000_000)) . ' triliun ' . terbilang($nilai % 1_000_000_000_000);
    }
}

if (! function_exists('terbilang_rupiah')) {
    function terbilang_rupiah(int|float $nilai): string
    {
        $raw = trim(preg_replace('/\s+/', ' ', terbilang((int) round($nilai))));

        return ($raw === '' ? 'nol' : $raw) . ' rupiah';
    }
}
