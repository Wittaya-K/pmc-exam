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
                    <li class="breadcrumb-item active">ปรับปรุงข้อมูล</li>
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

                    <form method="POST" action="{{ route('admin.student_update.exportFile') }}">
                        @csrf
                        <button type="submit"
                                class="btn btn-info d-flex align-items-center h-100 mr-2">
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
                                    <label>ชื่อ (eng)</label>
                                    <input type="text" class="form-control" id="first_name_en" name="first_name_en" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>นามสกุล (eng)</label>
                                    <input type="text" class="form-control" id="last_name_en" name="last_name_en" required>
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
                                    {{-- <input type="text" class="form-control" id="program_name" name="program_name" required> --}}
                                    <select name="program_name" id="program_name" class="form-control">
                                        <option value="">เลือก</option>
                                        <option value="ประถมปลาย (ป.4 - ป.6)">ประถมปลาย (ป.4 - ป.6)</option>
                                        <option value="มัธยมต้น (ม.1 - ม.3)">มัธยมต้น (ม.1 - ม.3)</option>
                                        <option value="มัธยมปลาย (ม.4 - ม.6)">มัธยมปลาย (ม.4 - ม.6)</option>
                                    </select>
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
                                    <label>ระดับชั้น</label>
                                    {{-- <input type="text" class="form-control" id="level" name="level" required> --}}
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
                                    <input type="text" class="form-control" id="phone" name="phone" required>
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

@endsection

@section('scripts')
    @parent
    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>
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
                    columns: [
                        {
                            data: 'id',
                            title: '#',
                            orderable: false,
                            width: '50px'
                        },
                        {
                            data: 'first_name_th',
                            title: 'ชื่อไทย',
                        },
                        {
                            data: 'last_name_th',
                            title: 'สกุลไทย',
                        },
                        {
                            data: 'program_name',
                            title: 'ระดับการสอบ',
                        },
                        {
                            data: 'test_center',
                            title: 'ศูนย์สอบ',
                        },
                        {
                            data: 'classLevel',
                            title: 'ชั้น',
                        },
                        {
                            data: null,
                            title: 'จัดการ',
                            orderable: false,
                            width: '100px',
                            className: 'text-center',
                            render: function(data, type, row) {
                                return `
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-info editStudent" data-id="${row.id}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        }
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
                    }
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

            // ฟังก์ชั่นโหลดข้อมูลนักเรียน
            function loadStudentData() {
                var test_center = $('#test_center').val();
                var fNamelname = $('#fNamelname').val();

                if (!test_center) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'แจ้งเตือน',
                        text: 'กรุณาเลือกศูนย์สอบก่อน'
                    });
                    return;
                }

                var url = "{{ route('admin.student_update.searchStudent') }}";
                var requestData = {
                    test_center: test_center,
                };

                // ถ้าเลือกชื่อด้วย ให้ใช้ getStudent แทน
                if (fNamelname) {
                    url = "{{ route('admin.student_update.getStudent') }}";
                    requestData.fNamelname = fNamelname;
                }

                $.ajax({
                    data: requestData,
                    url: url,
                    type: "POST",
                    dataType: 'json',
                    success: function(data) {
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
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'ผิดพลาด',
                            text: 'ไม่สามารถโหลดข้อมูลได้'
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
                
                $.get("{{ route('admin.student_update.index') }}" + '/' + id + '/edit', function(data) {
                    $('#modelHeading').html("แก้ไขข้อมูลผู้เข้าสอบ");
                    $('#studentModal').modal('show');
                    $('#student_id').val(data.id);
                    $('#first_name_th').val(data.first_name_th);
                    $('#last_name_th').val(data.last_name_th);
                    $('#first_name_en').val(data.first_name_en);
                    $('#last_name_en').val(data.last_name_en);
                    $('#school').val(data.school);
                    $('#program_name').val(data.program_name);
                    $('#test_center_input').val(data.test_center);
                    $('#classLevel').val(data.classLevel);
                    $('#level').val(data.level);
                    $('#email').val(data.email);
                    $('#phone').val(data.phone);
                });
            });

            // บันทึกข้อมูล
            $('#saveBtn').click(function(e) {
                e.preventDefault();

                let formData = new FormData($('#student_form')[0]);

                $.ajax({
                    url: "{{ route('admin.student_update.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        $('#student_form')[0].reset();
                        $('#studentModal').modal('hide');
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ',
                            text: 'บันทึกข้อมูลเรียบร้อยแล้ว'
                        });
                        
                        loadStudentData();
                    }
                });
            });
        });
    </script>
@endsection