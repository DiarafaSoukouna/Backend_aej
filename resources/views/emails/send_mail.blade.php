<!DOCTYPE html>
<html>
<head>
    <title>{{ $subjectText ?? 'Notification' }}</title>
</head>
<body>
    <h2>{{ $subjectText }}</h2>
    <p>{!! $messageContent !!}</p> {{-- {!! !!} permet du HTML si nécessaire --}}
</body>
</html>