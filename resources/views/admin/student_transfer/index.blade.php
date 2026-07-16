@extends('layouts.admin')
@section('content')
<style>
    /* เพิ่ม CSS เพื่อจัดการตาราง */
    .table-test-center {
        width: 100% !important;
    }

    .table-test-center th,
    .table-test-center td {
        white-space: nowrap;
        padding: 8px;
        vertical-align: middle;
    }

    /* ปรับความกว้างคอลัมน์ */
    .table-test-center th:first-child,
    .table-test-center td:first-child {
        width: 50px;
    }

    .table-test-center th:last-child,
    .table-test-center td:last-child {
        width: 100px;
        text-align: center;
    }

    /* แก้ไข scrollbar */
    .dataTables_wrapper .dataTables_scroll {
        overflow-x: auto;
    }

    .dataTables_wrapper .dataTables_scrollBody {
        overflow-x: auto !important;
    }

</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-0">
            <div class="col-sm-6">
                {{-- <h1 class="m-0">จัดการผู้เข้าสอบ</h1> --}}
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item active">ย้ายศูนย์สอบ</li>
                </ol>
            </div>
        </div>
    </div>
</div>

@can('student_recheck_create')
<div class="row mb-2">
    <div class="col-lg-12">
        <div class="d-flex flex-wrap align-items-stretch gap-2">
            <a class="btn btn-primary d-flex align-items-center mr-2" href="javascript:void(0)" id="createStudent">
                <i class="fas fa-plus"></i> เพิ่มผู้เข้าสอบ
            </a>

            <form method="POST" id="export_file_form" action="{{ route('admin.student_transfer.exportFile') }}">
                @csrf
                <button type="submit" class="btn btn-info d-flex align-items-center h-100 mr-2">
                    <i class="fad fa-download mr-1"></i> ดาวน์โหลด Excel
                </button>
            </form>
        </div>
    </div>
</div>

