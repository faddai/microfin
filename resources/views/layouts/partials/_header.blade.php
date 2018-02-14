<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 23/04/2017
 * Time: 11:23
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | {{ config('app.name') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/fontawesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/animate.css/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet">
    <style>
        body { font-size: 12px }
    </style>
    @stack('more_styles')
    <script type="text/javascript">
        window.microfin = window.microfin || {'currency': '{{ config('app.currency') }}'};
    </script>
</head>
<body>
