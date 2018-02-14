<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 23/04/2017
 * Time: 11:23
 */
?>

{{-- Unauthenticated pages --}}
@if(auth()->guest())
    <div class="app" style="margin-top: 8em;">
        @yield('content')
    </div>
@endif


<!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('libs/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('libs/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('libs/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('libs/numeral/min/numeral.min.js') }}"></script>
    <script src="{{ asset('libs/jquery-stickytabs/jquery.stickytabs.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <script src="{{ asset('js/loan_approval.js') }}"></script>
@stack('more_scripts')
</body>
</html>
