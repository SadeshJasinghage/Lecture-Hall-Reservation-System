<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','custom auth laravel')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    <style>
        #floating-alert-container .alert {
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        opacity: 1;
        transition: opacity 1s ease-out;
        }
    </style>

  </head>
  <body style="
    background: url('{{ request()->routeIs('Home', 'login', 'userRegister', 'guestRegister', 'adminRegister') ? asset('images/collagehouse.jpg') : asset('images/maths_department.jpg') }}') no-repeat center center fixed;
    background-size: cover;">
  
    @include('include.header')
    @yield('content')

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>