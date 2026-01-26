@extends('layouts.admin')
@section('content')
    <style>
        #tbl_student {
            table-layout: fixed;
            white-space: nowrap;
        }
    </style>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="">แดชบอร์ด</a></li>
                        <li class="breadcrumb-item active">ใบเซ็นชื่อ</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="card card-row card-secondary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fad fa-print"></i> ใบเซ็นชื่อ
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" id="pdf_print_form" name="pdf_print_form" action="{{ route('admin.reports.pdfPrint') }}" target="_blank">
                @csrf
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="col-sm-6 control-label">เลือกศูนย์สอบ</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fad fa-chevron-square-down"></i></span>
                                </div>
                                <select class="form-control select2" name="test_center" id="test_center">
                                    <option value="">เลือก</option>
                                    @foreach ($testCenter as $row)
                                        <option value="{{ $row->test_center }}">{{ $row->test_center }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col-md-3">
                        <div class="form-group">
                            <label class="col-sm-6 control-label">ห้องสอบ</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fad fa-chevron-square-down"></i></span>
                                </div>
                                <select class="form-control select2" name="room" id="room">
                                    <option value="">เลือก</option>
                                    @foreach ($room as $row)
                                        <option value="{{ $row->room }}">{{ $row->room }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div> --}}
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="col-sm-6 control-label">&nbsp;</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                </div>
                                <button type="submit" class="btn btn-danger mr-2" id="btnReport" value="btnReport" {{ count($testCenter) < 1 ? 'disabled':'' }}><i class="fad fa-file-pdf"></i> ออกใบเซ็นชื่อ</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    @parent
    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // $('#btnReport').click(function(e) {
        //     e.preventDefault();

        //     if ($('#test_center').val() == '') {
        //         Swal.fire({
        //             title: "แจ้งเตือน!",
        //             text: "กรุณาระบุศูนย์สอบ!",
        //             icon: "warning"
        //         });
        //         return false;
        //     }

        //     if ($('#room').val() == '') {
        //         Swal.fire({
        //             title: "แจ้งเตือน!",
        //             text: "กรุณาระบุห้องสอบ!",
        //             icon: "warning"
        //         });
        //         return false;
        //     }

        //     return true;
        // });

        $('#pdf_print_form').on('submit', function (e) {

            if ($('#test_center').val() == '') {
                e.preventDefault();
                Swal.fire({
                    title: "แจ้งเตือน!",
                    text: "กรุณาระบุศูนย์สอบ!",
                    icon: "warning"
                });
                return;
            }

            // if ($('#room').val() == '') {
            //     e.preventDefault();
            //     Swal.fire({
            //         title: "แจ้งเตือน!",
            //         text: "กรุณาระบุห้องสอบ!",
            //         icon: "warning"
            //     });
            //     return;
            // }

            // ถ้าผ่านทั้งหมด → submit ปกติ
        });

    </script>
@endsection
