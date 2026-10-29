<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product API docs</title>
    <link rel="stylesheet" href="{{ asset('vendor/swagger-ui/swagger-ui.css') }}">
</head>
<body>
<div id="swagger-ui" data-spec-url="{{ url('/docs/openapi.yaml') }}"></div>
<script src="{{ asset('vendor/swagger-ui/swagger-ui-bundle.js') }}"></script>
<script src="{{ asset('vendor/swagger-ui/docs.js') }}"></script>
</body>
</html>
