@extends('layouts.admin')
@section('content')
<style>
    .table-test-center {
        table-layout: fixed;
        white-space: nowrap;
    }

</style>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-0">
            <div class="col-sm-6">
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item active">ศูนย์สอบ</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@can('test_center_create')
{{-- <div class="row mb-2">
    <div class="col-lg-12">
        <a class="btn btn-primary mr-2" href="javascript:void(0)" id="createTestCenter"><i class="fad fa-folder-plus"></i>
            เพิ่มศูนย์สอบ</a>
        <a class="btn btn-success mr-2" href="{{ route('admin.test_center.create') }}"><i class="fad fa-file-spreadsheet"></i></i> นำเข้าไฟล์ศูนย์สอบ Excel</a>
        <form action="{{ route('admin.test_center.resetTestCenter') }}" method="POST" style="display:inline">
            @csrf
            <button class="btn btn-dark mr-2" onclick="return confirm('ยืนยันการรีเซ็ตศูนย์สอบทั้งหมด ?')">
                <i class="fad fa-undo"></i> รีเซ็ตศูนย์สอบ
            </button>
        </form>
        <button class="btn btn-danger mr-2" id="bulkDeleteBtn" style="display:none;">
            <i class="fad fa-trash-alt"></i> ลบรายการที่เลือก (<span id="selectedCount">0</span>)
        </button>
        <form method="POST" id="export_file_form" action="{{ route('admin.test_center.exportFile') }}">
            @csrf
            <button type="submit" class="btn btn-warning mr-2">
                <i class="fad fa-download"></i> ดาวน์โหลดไฟล์ Excel
            </button>
        </form>
    </div>
</div> --}}
<div class="row mb-2">
    <div class="col-lg-12">
        <div class="d-flex flex-wrap align-items-stretch gap-2">

            <a class="btn btn-primary d-flex align-items-center mr-2" 
               href="javascript:void(0)" id="createTestCenter">
                <i class="fad fa-folder-plus mr-1"></i> เพิ่มศูนย์สอบ
            </a>

            <a class="btn btn-success d-flex align-items-center mr-2"
               href="{{ route('admin.test_center.create') }}">
                <i class="fad fa-file-spreadsheet mr-1"></i> นำเข้าศูนย์สอบ Excel
            </a>

            <form action="{{ route('admin.test_center.resetTestCenter') }}"
                  method="POST">
                @csrf
                <button type="submit"
                        class="btn btn-dark d-flex align-items-center h-100 mr-2"
                        onclick="return confirm('ยืนยันการรีเซ็ตศูนย์สอบทั้งหมด ?')">
                    <i class="fad fa-undo mr-1"></i> รีเซ็ตศูนย์สอบ
                </button>
            </form>

            <button class="btn btn-danger d-flex align-items-center mr-2"
                    id="bulkDeleteBtn" style="display:none;">
                <i class="fad fa-trash-alt mr-1"></i>
                ลบรายการที่เลือก (<span id="selectedCount">0</span>)
            </button>

            <form method="POST" id="export_file_form" action="{{ route('admin.test_center.exportFile') }}">
                @csrf
                <button type="submit"
                        class="btn btn-info d-flex align-items-center h-100 mr-2">
                    <i class="fad fa-download mr-1"></i> ดาวน์โหลด Excel
                </button>
            </form>

        </div>
    </div>
