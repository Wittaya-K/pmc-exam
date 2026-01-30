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

    .badge-present {
        background-color: #28a745;
    }

    .badge-absent {
        background-color: #dc3545;
    }

    .badge-pending {
        background-color: #6c757d;
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
                    <li class="breadcrumb-item active">ผู้เข้าสอบ</li>
                </ol>
            </div>
        </div>
    </div>
</div>

@can('student_recheck_create')
{{-- <div class="row mb-2">
            <div class="col-lg-12">
                <a class="btn btn-primary mr-2" href="javascript:void(0)" id="createStudent">
                    <i class="fas fa-plus"></i> เพิ่มผู้เข้าสอบ
                </a>
                <a class="btn btn-success mr-2" href="javascript:void(0)" id="bulkCheckAttendance">
                    <i class="fas fa-check-double"></i> เช็คชื่อหลายคน
                </a>

                <form method="POST" action="{{ route('admin.student_recheck.exportFile') }}">
@csrf
<button type="submit" class="btn btn-info d-flex align-items-center h-100 mr-2">
    <i class="fad fa-download mr-1"></i> ดาวน์โหลด Excel
</button>
</form>
</div>
</div> --}}

<div class="row mb-2">
    <div class="col-lg-12">
        <div class="d-flex flex-wrap align-items-stretch gap-2">
            <a class="btn btn-primary d-flex align-items-center mr-2" href="javascript:void(0)" id="createStudent">
                <i class="fas fa-plus"></i> เพิ่มผู้เข้าสอบ
            </a>

            <a class="btn btn-success d-flex align-items-center mr-2" href="javascript:void(0)" id="bulkCheckAttendance">
                <i class="fas fa-check-double"></i> เช็คชื่อหลายคน
            </a>

            <form method="POST" id="export_file_form" action="{{ route('admin.student_recheck.exportFile') }}">
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
                        <label class="control-label">เลือกห้องสอบ</label>
                        <div class="input-group mb-3">
                            <select class="form-control select2" name="room_filter" id="room_filter">
                                <option value="">เลือก</option>
                                @foreach ($room as $row)
                                <option value="{{ $row->room }}">{{ $row->room }}</option>
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
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">สถานะการเข้าสอบ</label>
                        <div class="input-group mb-3">
                            <select class="form-control" name="attendance_filter" id="attendance_filter">
                                <option value="">ทั้งหมด</option>
                                <option value="present">มาสอบ</option>
                                <option value="absent">ขาดสอบ</option>
                                <option value="pending">รอตรวจสอบ</option>
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

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>ชื่อ (ไทย)</label>
                                <input type="text" class="form-control" id="first_name_th" name="first_name_th" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>นามสกุล (ไทย)</label>
                                <input type="text" class="form-control" id="last_name_th" name="last_name_th" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>โรงเรียน</label>
                                <input type="text" class="form-control" id="school" name="school" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>ระดับการสอบ</label>
                                <input type="text" class="form-control" id="program_name" name="program_name" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>ศูนย์สอบ</label>
                                <input type="text" class="form-control" id="test_center_input" name="test_center" required>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>ชั้นการศึกษา</label>
                                <input type="text" class="form-control" id="classLevel" name="classLevel" required>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>ห้องสอบ</label>
                                <input type="text" class="form-control" id="room" name="room" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>เลขที่นั่ง</label>
                                <input type="number" class="form-control" id="seat_no" name="seat_no" required>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>สถานะการเข้าสอบ</label>
                                <select class="form-control" id="attendance_status" name="attendance_status">
                                    <option value="pending">รอตรวจสอบ</option>
                                    <option value="present">มาสอบ</option>
                                    <option value="absent">ขาดสอบ</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>เหตุผลที่ขาดสอบ</label>
                                <input type="text" class="form-control" id="absence_reason" name="absence_reason">
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-success" id="saveBtn">
                            <i class="fas fa-save"></i> บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal เช็คชื่อหลายคน -->
<div class="modal fade" id="bulkAttendanceModal" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">เช็คชื่อเข้าสอบหลายคน</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <button class="btn btn-info btn-sm" id="markAllPending">
                        <i class="fas fa-hourglass-start"></i> เช็ครอตรวจสอบทั้งหมด
                    </button>
                    <button class="btn btn-success btn-sm" id="markAllPresent">
                        <i class="fas fa-check"></i> เช็คมาสอบทั้งหมด
                    </button>
                    <button class="btn btn-danger btn-sm" id="markAllAbsent">
                        <i class="fas fa-times"></i> เช็คขาดสอบทั้งหมด
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="bulkAttendanceTable">
                        <thead>
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>รหัส</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>ห้องสอบ</th>
                                <th>เลขที่นั่ง</th>
                                <th>สถานะ</th>
                                <th>เหตุผล</th>
                            </tr>
                        </thead>
                        <tbody id="bulkAttendanceBody"></tbody>
                    </table>
                </div>
                <div class="text-right mt-3">
                    <button class="btn btn-primary" id="saveBulkAttendance">
                        <i class="fas fa-save"></i> บันทึกทั้งหมด
                    </button>
                </div>
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

        var tbl_student;

        function show_student(data) {
            if (tbl_student) {
                tbl_student.destroy();
            }

            tbl_student = $('.table-test-center').DataTable({
                destroy: true,
                pageLength: 25,
                scrollX: true,
                autoWidth: false,
                responsive: false,
                data: data.data, 
                order: [[7, 'asc'], [8, 'asc']
                ]
                , columns: [{
                        data: 'id'
                        , title: '#'
                        , orderable: false
                    , }
                    , {
                        data: 'first_name_th'
                        , title: 'ชื่อไทย'
                    , }
                    , {
                        data: 'last_name_th'
                        , title: 'สกุลไทย'
                    , },
                    // {
                    //     data: 'school',
                    //     title: 'โรงเรียน',
                    // },
                    {
                        data: 'program_name'
                        , title: 'ระดับการสอบ'
                    , }
                    , {
                        data: 'test_center'
                        , title: 'ศูนย์สอบ'
                    , },
                    // {
                    //     data: 'classLevel',
                    //     title: 'ชั้น',
                    // },
                    {
                        data: 'room'
                        , title: 'ห้องสอบ'
                    , }
                    , {
                        data: 'seat_no'
                        , title: 'เลขที่นั่ง'
                        , type: 'num'
                    }
                    , {
                        data: 'attendance_status'
                        , title: 'สถานะ'
                        , render: function(data, type, row) {
                            var badge = 'badge-pending';
                            var text = 'รอตรวจสอบ';

                            if (data === 'present') {
                                badge = 'badge-present';
                                text = 'มาสอบ';
                            } else if (data === 'absent') {
                                badge = 'badge-absent';
                                text = 'ขาดสอบ';
                            }

                            // เพิ่ม data attribute สำหรับการค้นหา
                            return `<span class="badge ${badge}" data-status="${data}">${text}</span>`;
                        }
                    }
                    , {
                        data: 'absence_reason'
                        , title: 'เหตุผล'
                        , render: function(data) {
                            return data ? data : '-';
                        }
                    }
                    , {
                        data: null,
                        title: 'จัดการ',
                        orderable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-info editStudent mr-2" data-id="${row.id}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-success markPresent mr-2" data-id="${row.id}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning markAbsent mr-2" data-id="${row.id}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger deleteStudent mr-2" data-id="${row.id}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                `;
                        }
                    }
                ]
            });
        }

        // เมื่อเลือกศูนย์สอบ
        $('#test_center').change(function() {
            var test_center = $(this).val();

            if (!test_center) return;

            loadStudentData();
        });

        // เมื่อเลือกชื่อ
        $('#fNamelname').change(function() {
            loadStudentData();
        });

        // ฟิลเตอร์สถานะ
        $('#attendance_filter').change(function() {
            loadStudentData();
        });



        // ฟังก์ชันโหลดข้อมูลนักเรียน

        // เมื่อเลือกห้องสอบ (เพิ่มหลัง $('#test_center').change)
        $('#room_filter').change(function() {
            loadStudentData();
        });

        // แก้ไขฟังก์ชัน loadStudentData() ให้รวม room_filter
        function loadStudentData() {
            var test_center = $('#test_center').val();
            var fNamelname = $('#fNamelname').val();
            var attendance_status = $('#attendance_filter').val();
            var room_filter = $('#room_filter').val(); // เพิ่มบรรทัดนี้

            if (!test_center) {
                Swal.fire({
                    icon: 'warning'
                    , title: 'แจ้งเตือน'
                    , text: 'กรุณาเลือกศูนย์สอบก่อน'
                });
                return;
            }

            var url = "{{ route('admin.student_recheck.searchStudent') }}";
            var requestData = {
                test_center: test_center
                , attendance_status: attendance_status
                , room_filter: room_filter // เพิ่มบรรทัดนี้
            };

            // ถ้าเลือกชื่อด้วย ให้ใช้ getStudent แทน
            if (fNamelname) {
                url = "{{ route('admin.student_recheck.getStudent') }}";
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

                    // อัพเดทรายการห้องตามข้อมูลที่ได้
                    if (!room_filter) {
                        var rooms = [...new Set(data.data.map(item => item.room))];
                        $('#room_filter').empty().append('<option value="">เลือก</option>');
                        rooms.forEach(function(room) {
                            if (room) {
                                $('#room_filter').append(`<option value="${room}">${room}</option>`);
                            }
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

            $.get("{{ route('admin.student_recheck.index') }}" + '/' + id + '/edit', function(data) {
                $('#modelHeading').html("แก้ไขข้อมูลผู้เข้าสอบ");
                $('#studentModal').modal('show');
                $('#student_id').val(data.id);
                $('#first_name_th').val(data.first_name_th);
                $('#last_name_th').val(data.last_name_th);
                $('#school').val(data.school);
                $('#program_name').val(data.program_name);
                $('#test_center_input').val(data.test_center);
                $('#classLevel').val(data.classLevel);
                $('#room').val(data.room);
                $('#seat_no').val(data.seat_no);
                $('#attendance_status').val(data.attendance_status);
                $('#absence_reason').val(data.absence_reason);
            });
        });

        // บันทึกข้อมูล
        $('#saveBtn').click(function(e) {
            e.preventDefault();

            let formData = new FormData($('#student_form')[0]);

            $.ajax({
                url: "{{ route('admin.student_recheck.store') }}"
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

        // เช็คมาสอบ
        $('body').on('click', '.markPresent', function() {
            var id = $(this).data('id');

            $.ajax({
                url: "{{ route('admin.student_recheck.updateAttendance') }}"
                , type: "POST"
                , data: {
                    id: id
                    , attendance_status: 'present'
                    , absence_reason: null
                }
                , success: function() {
                    Swal.fire({
                        icon: 'success'
                        , title: 'สำเร็จ'
                        , text: 'เช็คชื่อมาสอบแล้ว'
                        , timer: 1500
                        , showConfirmButton: false
                    });
                    loadStudentData();
                }
                , error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                    Swal.fire('ผิดพลาด', 'ไม่สามารถบันทึกได้', 'error');
                }
            });
        });

        // เช็คขาดสอบ
        $('body').on('click', '.markAbsent', function() {
            var id = $(this).data('id');

            Swal.fire({
                title: 'ระบุเหตุผล'
                , input: 'text'
                , inputPlaceholder: 'เหตุผลที่ขาดสอบ'
                , showCancelButton: true
                , confirmButtonText: 'บันทึก'
                , cancelButtonText: 'ยกเลิก'
                , inputValidator: (value) => {
                    if (!value) {
                        return 'กรุณาระบุเหตุผล';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.student_recheck.updateAttendance') }}"
                        , type: "POST"
                        , data: {
                            id: id
                            , attendance_status: 'absent'
                            , absence_reason: result.value
                        }
                        , success: function() {
                            Swal.fire({
                                icon: 'success'
                                , title: 'สำเร็จ'
                                , text: 'บันทึกการขาดสอบแล้ว'
                                , timer: 1500
                                , showConfirmButton: false
                            });
                            loadStudentData();
                        }
                        , error: function(xhr) {
                            console.error('Error:', xhr.responseText);
                            Swal.fire('ผิดพลาด', 'ไม่สามารถบันทึกได้', 'error');
                        }
                    });
                }
            });
        });

        // ลบข้อมูล
        $('body').on('click', '.deleteStudent', function() {
            var id = $(this).data('id');

            Swal.fire({
                title: 'ยืนยันการลบ?'
                , text: 'คุณต้องการลบข้อมูลนี้หรือไม่'
                , icon: 'warning'
                , showCancelButton: true
                , confirmButtonText: 'ลบ'
                , cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE"
                        , url: "{{ route('admin.student_recheck.store') }}" + '/' + id
                        , success: function() {
                            Swal.fire('ลบแล้ว', 'ลบข้อมูลเรียบร้อย', 'success');
                            $('#test_center').trigger('change');
                        }
                    });
                }
            });
        });

        // เช็คชื่อหลายคน
        $('#bulkCheckAttendance').click(function() {
            if (!tbl_student) {
                Swal.fire('แจ้งเตือน', 'กรุณาเลือกศูนย์สอบก่อน', 'warning');
                return;
            }

            var data = tbl_student.rows().data();
            var html = '';

            $.each(data, function(i, row) {
                html += `
                        <tr data-id="${row.id}">
                            <td><input type="checkbox" class="student-check" value="${row.id}"></td>
                            <td>${row.id}</td>
                            <td>${row.first_name_th} ${row.last_name_th}</td>
                            <td>${row.room}</td>
                            <td>${row.seat_no}</td>
                            <td>
                                <select class="form-control form-control-sm attendance-select">
                                    <option value="pending" ${row.attendance_status === 'pending' ? 'selected' : ''}>รอตรวจสอบ</option>
                                    <option value="present" ${row.attendance_status === 'present' ? 'selected' : ''}>มาสอบ</option>
                                    <option value="absent" ${row.attendance_status === 'absent' ? 'selected' : ''}>ขาดสอบ</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm reason-input" 
                                       value="${row.absence_reason || ''}" placeholder="เหตุผล">
                            </td>
                        </tr>
                    `;
            });

            $('#bulkAttendanceBody').html(html);
            $('#bulkAttendanceModal').modal('show');
        });

        // เลือกทั้งหมด
        $('#selectAll').change(function() {
            $('.student-check').prop('checked', $(this).is(':checked'));
        });

        // เช็คมาสอบทั้งหมด
        $('#markAllPending').click(function() {
            $('.attendance-select').val('pending');
        });

        // เช็คมาสอบทั้งหมด
        $('#markAllPresent').click(function() {
            $('.attendance-select').val('present');
        });

        // เช็คขาดสอบทั้งหมด
        $('#markAllAbsent').click(function() {
            $('.attendance-select').val('absent');
        });

        // บันทึกการเช็คชื่อหลายคน
        $('#saveBulkAttendance').click(function() {
            var updates = [];

            $('#bulkAttendanceBody tr').each(function() {
                var id = $(this).data('id');
                var status = $(this).find('.attendance-select').val();
                var reason = $(this).find('.reason-input').val();

                updates.push({
                    id: id
                    , attendance_status: status
                    , absence_reason: status === 'absent' ? reason : ''
                });
            });

            console.log('Updates to send:', updates); // Debug

            // ส่งข้อมูลทั้งหมดในครั้งเดียว
            $.ajax({
                url: "{{ route('admin.student_recheck.bulkUpdateAttendance') }}"
                , type: "POST"
                , data: {
                    updates: updates
                }
                , success: function(response) {
                    $('#bulkAttendanceModal').modal('hide');
                    Swal.fire({
                        icon: 'success'
                        , title: 'สำเร็จ'
                        , text: 'บันทึกข้อมูลทั้งหมดเรียบร้อย'
                    });
                    loadStudentData();
                }
                , error: function(xhr, status, error) {
                    console.error('Error:', error);
                    console.error('Response:', xhr.responseText);
                    Swal.fire({
                        icon: 'error'
                        , title: 'เกิดข้อผิดพลาด'
                        , text: 'ไม่สามารถบันทึกข้อมูลได้'
                    });
                }
            });
        });

        $("#export_file_form").on('submit', function(e) {
            e.preventDefault();
            let url = $(this).attr('action');
            let token = $('input[name="_token"]', this).val();

            waitingDialog.show('กำลังดาวน์โหลดไฟล์ Excel...', {
                onShow: function() {},
                onHide: function() {}
            });

            $.ajax({
                type: "POST",
                url: url,
                data: {
                    _token: token
                },
                xhrFields: {
                    responseType: 'blob' // สำคัญสำหรับไฟล์
                },
                success: function(data, status, xhr) {
                    // สร้าง URL สำหรับ blob
                    let blob = new Blob([data], {
                        type: xhr.getResponseHeader('Content-Type')
                    });
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);

                    // ดึงชื่อไฟล์จาก response header หรือใช้ชื่อเริ่มต้น
                    let contentDisposition = xhr.getResponseHeader('Content-Disposition');
                    let filename = 'ข้อมูลผู้เข้าสอบ.xlsx';

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
                        title: "Success",
                        text: "ดาวน์โหลดไฟล์สำเร็จ",
                        icon: "success",
                        timer: 3000
                    });
                },
                error: function(xhr, status, error) {
                    console.log(error);
                    Swal.fire({
                        title: "Error",
                        text: "เกิดข้อผิดพลาดในการดาวน์โหลดไฟล์",
                        icon: "error"
                    });
                },
                complete: function() {
                    waitingDialog.hide();
                }
            });
        });
    });
</script>
@endsection

