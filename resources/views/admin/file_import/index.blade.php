@extends('layouts.admin')
@section('content')
    <style>
        #tbl_schedule {
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
                        <li class="breadcrumb-item active">นำเข้าไฟล์ข้อมูล</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-lg-12 d-flex align-items-center">
            <a class="btn btn-success mr-2" href="{{ route('admin.file_import.create') }}">
                <i class="fad fa-folder-plus"></i> นำเข้าข้อมูล
            </a>

            <form action="{{ route('admin.file_import.resetStudentImport') }}"
                method="POST" class="m-0">
                @csrf
                <button class="btn btn-danger" onclick="return confirm('ยืนยันการรีเซ็ตศูนย์สอบทั้งหมด ?')">
                    <i class="fad fa-trash-alt"></i> รีเซ็ตข้อมูล
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-secondary">
            <i class="fad fa-stream"></i> รายชื่อผู้เข้าสอบ
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tbl_schedule" style="width: 100%;">
                </table>
            </div>
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


        var tbl_schedule;
        var columnVisible; // ซ่อน column

        show_schedule();

        function show_schedule() {
            if (tbl_schedule) {
                tbl_schedule.destroy();
            }

            tbl_samples = $('#tbl_schedule').DataTable({
                destroy: true,
                pageLength: 25,
                scrollX: true,
                scrollCollapse: false,
                responsive: false,
                autoWidth: false,
                ajax: "{{ route('admin.file_import.list') }}",
                deferRender: true,
                columns: [{
                        className: '',
                        data: 'id',
                        title: '<i class="fad fa-poll-people"></i> รหัสประจำตัวสอบ',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'title_th',
                        title: '<i class="fad fa-poll-people"></i> คำนำหน้าชื่อไทย',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'first_name_th',
                        title: '<i class="fad fa-poll-people"></i> ชื่อไทย',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'last_name_th',
                        title: '<i class="fad fa-poll-people"></i> สกุลไทย',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'school',
                        title: '<i class="fad fa-poll-people"></i> โรงเรียน',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'program_name',
                        title: '<i class="fad fa-poll-people"></i> ระดับการสอบ',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'test_center',
                        title: '<i class="fad fa-poll-people"></i> ศูนย์สอบ',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'payment_status',
                        title: '<i class="fad fa-poll-people"></i> สถานะการชำระเงิน',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'title_en',
                        title: '<i class="fad fa-poll-people"></i> คำนำหน้าชื่ออังกฤษ',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'first_name_en',
                        title: '<i class="fad fa-poll-people"></i> ชื่ออังกฤษ',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'last_name_en',
                        title: '<i class="fad fa-poll-people"></i> สกุลอังกฤษ',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'email',
                        title: '<i class="fad fa-poll-people"></i> เมล์',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'phone',
                        title: '<i class="fad fa-poll-people"></i> โทร',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'classLevel',
                        title: '<i class="fad fa-poll-people"></i> ชั้นการศึกษา',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'level',
                        title: '<i class="fad fa-poll-people"></i> ระดับชั้น',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'school_sub_district',
                        title: '<i class="fad fa-poll-people"></i> ตำบล',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'school_district',
                        title: '<i class="fad fa-poll-people"></i> อำเภอ',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'school_province',
                        title: '<i class="fad fa-poll-people"></i> จังหวัด',
                        orderable: false,
                    },
                ],
                columnDefs: [{
                    width: "300px",
                    targets: "_all"
                }]
            });
        }
    </script>
@endsection
