<?php

return [
    'aturan' => [
        'kode' => 'SIMULASI-KONVENSIONAL-V1',
        'nama' => 'Simulasi koperasi konvensional',
        'metode' => 'flat',
        'jasa_flat_bulanan' => 1.00000,
        'tenor_bulan' => [3, 6, 12],
        'pembulatan' => 'Rupiah terdekat; selisih pokok disesuaikan pada angsuran terakhir',
        'dasar' => 'Asumsi simulasi proyek, bukan kebijakan universal koperasi',
        'sumber' => 'Petunjuk Pelaksanaan SOM Usaha Simpan Pinjam Koperasi, Kemenkop, 2025',
        'referensi' => 'https://jdih.kop.go.id/doc/download/file/'
            .'1770612160_Petunjuk%20Pelaksanaan%20SOM_2025%20%282%29.pdf/t/peraturan',
    ],
];
