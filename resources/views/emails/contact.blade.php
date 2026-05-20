<!DOCTYPE html>
<html>
<head>
    <title>Pesan dari Kontak Agrimart</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Halo Admin AgriMart!</h2>
    <p>Anda menerima pesan baru dari halaman Kontak web. Berikut adalah detailnya:</p>
    
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; width: 120px;">Nama Pengirim</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $data['name'] }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Email</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $data['email'] }}</td>
        </tr>
    </table>

    <h3>Isi Pesan:</h3>
    <div style="background-color: #f9f9f9; padding: 15px; border-left: 4px solid #16a34a;">
        {!! nl2br(e($data['message'])) !!}
    </div>

    <p style="margin-top: 30px; font-size: 12px; color: #888;">
        Anda dapat langsung membalas email ini untuk merespons pelanggan.
    </p>
</body>
</html>