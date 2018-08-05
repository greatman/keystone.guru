<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/lib.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    @yield('head')
</head>
<body>
<div id="app">
    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">
                <!-- Collapsed Hamburger -->
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                        data-target="#app-navbar-collapse">
                    <span class="sr-only">{{__('Toggle Navigation')}}</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <!-- Branding Image -->
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav">
                    <li>
                        <a href="{{ route('dungeonroutes') }}">Routes</a>
                    </li>
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="nav navbar-nav navbar-right">
                    <!-- Authentication Links -->
                    @if (Auth::guest())
                        <li><a href="{{ route('login') }}">{{__('Login')}}</a></li>
                        <li><a href="{{ route('register') }}">{{__('Register')}}</a></li>
                    @else
                        <li>
                            <div style="padding: 7px">
                                <a href="{{ route('dungeonroute.new') }}" class="btn btn-success text-white"
                                   role="button"><i class="fas fa-plus"></i> {{__('Create route')}}</a>
                            </div>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                               aria-expanded="false">
                                <div class="username_menu">
                                    <div class="pull-left user_icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="pull-left">
                                        {{ Auth::user()->name }}
                                    </div>
                                    <span class="caret"></span>
                                </div>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                                @if( Auth::user()->can('read-expansions') )
                                    <li><a href="{{ route('admin.expansions') }}">{{__('View expansions')}}</a></li>
                                @endif
                                @if( Auth::user()->can('read-dungeons') )
                                    <li><a href="{{ route('admin.dungeons') }}">{{__('View dungeons')}}</a></li>
                                @endif
                                @if( Auth::user()->can('read-npcs') )
                                    <li><a href="{{ route('admin.npcs') }}">{{__('View NPCs')}}</a></li>
                                @endif
                                <li>
                                    <a href="{{ route('profile.edit') }}">My profile</a>
                                </li>
                                <li>
                                    <a href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        Logout
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                          style="display: none;">
                                        {{ csrf_field() }}
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
    <div class="container<?php echo(isset($wide) && $wide ? "-fluid" : ""); ?>">
        <div class="row">
            <div class="<?php echo(isset($wide) && $wide ? "col-md-12" : "col-md-8 col-md-offset-2"); ?>">
                <div class="panel panel-default ">
                    <div class="panel-heading <?php echo(isset($wide) && $wide ? "panel-heading-wide" : ""); ?>">
                        @yield('header-title')
                    </div>

                    <div class="panel-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container text-center">
        <hr/>
        <div class="row">
            <div class="col-lg-12">
                <div class="col-md-3">
                    <ul class="nav nav-pills nav-stacked">
                        <li><a href="#">About</a></li>
                        <li><a href="#">News</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <ul class="nav nav-pills nav-stacked">
                        <li><a href="#">Product for Mac</a></li>
                        <li><a href="#">Product for Windows</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <ul class="nav nav-pills nav-stacked">
                        <li><a href="#">Help</a></li>
                        <li><a href="#">Presentations</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <ul class="nav nav-pills nav-stacked">
                        <li>
                            <a href="https://">
                                <i class="fab fa-github"> Github</i>
                            </a>
                        </li>
                        <li><a href="#">Developer API</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-lg-12"> <!-- -->
                <ul class="nav nav-pills nav-justified">
                    <li><a href="/">©{{ date('Y') }} {{ Config::get('app.name') }}</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Privacy</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/lib.js') }}"></script>
<!-- Custom last; may require anything from the above -->
<script src="{{ asset('js/custom.js') }}"></script>
@yield('scripts')
</body>
</html>
