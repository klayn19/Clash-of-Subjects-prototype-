<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Account — Clash of Subject</title>
</head>
<body style="margin:0;padding:24px;background:#0a0c18;font-family:Arial,sans-serif;color:#dce7ff;">
  <div style="max-width:600px;margin:0 auto;background:#0e1530;border:2px solid #1e2a50;">
    <div style="height:4px;background:#f0c030;"></div>
    <div style="padding:28px 32px;text-align:center;background:#090d1e;border-bottom:2px solid #f0c030;">
      <h1 style="margin:0 0 8px;color:#f0c030;font-size:22px;">CLASH OF SUBJECT</h1>
      <p style="margin:0;color:#b4c8ff;">TEACHER ACCOUNT</p>
    </div>
    <div style="padding:28px 32px;line-height:1.7;">
      <p>Hello {{ $teacherName }},</p>
      <p>Your teacher account has been created. Your assigned teaching subject is:</p>
      <div style="margin:24px 0;padding:18px;text-align:center;background:#090d1e;border:2px solid #f0c030;color:#f0c030;font-size:22px;font-weight:bold;">
        {{ $subjectName }}
      </div>
      <p>You can now sign in to Clash of Subject using the email address and password provided during registration.</p>
      <p style="margin-top:28px;color:#8ca9e6;font-size:12px;">This is an automated message. Please do not reply.</p>
    </div>
    <div style="padding:16px;text-align:center;background:#090d1e;border-top:1px solid #1e2a50;color:#8ca9e6;font-size:12px;">
      CLASH OF SUBJECT
    </div>
  </div>
</body>
</html>
