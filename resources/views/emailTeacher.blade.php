<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Credentials</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 16px;
            line-height: 1.5;
        }

        .container {
            margin: 0 auto;
            max-width: 800px;
            padding: 20px;
        }

        .logo {
            display: block;
            margin: 0 auto;
            max-width: 150px;
        }

        h1 {
            text-align: center;
            margin-top: 0;
        }

        p {
            margin-bottom: 20px;
        }

        .credentials {
            background-color: #f1f1f1;
            border-radius: 10px;
            padding: 20px;
        }

        .credential-label {
            font-weight: bold;
        }

        .credential-value {
            font-weight: normal;
        }

        .signature {
            text-align: center;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- <img src="{{ asset('path/to/your/logo.png') }}" alt="Your Logo" class="logo"> --}}
        <h1>Login Credentials</h1>
        <p>Dear {{ $teacherName }},</p>
        <p>Your login credentials for our website are:</p>
        <div class="credentials">
            <p><span class="credential-label">Email:</span> <span class="credential-value">{{ $teacherEmail }}</span></p>
            <p><span class="credential-label">Code:</span> <span class="credential-value">{{ $teacherCode }}</span></p>
            <p><span class="credential-label">Password:</span> <span class="credential-value">{{ $teacherPassword }}</span></p>
        </div>
        {{-- <p>If you have any trouble logging in, please contact us at <a href="mailto:info@example.com">info@example.com</a>.</p> --}}
        <p class="signature">Best regards,<br>{{ config('app.name') }}</p>
    </div>
</body>
</html>
