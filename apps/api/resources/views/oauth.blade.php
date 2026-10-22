<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <title>{{ config('app.name') }}</title>
    <script>
        window.opener.postMessage(@json($message), @json(config('app.frontend_url')));
        window.close();
    </script>
</head>
<body>
  {{ __('Redirecting...') }}
</body>
</html>