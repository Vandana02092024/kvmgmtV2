<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Your claim form</title></head>
<body style="margin:0;background:#f4f6f8;font-family:Arial,sans-serif;color:#243247;padding:28px 12px">
<table role="presentation" style="max-width:600px;width:100%;margin:auto;background:#fff;border:1px solid #e1e6ed;border-radius:10px"><tr><td style="padding:32px">
    <p style="font-size:13px;letter-spacing:2px;color:#617084">ZIPS · {{ $storeName }}</p>
    <h1 style="font-size:26px">Complete your claim form</h1>
    <p>{{ $storeName }} has invited you to complete a claim form. Your email address and store are already linked to the form. No account or login is required.</p>
    <p style="padding:16px 0"><a href="{{ $invitationUrl }}" style="display:inline-block;background:#193c63;color:#fff;text-decoration:none;padding:14px 22px;border-radius:6px">Open claim form</a></p>
    <p>This link expires on <strong>{{ $expiresAt }}</strong> and can be submitted once. Keep it private; anyone with this link can open the form.</p>
    <p style="font-size:13px;color:#617084">If the button does not work, copy this complete link into your browser:</p>
    <p style="font-size:12px;word-break:break-all"><a href="{{ $invitationUrl }}">{{ $invitationUrl }}</a></p>
    <p style="font-size:13px;color:#617084">If you did not request this form, you can ignore this email or contact the store.</p>
</td></tr></table></body></html>
