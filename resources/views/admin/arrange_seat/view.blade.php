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
                        <li class="breadcrumb-item active">จัดที่นั่งสอบอัตโนมัติ</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="card card-row card-secondary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fad fa-list"></i> การค้นหา
            </h3>
        </div>
        <div class="card-body">
            <form id="search_form" name="search_form">
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="col-sm-6 control-label">ชื่อผู้เข้าสอบ</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fad fa-chevron-square-down"></i></span>
                                </div>
                                <select class="form-control select2" name="fNamelname" id="fNamelname">
                                    <option value="">เลือก</option>
                                    @foreach ($fNamelname as $row)
                                        <option value="{{ $row->first_name_th }},{{ $row->last_name_th }}">
                                            {{ $row->first_name_th }}&nbsp;&nbsp;&nbsp;{{ $row->last_name_th }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col-md-2">
                        <div class="form-group">
                            <label class="col-sm-6 control-label">&nbsp;</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                </div>
                                <button type="button" class="btn btn-primary" id="btnSearch" value="btnSearch"><i
                                        class="fad fa-search"></i> ค้นหา</button>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-secondary">
            <i class="fad fa-stream"></i> ที่นั่งสอบ
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tbl_student" style="width: 100%;">
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

        var tbl_student;

        $('#btnSearch').click(function(e) {
            e.preventDefault();

            if ($('#test_center').val() == '') {
                Swal.fire({
                    title: "แจ้งเตือน!",
                    text: "กรุณาระบุศูนย์สอบ!",
                    icon: "warning"
                });
                return false;
            }

            $.ajax({
                data: $('#search_form').serialize(),
                url: "{{ route('admin.arrange_seat.search') }}",
                type: "POST",
                dataType: 'json',
                success: function(data) {
                    console.log('Success:', data);
                    show_student(data);
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
                    alert('เกิดข้อผิดพลาดในการค้นหาข้อมูล');
                }
            });
        });

        function show_student(data) {
            // ทำลาย DataTable เดิม ถ้ามี
            if (tbl_student) {
                tbl_student.destroy();
            }

            tbl_student = $('#tbl_student').DataTable({
                destroy: true,
                pageLength: 10,
                scrollX: true,
                scrollCollapse: false,
                responsive: false,
                autoWidth: false,
                data: data.data, // หรือ data ตรงๆ ขึ้นอยู่กับโครงสร้างที่ return มา
                deferRender: true,
                order: [
                    [7, 'asc'], // room
                    [8, 'asc'], // seat_no
                ],
                columns: [{
                        className: '',
                        data: 'id',
                        title: '<i class="fad fa-poll-people"></i> รหัสประจำตัวสอบ',
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
                        data: 'classLevel',
                        title: '<i class="fad fa-poll-people"></i> ชั้นการศึกษา',
                        orderable: false,
                    },
                    {
                        className: '',
                        data: 'room',
                        title: '<i class="fad fa-poll-people"></i> ห้องสอบ',
                        orderable: true,
                    },
                    {
                        className: '',
                        data: 'seat_no',
                        title: '<i class="fad fa-poll-people"></i> เลขที่นั่ง',
                        orderable: true,
                        type: 'num'
                    },
                ],
                columnDefs: [{
                    width: "300px",
                    targets: "_all"
                }]
            });
        }

        // ค้นหาตามศูนย์สอบ
        $('#test_center').change(function() {
            var test_center = $('#test_center').val();

            $.ajax({
                data: {
                    test_center: test_center,
                },
                url: "{{ route('admin.arrange_seat.searchStudent') }}",
                type: "POST",
                dataType: 'json',
                success: function(data, textStatus, XmlHttpRequest) {
                    if (XmlHttpRequest.status === 200) {
                        // console.log(test_center);
                        show_student(data);
                        $('#fNamelname').empty().append('<option value="">เลือก</option>');

                        $.each(data.data, function (i, value) {
                            $('#fNamelname').append(
                                `<option value="${value.first_name_th},${value.last_name_th}">
                                    ${value.first_name_th} ${value.last_name_th}
                                </option>`
                            );
                        });

                        $('#fNamelname').trigger('change'); // สำหรับ select2
                    }
                },
                error: function(data) {
                    
                }
            });
        });

        // ค้นหาตามชื่อและศูนย์สอบ
        $('#fNamelname').change(function() {
            var test_center = $('#test_center').val();
            var fNamelname = $('#fNamelname').val();
            console.log(test_center,fNamelname);
            $.ajax({
                data: {
                    test_center:test_center,
                    fNamelname: fNamelname
                },
                url: "{{ route('admin.arrange_seat.getStudent') }}",
                type: "POST",
                dataType: 'json',
                success: function(data, textStatus, XmlHttpRequest) {
                    if (XmlHttpRequest.status === 200) {
                        // console.log(fNamelname);
                        show_student(data);
                    }
                },
                error: function(data) {
                    
                }
            });
        });
    </script>
@endsection
