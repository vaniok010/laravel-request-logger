<!doctype html>
<html data-bs-theme="{{$settings['theme']}}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('vendor/request-logger/favicon.ico') }}">
    @vite('resources/css/app.scss', 'vendor/request-logger/build')
    @stack('styles')
    <title>Request Logs</title>
</head>
<body>
<div class="container-fluid">
    <div id="requestLogger">
        <router-view></router-view>
    </div>
</div>
@vite('resources/js/app.js', 'vendor/request-logger/build')
<script>
    window.RequestLogger = @json($settings);
</script>
</body>
</html>
