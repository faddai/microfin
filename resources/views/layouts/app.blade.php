@include('layouts.partials._header')

    @if(auth()->check())
        <div id="app">
            @include('dashboard.partials._main_nav')
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="title">@yield('title')</h2>
                        <p>@yield('page-description')</p>
                    </div>

                    <div class="col-md-6" style="margin-top: 10px; margin-bottom: 10px">
                        <div class="btn-group pull-right" role="group" aria-label="...">
                            @yield('page-actions')
                        </div>
                    </div>
                </div>

                <div class="filter">
                    @yield('content-filter')
                </div>

                @include('flash::message')

                @if (isset($errors) && count($errors))
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{$error}}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Don't need the dashboard to within a panel --}}
                @if(request()->is('/'))
                    @yield('content')
                @else
                    <div class="panel panel-default">
                        <div class="panel-body">
                            @yield('content')
                        </div>
                    </div>
                @endif

            </div>

            {{-- form used in the approval workflow for loans--}}
            <form action="" method="POST" name="loan-approval-form">
                {{ csrf_field() }}
            </form>
        </div>
        @include('layouts.partials._copyright')
    @endif

@include('layouts.partials._footer')