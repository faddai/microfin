<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 27/06/2017
 * Time: 03:12
 */
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <style type="text/css">
            .font-bold { font-weight: bolder }
            .m-b { margin-bottom: 15px; }
            .m-t { margin-top: 15px; }
            .m-l-n { margin-left: -15px }
        </style>
    </head>

    <body style="background: #ffffff;">

    <div id="app">
        <div class="row">
            <div class="col-md-6" style="margin-bottom: 10px">
                <img src="{{ asset('img/bfc.png') }}" alt="{{ config('app.name') }} logo" width="12%"
                     class="pull-left" style="margin-right: 10px;">
                <p style="margin-top: -10px">
                    <span style="font-size: 20px;">{{ config('app.company') }}</span>
                    <br> {{ config('app.address') }}
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h2><strong>@yield('title')</strong></h2>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 m-b m-l-n m-t">
                @yield('summary')
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-body">
                @yield('content')
            </div>
        </div>
        <em>Retrieved at {{ \Carbon\Carbon::now() }} by {{ auth()->user()->getFullName() }}</em>
    </div>

    </body>

</html>