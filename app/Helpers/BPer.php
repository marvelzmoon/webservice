<?php

namespace App\Helpers;

use App\Models\ReferensiMobilejknBpjs;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Str;

class BPer
{
    public static function validTanggal($tanggal)
    {
        $format = 'Y-m-d';
        $d = DateTime::createFromFormat($format, $tanggal);

        return $d && $d->format($format) === $tanggal;
    }

    public static function tebakHari($tanggal)
    {
        $carbonDate = Carbon::parse($tanggal);

        // Mendapatkan nomor hari (0=Minggu, 1=Senin, ..., 6=Sabtu)
        $dayNumber = $carbonDate->dayOfWeek;

        $hariIndonesia = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        $namaHari = Str::upper($hariIndonesia[$dayNumber]);

        return $namaHari;
    }

    public static function hitungUmur($tanggal_lahir)
    {
        $lahir = Carbon::parse($tanggal_lahir);
        $now   = Carbon::now();

        $diff = $lahir->diff($now);

        if ($diff->y >= 1) {
            // Jika sudah 1 tahun atau lebih → tahun
            return $diff->y . ' Th';
        } elseif ($diff->m >= 1) {
            // Jika kurang dari 1 tahun tapi sudah 1 bulan → bulan
            return $diff->m . ' Bl';
        } else {
            // Jika kurang dari 1 bulan → hari
            return $diff->d . ' Hr';
        }
    }

    public static function hitungUmur1($tanggalLahir, $tanggalDaftar)
    {
        if (!$tanggalLahir || $tanggalLahir == '0000-00-00') {
            return [
                'tahun' => 0,
                'bulan' => 0,
                'hari'  => 0
            ];
        }

        try {
            $dob = Carbon::parse($tanggalLahir);
            $daftar = Carbon::parse($tanggalDaftar);
        } catch (\Exception $e) {
            return [
                'tahun' => 0,
                'bulan' => 0,
                'hari'  => 0
            ];
        }

        // 🔥 Selisih dari tanggal lahir ke tanggal daftar
        $diff = $dob->diff($daftar);

        return [
            'tahun' => $diff->y,
            'bulan' => $diff->m,
            'hari'  => $diff->d,
        ];
    }

    public static function formatUmur($tanggalLahir, $tanggalDaftar)
    {
        $u = self::hitungUmur1($tanggalLahir, $tanggalDaftar);

        return "{$u['tahun']} Th {$u['bulan']} Bl {$u['hari']} Hr";
    }

    public static function formatnoref($noref)
    {
        // $input = $noref;

        // // Hilangkan spasi kiri/kanan
        // $input = trim($input);

        // if (preg_match('/^\d{4}\/\d{2}\/\d{2}\/\d{6}$/', $input)) {
        //     // --------------------------
        //     // Format A: 2025/11/07/000003
        //     // --------------------------

        //     $clean = str_replace('/', '', $input); // "20251107000003"

        //     // Tindakan jika terdeteksi format A
        //     $jenis = 'formatA';
        // } elseif (preg_match('/^\d{14}$/', $input)) {
        //     // --------------------------
        //     // Format B: 20251107000003
        //     // --------------------------

        //     $clean = $input;

        //     // Tindakan jika terdeteksi format B
        //     $jenis = 'formatB';
        // } else {
        //     // --------------------------
        //     // Format tidak valid
        //     // --------------------------
        //     return back()->withErrors(['kode' => 'Format kode tidak dikenali.']);
        // }
    }

    public static function cekNoRef($noref)
    {
        $cek = ReferensiMobilejknBpjs::where('no_rawat', $noref)->first();

        if ($cek) {
            return $cek->nobooking;
        }

        return $noref;
    }
}
