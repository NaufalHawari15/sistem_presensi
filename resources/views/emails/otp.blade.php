<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            background-color: #00338d; /* Warna korporat (biru tua) */
            color: #ffffff;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px 20px;
        }
        .content p {
            font-size: 16px;
        }
        .otp-code {
            background-color: #f0f0f0;
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            padding: 15px;
            margin: 25px 0;
            letter-spacing: 5px;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #777;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sistem Presensi Perusahaan</h1>
        </div>
        <div class="content">
            <p>Yth. Pengguna,</p>
            <p>Terima kasih telah mendaftar. Untuk menyelesaikan proses registrasi dan memverifikasi alamat email Anda, silakan gunakan kode verifikasi sekali pakai (OTP) berikut:</p>
            
            <div class="otp-code">
                {{ $otp }}
            </div>

            <p>Kode ini berlaku selama 10 menit. Mohon untuk tidak membagikan kode ini kepada siapa pun untuk menjaga keamanan akun Anda.</p>
            <p>Jika Anda tidak merasa melakukan pendaftaran ini, mohon abaikan email ini.</p>
            <br>
            <p>Hormat kami,</p>
            <p><strong>Administrator Sistem</strong></p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Nama Perusahaan Anda. All rights reserved.</p>
            <p>Email ini dibuat secara otomatis. Mohon untuk tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