</div>
@endcan
<div class="card">
    <div class="card-header bg-secondary">
        รายการ
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-test-center">
                <thead>
                    <tr width="10">
                        <th width="30">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>#</th>
                        <th>ศูนย์สอบ</th>
                        <th>อาคาร/ตึก</th>
                        <th>ชั้น</th>
                        <th>ห้อง</th>
                        <th>ความจุ</th>
                        <th>ห้องแอร์</th>
                        <th>ห้องพัดลม</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="ajaxModel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modelHeading"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fad fa-window-close" style="--fa-primary-color: #bd0000; --fa-secondary-color: #bd0000;"></i></span>
                </button>
            </div>
            <div class="modal-body">
                <form id="test_center_form" name="test_center_form" class="form-horizontal">
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-lg-3 col-12">
                            <div class="form-group">
                                <label class="col-sm-12 control-label">ศูนย์สอบ</label>
                                <div class="input-group mb-3">
                                    {{-- <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fad fa-text"></i></span>
                                        </div> --}}
                                    <input type="text" class="form-control" id="test_center" name="test_center" placeholder="" placeholder="" value="" title="" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-12">
                            <div class="form-group">
                                <label class="col-sm-12 control-label">อาคาร/ตึก</label>
                                <div class="input-group mb-3">
                                    {{-- <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fad fa-text"></i></span>
                                        </div> --}}
                                    <input type="text" class="form-control" id="building" name="building" placeholder="" placeholder="" value="" title="" required>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-12">
                            <div class="form-group">
                                <label class="col-sm-12 control-label">ชั้น</label>
                                <div class="input-group mb-3">
                                    {{-- <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fad fa-text"></i></span>
                                        </div> --}}
                                    <input type="number" class="form-control" id="floor" name="floor" placeholder="" placeholder="" value="" title="" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-12">
                            <div class="form-group">
                                <label class="col-sm-12 control-label">ห้อง</label>
                                <div class="input-group mb-3">
                                    {{-- <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fad fa-text"></i></span>
                                        </div> --}}
                                    <input type="text" class="form-control" id="room" name="room" placeholder="" placeholder="" value="" title="" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-3 col-12">
                            <div class="form-group">
                                <label class="col-sm-12 control-label">ความจุ</label>
                                <div class="input-group mb-3">
                                    {{-- <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fad fa-text"></i></span>
                                        </div> --}}
                                    <input type="number" class="form-control" id="capacity" name="capacity" placeholder="" placeholder="" value="" title="" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-12">
                            <div class="form-group">
                                <label class="col-sm-12 control-label">ห้องแอร์</label>
                                <div class="input-group mb-3">
                                    {{-- <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fad fa-text"></i></span>
                                        </div> --}}
                                    <select name="air_condition" id="air_condition" class="form-control">
                                        <option value="">เลือก</option>
                                        <option value="Y">ใช่</option>
                                        <option value="N">ไม่ใช่</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-12">
                            <div class="form-group">
                                <label class="col-sm-12 control-label">ห้องพัดลม</label>
                                <div class="input-group mb-3">
                                    {{-- <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fad fa-text"></i></span>
                                        </div> --}}
                                    <select name="fan" id="fan" class="form-control">
                                        <option value="">เลือก</option>
                                        <option value="Y">ใช่</option>
                                        <option value="N">ไม่ใช่</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-success" id="saveBtn" value="create"><i class="fad fa-save"></i> บันทึก
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

        var table = $('.table-test-center').DataTable({
            processing: true
            , serverSide: false
            , ajax: "{{ route('admin.test_center.index') }}"
            , columns: [{
                    data: 'id'
                    , name: 'id'
                    , orderable: false
                    , searchable: false
                    , render: function(data, type, row) {
                        return '<input type="checkbox" class="row-checkbox" value="' + data + '">';
                    }
                }
                , {
                    data: 'id'
                    , name: 'id'
                }
                , {
                    data: 'test_center'
                    , name: 'test_center'
                    , width: '300px'
                }
                , {
                    data: 'building'
                    , name: 'building'
                    , width: '300px'
                }
                , {
                    data: 'floor'
                    , name: 'floor'
                }
                , {
                    data: 'room'
                    , name: 'room'
                    , width: '110px'
                }
                , {
                    data: 'capacity'
                    , name: 'capacity'
                }
                , {
                    data: 'air_condition'
                    , name: 'air_condition'
                }
                , {
                    data: 'fan'
                    , name: 'fan'
                }
                , {
                    data: 'action'
                    , name: 'action'
                    , orderable: false
                    , searchable: false
                }
            , ]
        });

        // Select All Checkbox
        $('#selectAll').on('click', function() {
            $('.row-checkbox').prop('checked', this.checked);
            updateBulkDeleteButton();
        });

        // Individual Checkbox
        $('tbody').on('change', '.row-checkbox', function() {
            updateBulkDeleteButton();

            // Update Select All state
            var totalCheckboxes = $('.row-checkbox').length;
            var checkedCheckboxes = $('.row-checkbox:checked').length;
            $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
        });

        // Update Bulk Delete Button
        function updateBulkDeleteButton() {
            var checkedCount = $('.row-checkbox:checked').length;
            $('#selectedCount').text(checkedCount);

            if (checkedCount > 0) {
                $('#bulkDeleteBtn').show();
            } else {
                $('#bulkDeleteBtn').hide();
            }
        }

        // Bulk Delete Action
        $('#bulkDeleteBtn').on('click', function() {
            var selectedIds = [];
            $('.row-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                Swal.fire({
                    title: "แจ้งเตือน!"
                    , text: "กรุณาเลือกรายการที่ต้องการลบ!"
                    , icon: "warning"
                });
                return;
            }

            Swal.fire({
                title: 'ยืนยันการลบ?'
                , text: "คุณต้องการลบ " + selectedIds.length + " รายการที่เลือก?"
                , icon: 'warning'
                , showCancelButton: true
                , confirmButtonColor: '#3085d6'
                , cancelButtonColor: '#d33'
                , confirmButtonText: 'ใช่, ลบเลย!'
                , cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST"
                        , url: "{{ route('admin.test_center.bulkDelete') }}"
                        , data: {
                            ids: selectedIds
                        }
                        , success: function(response) {
                            Swal.fire(
                                'ลบสำเร็จ!'
                                , 'ลบข้อมูล ' + selectedIds.length + ' รายการเรียบร้อย'
                                , 'success'
                            );
                            location.reload();
                        }
                        , error: function(data) {
                            Swal.fire(
                                'เกิดข้อผิดพลาด!'
                                , 'ไม่สามารถลบข้อมูลได้'
                                , 'error'
                            );
                            console.log('Error:', data);
                        }
                    });
                }
            });
        });

        $('#createTestCenter').click(function() {
            $('#saveBtn').val("บันทึก");
            $('#id').val('');
            $('#test_center_form').trigger("reset");
            $('#modelHeading').html("เพิ่มข้อมูล");
            $('#ajaxModel').modal('show');
        });

        $('body').on('click', '.editTestCenter', function() {
            var id = $(this).data('id');
            $.get("{{ route('admin.test_center.index') }}" + '/' + id + '/edit', function(
                data) {
                $('#modelHeading').html("แก้ไขข้อมูล");
                $('#saveBtn').val("edit-user");
                $('#ajaxModel').modal('show');
                $('#id').val(data.id);
                $('#test_center').val(data.test_center);
                $('#building').val(data.building);
                $('#floor').val(data.floor);
                $('#room').val(data.room);
                $('#capacity').val(data.capacity);
                $('#air_condition').val(data.air_condition);
                $('#fan').val(data.fan);
            })
        });

        $('#saveBtn').click(function(e) {
            e.preventDefault();

            let form = $('#test_center_form')[0];
            let formData = new FormData(form);
            if ($('#test_center').val() == '') {
                Swal.fire({
                    title: "แจ้งเตือน!"
                    , text: "กรุณาระบุศูนย์สอบ!"
                    , icon: "warning"
                });
            } else if ($('#building').val() == '') {
                Swal.fire({
                    title: "แจ้งเตือน!"
                    , text: "กรุณาระบุอาคาร/ตึก!"
                    , icon: "warning"
                });
            } else if ($('#floor').val() == '') {
                Swal.fire({
                    title: "แจ้งเตือน!"
                    , text: "กรุณาระบุชั้น!"
                    , icon: "warning"
                });
            } else if ($('#room').val() == '') {
                Swal.fire({
                    title: "แจ้งเตือน!"
                    , text: "กรุณาระบุห้อง!"
                    , icon: "warning"
                });
            } else if ($('#capacity').val() == '') {
                Swal.fire({
                    title: "แจ้งเตือน!"
                    , text: "กรุณาระบุความจุ!"
                    , icon: "warning"
                });
            } else {
                $.ajax({
                    url: "{{ route('admin.test_center.store') }}"
                    , type: "POST"
                    , data: formData
                    , processData: false
                    , contentType: false
                    , dataType: 'json'
                    , success: function(data) {
                        $('#test_center_form')[0].reset();
                        $('#ajaxModel').modal('hide');
                        location.reload();
                    }
                    , error: function(err) {
                        console.log(err);
                        $('#saveBtn').html('บันทึก');
                    }
                });
            }
        });

        $('body').on('click', '.deleteTestCenter', function() {

            var id = $(this).data("id");

            Swal.fire({
                title: 'ยืนยันการลบ?'
                , text: "คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้!"
                , icon: 'warning'
                , showCancelButton: true
                , confirmButtonColor: '#3085d6'
                , cancelButtonColor: '#d33'
                , confirmButtonText: 'ใช่, ลบเลย!'
                , cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE"
                        , url: "{{ route('admin.test_center.store') }}" + '/' + id
                        , success: function(data) {
                            Swal.fire(
                                'ลบสำเร็จ!'
                                , 'ลบข้อมูลเรียบร้อย'
                                , 'success'
                            );
                            location.reload();
                        }
                        , error: function(data) {
                            Swal.fire(
                                'เกิดข้อผิดพลาด!'
                                , 'ไม่สามารถลบข้อมูลได้'
                                , 'error'
                            );
                            console.log('Error:', data);
                        }
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
                    let filename = 'ศูนย์สอบ.xlsx';

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
