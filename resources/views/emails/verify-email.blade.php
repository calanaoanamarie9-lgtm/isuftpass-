<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Your Email - ISUFSTPASS</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f9;-webkit-font-smoothing:antialiased;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f6f9;padding:32px 12px;">
    <tr>
        <td align="center">

            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;font-family:Arial,Helvetica,sans-serif;border-radius:16px;overflow:hidden;background-color:#ffffff;">

                {{-- ================= HEADER ================= --}}
                <tr>
                    <td bgcolor="#071f67" style="background-color:#071f67;padding:36px 32px;text-align:center;">
                        <div style="color:#ffffff;font-size:30px;font-weight:bold;line-height:1;letter-spacing:1px;">
                            ISUFSTPASS
                        </div>
                        <div style="color:#bfdbfe;font-size:12px;margin-top:10px;line-height:1.6;">
                            QR CODE-BASED ONLINE TRANSACTION MANAGEMENT<br>SYSTEM
                        </div>
                    </td>
                </tr>

                {{-- Yellow divider --}}
                <tr>
                    <td bgcolor="#facc15" height="6" style="background-color:#facc15;height:6px;font-size:0;line-height:0;">&nbsp;</td>
                </tr>

                {{-- ================= CONTENT ================= --}}
                <tr>
                    <td style="padding:40px 40px 36px 40px;">

                        <h1 style="margin:0 0 24px 0;font-size:22px;font-weight:bold;color:#071f67;text-align:center;letter-spacing:0.5px;">
                            Verify Your Email
                        </h1>

                        <p style="margin:0 0 16px;font-size:15px;font-weight:bold;color:#111827;">
                            Hello, {{ $userName }}!
                        </p>

                        <p style="margin:0 0 28px;font-size:14px;line-height:1.8;color:#374151;">
                            Welcome to <strong>ISUFSTPASS</strong>. Please verify your email address
                            to activate your account and securely access the system.
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center">
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td bgcolor="#071f67" style="background-color:#071f67;border-radius:12px;">
                                                <a href="{{ $url }}"
                                                   style="display:inline-block;padding:16px 44px;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:bold;text-decoration:none;letter-spacing:1px;">
                                                    &#10003;&nbsp;&nbsp;VERIFY MY EMAIL
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:26px 0 0 0;font-size:13px;line-height:1.8;color:#6b7280;text-align:center;">
                            This verification link will expire for security purposes.
                        </p>

                        <p style="margin:18px 0 0 0;font-size:13px;line-height:1.8;color:#6b7280;text-align:center;">
                            If you did not create an ISUFSTPASS account,<br>
                            you can safely ignore this email.
                        </p>

                    </td>
                </tr>

                {{-- ================= FOOTER ================= --}}
                <tr>
                    <td style="padding:0 40px;">
                        <div style="border-top:1px solid #e5e7eb;"></div>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#f9fafb" style="background-color:#f9fafb;padding:24px 40px;text-align:center;border-top:1px solid #e5e7eb;">
                        <div style="color:#102d5b;font-size:14px;font-weight:bold;">
                            ISUFSTPASS
                        </div>
                        <div style="color:#9ca3af;font-size:11px;margin-top:5px;">
                            Secure &bull; Reliable &bull; Official
                        </div>
                        <div style="color:#9ca3af;font-size:11px;margin-top:10px;line-height:1.7;">
                            Iloilo State University of Fisheries Science<br>and Technology
                        </div>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
