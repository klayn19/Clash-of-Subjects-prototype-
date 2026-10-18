<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Failing Score Alert</title>
</head>
<body style="margin:0;padding:24px;background:#080b1b;font-family:Arial,sans-serif;color:#fff;">
  <main style="max-width:600px;margin:0 auto;padding:28px;background:#0e1530;border:2px solid #c0392b;">
    <h1 style="margin:0 0 18px;color:#f0c030;font-size:22px;">Student Failing Score Alert</h1>
    <p style="line-height:1.6;">A teacher has sent this notification to let you know that
      <strong>{{ $studentName }}</strong> has a score below the passing threshold of 75%.</p>
    <table style="width:100%;border-collapse:collapse;margin:22px 0;">
      <tr><th style="padding:10px;text-align:left;border-bottom:1px solid #1e2a50;">Subject</th><td style="padding:10px;border-bottom:1px solid #1e2a50;">{{ $subjectName }}</td></tr>
      <tr><th style="padding:10px;text-align:left;border-bottom:1px solid #1e2a50;">Assessment</th><td style="padding:10px;border-bottom:1px solid #1e2a50;">{{ $type }}</td></tr>
      <tr><th style="padding:10px;text-align:left;border-bottom:1px solid #1e2a50;">Quarter</th><td style="padding:10px;border-bottom:1px solid #1e2a50;">{{ $quarter ? 'Quarter ' . $quarter : 'Multiple quarters' }}</td></tr>
      <tr><th style="padding:10px;text-align:left;border-bottom:1px solid #1e2a50;">Score</th><td style="padding:10px;border-bottom:1px solid #1e2a50;color:#ff7770;font-weight:bold;">{{ $correct }} / {{ $total }} ({{ number_format($grade, 2) }}%)</td></tr>
    </table>
    <p style="line-height:1.6;">Please contact the teacher to discuss how to support your student’s progress.</p>
    <p style="margin-top:28px;color:#aab6d3;font-size:12px;">This notification was sent from the Clash of Subject system.</p>
  </main>
</body>
</html>
