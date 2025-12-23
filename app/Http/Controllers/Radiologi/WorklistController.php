<?php

namespace App\Http\Controllers\Radiologi;

use App\Http\Controllers\Controller;
use App\Models\PermintaanRadiologi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WorklistController extends Controller
{
    public function generate($date = 'now')
    {
        $date = date('Y-m-d', strtotime($date));

        $orders = PermintaanRadiologi::
            join('reg_periksa','reg_periksa.no_rawat','=','permintaan_radiologi.no_rawat')->
            join('pasien','reg_periksa.no_rkm_medis','=','pasien.no_rkm_medis')->
            join('pegawai','pegawai.nik','=','permintaan_radiologi.dokter_perujuk')->
            join('permintaan_pemeriksaan_radiologi','permintaan_pemeriksaan_radiologi.noorder','=','permintaan_radiologi.noorder')->
            join('jns_perawatan_radiologi','jns_perawatan_radiologi.kd_jenis_prw','=','permintaan_pemeriksaan_radiologi.kd_jenis_prw')->
            where('tgl_permintaan', $date)
            // ->where('tgl_sampel', '0000-00-00')
            ->get([
                'permintaan_radiologi.noorder',
                'permintaan_radiologi.tgl_permintaan',
                'permintaan_radiologi.jam_permintaan',
                'permintaan_pemeriksaan_radiologi.kd_jenis_prw',
                'pasien.nm_pasien',
                'pasien.tgl_lahir',
                'pasien.no_rkm_medis',
                'pegawai.nama as perujuk',
                'jns_perawatan_radiologi.nm_perawatan',
            ]);

        if ($orders->isEmpty()) {
            return response()->json([
                'code' => 204,
                'message' => 'Data tidak ditemukan',
            ]);
        }

        $generated = [];

        foreach ($orders as $order) {
            $filename = $this->createWorklist($order);
            $generated[] = $filename;
        }

        return response()->json([
            'code' => 200,
            'message' => 'Worklist berhasil digenerate',
            'count' => count($generated),
            'files' => $generated
        ]);
    }
    private function createWorklist($order)
    {
        $basePath = storage_path('app/worklists');
        if (!is_dir($basePath)) {
            mkdir($basePath, 0775, true);
        }

        // ===== DATA NORMALIZATION =====
        $accession = $order->noorder.'-IO-'.$order->kd_jenis_prw;
        $sopClassUid = '1.2.840.10008.5.1.4.31';
        $sopInstanceUid = $this->generateSopInstanceUidPerOrder($accession);

        $patientName = strtoupper(trim($order->nm_pasien));
        $patientName = implode('^', preg_split('/\s+/', $patientName)); // PN valid

        $gender = ($order->jk === 'L') ? 'M' : 'F';

        $tglLahir = date('Ymd', strtotime($order->tgl_lahir));
        $tglReq   = date('Ymd', strtotime($order->tgl_permintaan));
        $jamReq   = date('His', strtotime($order->jam_permintaan));

        $xmlFile = "{$basePath}/{$accession}.xml";
        $wlFile  = "{$basePath}/{$accession}.wl";

        // ===== XML (xml2dcm-compatible) =====
        $xml = <<<XML
    <?xml version="1.0" encoding="UTF-8"?>
    <data-set>
    <element tag="0008,0005" vr="CS"><value>ISO_IR 100</value></element>
    <element tag="0008,0016" vr="UI"><value>{$sopClassUid}</value></element>
    <element tag="0008,0018" vr="UI"><value>{$sopInstanceUid}</value></element>

    <element tag="0010,0010" vr="PN"><value>{$patientName}</value></element>
    <element tag="0010,0020" vr="LO"><value>{$order->no_rkm_medis}</value></element>
    <element tag="0010,0030" vr="DA"><value>{$tglLahir}</value></element>
    <element tag="0010,0040" vr="CS"><value>{$gender}</value></element>
    <element tag="0008,0090" vr="PN"><value>{$order->perujuk}</value></element>

    <element tag="0008,0050" vr="SH"><value>{$accession}</value></element>

    <sequence tag="0040,0100" vr="SQ">
        <item>
        <element tag="0040,0001" vr="AE"><value>ORTHANC</value></element>
        <element tag="0040,0002" vr="DA"><value>{$tglReq}</value></element>
        <element tag="0040,0003" vr="TM"><value>{$jamReq}</value></element>
        <element tag="0040,0007" vr="LO"><value>{$order->nm_perawatan}</value></element>
        <element tag="0008,0060" vr="CS"><value>CR</value></element>
        </item>
    </sequence>
    </data-set>
    XML;

        file_put_contents($xmlFile, $xml);

        // ===== CONVERT XML → DICOM (DCMTK 3.6.7) =====
        // Explicit VR Little Endian
        $cmd = sprintf(
            'xml2dcm +te %s %s 2>&1',
            escapeshellarg($xmlFile),
            escapeshellarg($wlFile)
        );

        $output = shell_exec($cmd);

        if (!file_exists($wlFile)) {
            \Log::error('Gagal generate MWL', [
                'cmd' => $cmd,
                'output' => $output,
                'xml' => $xml
            ]);
            return null;
        }

        // optional: bersihkan XML
        unlink($xmlFile);

        return basename($wlFile);
    }

    function generateSopInstanceUidPerOrder(string $orderNo): string
    {
        $root = '1.2.826.0.1.3680043.2.1125';

        // crc32 → angka stabil & cepat
        $hash = sprintf('%u', crc32($orderNo));

        return $root . '.' . $hash;
    }

}
