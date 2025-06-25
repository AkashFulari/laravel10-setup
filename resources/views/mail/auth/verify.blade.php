<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>OTP Verification</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        h1 {
            color: #333333;
        }

        p {
            color: #555555;
            line-height: 1.5;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            background-color: #007BFF;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
        }

        .footer {
            font-size: 12px;
            color: #aaaaaa;
            text-align: center;
            padding: 10px;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <td>
                <div class="container">
                    <p>Hi, {{$userName}},</p>
                    <p>Thank you for signing up with {{ config("services.domain.app_name") }}!</p>
                    <p>To complete your account verification, please use the One-Time Password (OTP) below:</p>
                    <p>Your OTP Code: {{ $data['OTP'] }}</p>
                    <p class="footer">If you didn’t request this code, please ignore this email.</p>
                    <br>
                    <p class="footer">Thanks,</p>
                    <p class="footer">{{ config("services.domain.app_name") }} Team</p>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>