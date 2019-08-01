@extends('layouts.app')

@section("content")
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Page Header
                <small>Optional description</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                <li class="active">Here</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content container-fluid">

            <!--------------------------
              | Your Page Content Here |
              -------------------------->
            <a href="test">测试</a>
        </section>
        <div class="container">
            <div class="panel-heading">上传文件</div>
            <form class="form-horizontal" method="POST" action="/api/createEvent" enctype="multipart/form-data">
                {{ csrf_field() }}
                <label for="file">选择文件</label>
                <input id="file" type="file" class="form-control" name="picture" required>
                <input type="text" name="event_name" value="123">
                <input type="datetime-local" name="event_time" >
                <input type="text" name="event_place" value="123">
                <input type="text" name="link" value="123">
                <input type="text" name="type" value="123">
                <input type="text" name="state" value="123">
                <button type="submit" class="btn btn-primary">确定</button>
            </form>
        </div>
        <!-- /.content -->
    </div>
@endsection

