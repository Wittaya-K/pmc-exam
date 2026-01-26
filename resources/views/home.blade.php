@extends('layouts.admin')
@section('content')
<div class="content">
    <div class="row">
        <div class="container-fluid">

            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            {{-- <h1 class="m-0">แดชบอร์ด</h1> --}}
                        </div><!-- /.col -->
                        {{-- <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">แดชบอร์ด</a></li>
                                <li class="breadcrumb-item active">หน้าแรก</li>
                            </ol>
                        </div> --}}
                        <!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            {{-- <div class="card card-row card-secondary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fad fa-file-chart-pie"></i> หน้าแรก
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="container-fluid">
                            <section class="content">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card card-primary">
                                                <div class="card-body p-0">
                                                    <div id="external-events"></div>
                                                    <div id="calendar"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
    @endsection
    @section('scripts')
    @parent
    <!-- Page specific script -->
    @endsection

