<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <title>Workface</title>
    <link rel="shortcut icon" href="/images/logo.ico" type="image/x-icon"/>
    
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    {{--<link rel="stylesheet" href="/admin-lte/bower_components/bootstrap/dist/css/bootstrap.min.css">--}}
    <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/admin-lte/bower_components/font-awesome/css/font-awesome.min.css">
    {{--<link rel="stylesheet" href="https://cdn.bootcss.com/font-awesome/5.8.1/css/fontawesome.min.css">--}}
    
    <!-- Ionicons -->
    <link rel="stylesheet" href="/admin-lte/bower_components/Ionicons/css/ionicons.min.css">
    {{--<link rel="stylesheet" href="https://cdn.bootcss.com/ionicons/4.5.6/css/ionicons.min.css">--}}
    
    <!-- Theme style -->
    <link rel="stylesheet" href="/admin-lte/dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
          page. However, you can choose any other skin. Make sure you
          apply the skin class to the body tag so the changes take effect. -->
    <link rel="stylesheet" href="/admin-lte/dist/css/skins/skin-blue.min.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Google Font -->
    {{--<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">--}}
</head>
<!--
BODY TAG OPTIONS:
=================
Apply one or more of the following classes to get the
desired effect
|---------------------------------------------------------|
| SKINS         | skin-blue                               |
|               | skin-black                              |
|               | skin-purple                             |
|               | skin-yellow                             |
|               | skin-red                                |
|               | skin-green                              |
|---------------------------------------------------------|
|LAYOUT OPTIONS | fixed                                   |
|               | layout-boxed                            |
|               | layout-top-nav                          |
|               | sidebar-collapse                        |
|               | sidebar-mini                            |
|---------------------------------------------------------|
-->
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    
    <!-- Main Header -->
    @include('admin.layouts._header')

    <!-- Left side column. contains the logo and sidebar -->
    @include('admin.layouts._left')
    
    <!-- Content Wrapper. Contains page content -->
    @yield('content')
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    @include('admin.layouts._footer')

    <!-- Control Sidebar -->
    @include('admin.layouts._tip')
    <!-- /.control-sidebar -->
    <!-- Add the sidebar's background. This div must be placed
    immediately after the control sidebar -->
    <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<!-- REQUIRED JS SCRIPTS -->

<!-- jQuery 3 -->
{{--<script src="/admin-lte/bower_components/jquery/dist/jquery.min.js"></script>--}}
<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
{{--<script src="/admin-lte/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>--}}
<script src="https://cdn.bootcss.com/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<!-- AdminLTE App -->
<script src="/admin-lte/dist/js/adminlte.min.js"></script>

<!-- Optionally, you can add Slimscroll and FastClick plugins.
     Both of these plugins are recommended to enhance the
     user experience. -->
</body>
</html>
