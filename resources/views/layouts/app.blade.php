<!DOCTYPE html>
<html lang="en">
<head>
    
    
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-EDCMW69JVD"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-EDCMW69JVD');
</script>
    
    <meta name="google-site-verification" content="fADzVZxLXSWcULUPUHPiNKZwpAqy2osrAnERpUy3Urw" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EcomgstPro')</title>

    <!-- Additional styles can be pushed here -->
    @stack('styles')
</head>
<body>
    @include('layouts.header')

    @yield('content')

    @include('layouts.footer')

    <!-- Additional scripts can be pushed here -->
    @stack('scripts')
</body>
</html>
