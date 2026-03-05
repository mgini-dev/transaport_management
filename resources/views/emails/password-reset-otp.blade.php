<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f8fafc;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:560px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background:#1b3b86;color:#ffffff;padding:20px 24px;">
                            <h1 style="margin:0;font-size:20px;line-height:1.3;">Password Reset OTP</h1>
                            <p style="margin:8px 0 0 0;font-size:13px;opacity:0.9;">{{ config('app.company_name', config('app.name', 'NMIS')) }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 12px 0;font-size:14px;">Hello {{ $userName ?: 'User' }},</p>
                            <p style="margin:0 0 16px 0;font-size:14px;line-height:1.6;">
                                We received a request to reset your account password. Use the OTP below to continue:
                            </p>

                            <div style="margin:0 0 16px 0;padding:16px;border:1px dashed #1b3b86;border-radius:10px;background:#f1f5f9;text-align:center;">
                                <span style="font-size:32px;letter-spacing:8px;font-weight:700;color:#1b3b86;">{{ $otpCode }}</span>
                            </div>

                            <p style="margin:0 0 12px 0;font-size:13px;color:#334155;">
                                This OTP expires in <strong>{{ $expiresInMinutes }} minutes</strong>.
                            </p>
                            <p style="margin:0 0 0 0;font-size:13px;color:#475569;">
                                If you did not request this reset, ignore this message.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;">
                            This is an automated security message from {{ config('app.company_name', config('app.name', 'NMIS')) }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

