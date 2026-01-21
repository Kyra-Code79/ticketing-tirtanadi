<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tiket Laporan - <?= $item['nomor_tiket'] ?></title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .header { text-align: center; border-bottom: 3px solid #0056b3; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { width: 80px; height: auto; vertical-align: middle; margin-right: 15px; }
        .company-name { font-size: 24px; font-weight: bold; color: #333; display: inline-block; vertical-align: middle; }
        .ticket-section { text-align: center; margin-bottom: 40px; }
        .ticket-label { font-size: 14px; color: #666; uppercase; letter-spacing: 2px; }
        .ticket-number { font-size: 42px; font-weight: 800; color: #0056b3; margin: 10px 0; }
        .timestamp { font-size: 14px; color: #555; }
        
        .details-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .details-table th, .details-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .details-table th { width: 150px; color: #666; font-weight: normal; }
        .details-table td { font-weight: bold; color: #333; }
        
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 10px; color: #aaa; padding: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <?php if($logo_base64): ?>
            <img src="<?= $logo_base64 ?>" class="logo" alt="Logo">
        <?php endif; ?>
        <span class="company-name">PERUMDA TIRTANADI</span>
    </div>

    <div class="ticket-section">
        <div class="ticket-label">BUKTI PENGADUAN GANGGUAN</div>
        <div class="ticket-number"><?= $item['nomor_tiket'] ?></div>
        <div class="timestamp">
            Export: <?= date('d F Y H:i:s') ?>
        </div>
    </div>

    <table class="details-table">
        <tr>
            <th>Nama Pelapor</th>
            <td><?= esc($item['nama_pelapor']) ?></td>
        </tr>
        <tr>
            <th>Waktu Lapor</th>
            <td><?= date('d F Y H:i', strtotime($item['created_at'])) ?></td>
        </tr>
        <tr>
            <th>Lokasi</th>
            <td><?= esc($item['nama_kecamatan']) ?></td>
        </tr>
         <tr>
            <th>Alamat</th>
            <td><?= esc($item['alamat_detail']) ?></td>
        </tr>
        <tr>
            <th>Isi Laporan</th>
            <td><?= esc($item['isi_laporan']) ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?= esc($item['status']) ?></td>
        </tr>
    </table>

    <div class="footer">
        Dokumen ini dibuat secara otomatis oleh Sistem Ticketing Tirtanadi.
    </div>
</body>
</html>