<div class="card card-row card-secondary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-search"></i> การค้นหา
        </h3>
    </div>
    <div class="card-body">
        <form id="search_form" name="search_form">
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">เลือกศูนย์สอบ</label>
                        <div class="input-group mb-3">
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
                        <label class="control-label">ชื่อผู้เข้าสอบ</label>
                        <div class="input-group mb-3">
                            <select class="form-control select2" name="fNamelname" id="fNamelname">
                                <option value="">เลือก</option>
                                @foreach ($fNamelname as $row)
                                <option value="{{ $row->first_name_th }},{{ $row->last_name_th }}">
                                    {{ $row->first_name_th }} {{ $row->last_name_th }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan

<div class="card">
    <div class="card-header bg-secondary">
        <i class="fad fa-list"></i> รายชื่อผู้เข้าสอบ
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-test-center" id="studentTable">
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal เพิ่ม/แก้ไขข้อมูล -->
<div class="modal fade" id="studentModal" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modelHeading"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="student_form" name="student_form" class="form-horizontal">
                    <input type="hidden" name="id" id="student_id">

                    <div class="card card-row card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fad fa-info-square"></i> ข้อมูลผู้เข้าสอบ รหัสประจำตัว: <p class="text-white d-inline" id="student_id_info"></p>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>ชื่อ (ไทย)</label>
                                        <input type="text" class="form-control" id="first_name_th" name="first_name_th" required>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>นามสกุล (ไทย)</label>
                                        <input type="text" class="form-control" id="last_name_th" name="last_name_th" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>ชื่อ (eng)</label>
                                        <input type="text" class="form-control" id="first_name_en" name="first_name_en" required>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>นามสกุล (eng)</label>
                                        <input type="text" class="form-control" id="last_name_en" name="last_name_en" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>โรงเรียน</label>
                                        <input type="text" class="form-control" id="school" name="school" required>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>ระดับการสอบ</label>
                                        <select name="program_name" id="program_name" class="form-control">
                                            <option value="">เลือก</option>
                                            @foreach ($programName as $item)
                                                <option value="{{ $item->program_name }}">{{ $item->program_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>ชั้นการศึกษา</label>
                                        <select id="classLevel" name="classLevel" required class="form-control">
                                            <option value="">เลือก</option>
                                            @foreach ($classLevel as $item)
                                            <option value="{{ $item->classLevel }}">{{ $item->classLevel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>ระดับชั้น</label>
                                        <select name="level" id="level" class="form-control">
                                            <option value="">เลือก</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                            <option value="6">6</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>เมล</label>
                                        <input type="text" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>โทร</label>
                                        <input type="number" class="form-control" id="phone" name="phone" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-row card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fad fa-map-marker-alt"></i> สถานที่สอบ
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>ศูนย์สอบ</label>
                                        <select id="test_center_input" name="test_center_input" required class="form-control select2">
                                            <option value="">เลือก</option>
                                            @foreach ($testCenter as $item)
                                            <option value="{{ $item->test_center }}">{{ $item->test_center }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>อาคาร</label>
                                        <select id="building" name="building" required class="form-control select2">
                                            <option value="">เลือก</option>
                                            @foreach ($building as $item)
                                            <option value="{{ $item->building }}">{{ $item->building }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>ชั้น</label>
                                        <select id="floor" name="floor" required class="form-control select2">
                                            <option value="">เลือก</option>
                                            @foreach ($floor as $item)
                                            <option value="{{ $item->floor }}">{{ $item->floor }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>ห้อง</label>
                                        <select name="room" id="room" class="form-control select2">
                                            <option value="">เลือก</option>
                                            @foreach ($room as $item)
                                            <option value="{{ $item->room }}">{{ $item->room }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-row card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fad fa-history"></i> ประวัติการย้ายศูนย์สอบ
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover" id="table_student_transfer">
                                    <thead>
                                        <tr>
                                            <th class="text-center">ศูนย์สอบ</th>
                                            <th class="text-center">อาคาร</th>
                                            <th class="text-center">ชั้น</th>
                                            <th class="text-center">ห้อง</th>
                                            <th class="text-center">วันที่ย้าย</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="accordion" hidden>
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h4 class="card-title w-100">
                                    <a class="d-block w-100" data-toggle="collapse" href="#collapseOne" aria-expanded="true">
                                        Collapsible Group Item #1
                                    </a>
                                </h4>
                            </div>
                            <div id="collapseOne" class="collapse show" data-parent="#accordion" style="">
                                <div class="card-body">
                                    Collapsible Group Item #1
                                </div>
                            </div>
                        </div>
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h4 class="card-title w-100">
                                    <a class="d-block w-100" data-toggle="collapse" href="#collapseTwo">
                                        Collapsible Group Item #2
                                    </a>
                                </h4>
                            </div>
                            <div id="collapseTwo" class="collapse" data-parent="#accordion">
                                <div class="card-body">
                                    Collapsible Group Item #3
                                </div>
                            </div>
                        </div>
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h4 class="card-title w-100">
                                    <a class="d-block w-100" data-toggle="collapse" href="#collapseThree">
                                        Collapsible Group Item #3
                                    </a>
                                </h4>
                            </div>
                            <div id="collapseThree" class="collapse" data-parent="#accordion">
                                <div class="card-body">
                                    Collapsible Group Item #3
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-warning" id="saveBtn">
                            <i class="fad fa-exchange"></i> ยืนยันการย้าย
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@parent
<script src="{{ asset('js/sweetalert2@11.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/bootstrap-waitingfor.min.js') }}"></script>
<script type="text/javascript">
    $(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#test_center_input').select2({
            width: '100%',
            dropdownParent: $('#studentModal')
        });

        $('#building').select2({
            width: '100%',
            dropdownParent: $('#studentModal')
        });

        $('#floor').select2({
            width: '100%',
            dropdownParent: $('#studentModal')
        });

        $('#room').select2({
            width: '100%',
            dropdownParent: $('#studentModal')
        });

        $('#studentModal').on('shown.bs.modal', function () {
            $(document).off('focusin.bs.modal');
        });

        var tbl_student;

        function show_student(data) {
            if (tbl_student) {
                tbl_student.destroy();
            }

            tbl_student = $('.table-test-center').DataTable({
                destroy: true
                , pageLength: 25
                , scrollX: true
                , autoWidth: false
                , responsive: false
                , data: data.data
                , columns: [{
                        data: 'id'
                        , title: 'รหัสประจำตัวสอบ'
                        , orderable: false
                        , width: '50px'
                    }
                    , {
                        data: 'first_name_th'
                        , title: 'ชื่อไทย'
                    , }
                    , {
                        data: 'last_name_th'
                        , title: 'สกุลไทย'
                    , }
                    , {
                        data: 'program_name'
                        , title: 'ระดับการสอบ'
                    , }
                    , {
                        data: 'test_center'
                        , title: 'ศูนย์สอบ'
                    , }
                    , {
                        data: 'classLevel'
                        , title: 'ชั้น'
                    , }
                    , {
                        data: null
                        , title: 'จัดการ'
                        , orderable: false
                        , width: '100px'
                        , className: 'text-center'
                        , render: function(data, type, row) {
                            return `
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-info editStudent" data-id="${row.id}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                `;
                        }
                    }
                ]
                , "ordering": false
            , });
        }

        // เมื่อเลือกศูนย์สอบ
        $('#test_center').change(function() {

            $('#fNamelname').val(null).trigger('change.select2');
            var test_center = $(this).val();

            if (!test_center) return;

            loadStudentData();
        });

        // เมื่อเลือกชื่อ
        $('#fNamelname').change(function() {
            loadStudentData();
        });

        // ฟังก์ชั่นโหลดข้อมูลนักเรียน
        function loadStudentData() {
            var test_center = $('#test_center').val();
            var fNamelname = $('#fNamelname').val();

            if (!test_center) {
                Swal.fire({
                    icon: 'warning'
                    , title: 'แจ้งเตือน'
                    , text: 'กรุณาเลือกศูนย์สอบก่อน'
                });
                return;
            }

            var url = "{{ route('admin.student_transfer.searchStudent') }}";
            var requestData = {
                test_center: test_center
            , };

            // ถ้าเลือกชื่อด้วย ให้ใช้ getStudent แทน
            if (fNamelname) {
                url = "{{ route('admin.student_transfer.getStudent') }}";
                requestData.fNamelname = fNamelname;
            }

            $.ajax({
                data: requestData
                , url: url
                , type: "POST"
                , dataType: 'json'
                , success: function(data) {
                    show_student(data);

                    // อัพเดทรายชื่อถ้าไม่ได้เลือกชื่อไว้
                    if (!fNamelname) {
                        $('#fNamelname').empty().append('<option value="">เลือก</option>');
                        $.each(data.data, function(i, value) {
                            $('#fNamelname').append(
                                `<option value="${value.first_name_th},${value.last_name_th}">
                                        ${value.first_name_th} ${value.last_name_th}
                                    </option>`
                            );
                        });
                    }
                }
                , error: function(xhr, status, error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error'
                        , title: 'ผิดพลาด'
                        , text: 'ไม่สามารถโหลดข้อมูลได้'
                    });
                }
            });
        }

        // เพิ่มผู้เข้าสอบ
        $('#createStudent').click(function() {
            $('#student_id').val('');
            $('#student_form').trigger("reset");
            $('#modelHeading').html("เพิ่มผู้เข้าสอบ");
            $('#studentModal').modal('show');
        });

        // แก้ไขผู้เข้าสอบ
        $('body').on('click', '.editStudent', function() {
            var id = $(this).data('id');

            $.get("{{ route('admin.student_transfer.index') }}" + '/' + id + '/edit', function(data) {
                // $('#modelHeading').html("แก้ไขข้อมูลผู้เข้าสอบ");
                $('#studentModal').modal('show');
                $('#student_id').val(data.student.id);
                $('#student_id_info').text(data.student.id);
                $('#first_name_th').val(data.student.first_name_th);
                $('#last_name_th').val(data.student.last_name_th);
                $('#first_name_en').val(data.student.first_name_en);
                $('#last_name_en').val(data.student.last_name_en);
                $('#school').val(data.student.school);
                $('#program_name').val(data.student.program_name);
                $('#test_center_input').val(data.student.test_center).trigger('change');
                $('#classLevel').val(data.student.classLevel);
                $('#level').val(data.student.level);
                $('#email').val(data.student.email);
                $('#phone').val(data.student.phone);
                $('#building').val(data.student.building).trigger('change');
                $('#floor').val(data.student.floor).trigger('change');
                $('#room').val(data.student.room).trigger('change');

                $('#table_student_transfer tbody').empty();
                data.studentTransfer.forEach(function(transfer) {
                    $('#table_student_transfer tbody').append(`
                        <tr>
                            <td>${transfer.test_center}</td>
                            <td  class="text-center">${transfer.building}</td>
                            <td  class="text-center">${transfer.floor}</td>
                            <td  class="text-center">${transfer.room}</td>
                            <td  class="text-center">${transfer.created_at_formatted}</td>
                        </tr>
                    `);
                });
            });
        });

        // บันทึกข้อมูล
        $('#saveBtn').click(function(e) {
            e.preventDefault();

            let formData = new FormData($('#student_form')[0]);

            $.ajax({
                url: "{{ route('admin.student_transfer.store') }}"
                , type: "POST"
                , data: formData
                , processData: false
                , contentType: false
                , success: function(data) {
                    $('#student_form')[0].reset();
                    $('#studentModal').modal('hide');

                    Swal.fire({
                        icon: 'success'
                        , title: 'สำเร็จ'
                        , text: 'บันทึกข้อมูลเรียบร้อยแล้ว'
                    });

                    loadStudentData();
                }
            });
        });

        $("#export_file_form").on('submit', function(e) {
            e.preventDefault();
            let url = $(this).attr('action');
            let token = $('input[name="_token"]', this).val();

            waitingDialog.show('กำลังดาวน์โหลดไฟล์ Excel...', {
                onShow: function() {}
                , onHide: function() {}
            });

            $.ajax({
                type: "POST"
                , url: url
                , data: {
                    _token: token
                }
                , xhrFields: {
                    responseType: 'blob' // สำคัญสำหรับไฟล์
                }
                , success: function(data, status, xhr) {
                    // สร้าง URL สำหรับ blob
                    let blob = new Blob([data], {
                        type: xhr.getResponseHeader('Content-Type')
                    });
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);

                    // ดึงชื่อไฟล์จาก response header หรือใช้ชื่อเริ่มต้น
                    let contentDisposition = xhr.getResponseHeader('Content-Disposition');
                    let filename = 'รายชื่อผู้เข้าสอบปรับปรุงข้อมูล.xlsx';

                    if (contentDisposition) {
                        let filenameMatch = contentDisposition.match(
                            /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                        if (filenameMatch && filenameMatch[1]) {
                            filename = filenameMatch[1].replace(/['"]/g, '');
                        }
                    }

                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(link.href);

                    Swal.fire({
                        title: "Success"
                        , text: "ดาวน์โหลดไฟล์สำเร็จ"
                        , icon: "success"
                        , timer: 3000
                    });
                }
                , error: function(xhr, status, error) {
                    console.log(error);
                    Swal.fire({
                        title: "Error"
                        , text: "เกิดข้อผิดพลาดในการดาวน์โหลดไฟล์"
                        , icon: "error"
                    });
                }
                , complete: function() {
                    waitingDialog.hide();
                }
            });
        });
    });

</script>
@endsection
