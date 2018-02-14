@extends('layouts.app')
<title>Page not found</title>
@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
            <div class="panel-body text-center">
                <i class="fa fa-warning text-danger" style="font-size: 10em"></i>
                <h4 class="m-b">Sorry, we couldn't find the page you were looking for.</h4>
                <a href="/"><i class="fa fa-home"></i> Click here to go to the main page</a>
            </div>
        </div>
    </div>
</div>
@endsection