<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quizbee Session History</title>
</head>
<body style="margin:0;padding:24px;background:#0a0c18;font-family:Arial,sans-serif;color:#dce7ff;">
  <div style="max-width:680px;margin:0 auto;background:#0e1530;border:2px solid #1e2a50;">
    <div style="height:4px;background:#f0c030;"></div>
    <div style="padding:24px 28px;background:#090d1e;border-bottom:2px solid #f0c030;">
      <h1 style="margin:0 0 8px;color:#f0c030;font-size:22px;">QUIZBEE SESSION HISTORY</h1>
      <p style="margin:0;color:#b4c8ff;">Hello {{ $studentName }}, here are the questions answered in your session and their correct answers.</p>
    </div>
    <div style="padding:20px 28px;">
      @foreach($answers as $index => $answer)
        <div style="margin-bottom:16px;padding:16px;background:#090d1e;border:1px solid #1e2a50;">
          <p style="margin:0 0 8px;color:#f0c030;font-weight:bold;">{{ $index + 1 }}. {{ strtoupper($answer->subject) }}</p>
          <p style="margin:0 0 10px;line-height:1.6;">{{ $answer->question }}</p>
          @if(isset($answer->student_answer_key) && $answer->student_answer_key !== null)
            <p style="margin:0 0 8px;color:{{ $answer->is_correct ? '#8ee6a0' : '#ff9999' }};"><strong>Your answer ({{ $answer->is_correct ? 'correct' : 'incorrect' }}) ({{ $answer->student_answer_key }}):</strong> {{ $answer->student_answer_text }}</p>
          @endif
          <p style="margin:0;color:#8ee6a0;"><strong>Correct answer ({{ $answer->correct_answer_key }}):</strong> {{ $answer->correct_answer_text }}</p>
        </div>
      @endforeach
      <p style="margin-top:24px;color:#8ca9e6;font-size:12px;">This is an automated message. Please do not reply.</p>
    </div>
  </div>
</body>
</html>
