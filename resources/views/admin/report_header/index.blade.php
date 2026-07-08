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
                    <li class="breadcrumb-item active">ตั้งค่าหัวรายงาน</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@can('report_header_create')

{{-- <div class="row mb-2">
    <div class="col-lg-12">
        <div class="d-flex flex-wrap align-items-stretch gap-2">

            <a class="btn btn-primary d-flex align-items-center mr-2" href="javascript:void(0)" id="createReportHeader">
                <i class="fad fa-folder-plus mr-1"></i> เพิ่มหัวรายงาน
            </a>

        </div>
    </div>
</div> --}}
@endcan
<div class="card">
    <div class="card-header bg-secondary">
        รายการ
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-report-header">
                <thead>
                    <tr width="10">
                        <th>#</th>
                        <th>ชื่อโครงการ (ไทย)</th>
                        <th>ชื่อโครงการ (อังกฤษ)</th>
                        <th>วันที่เริ่มเปิดสอบ</th>
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
                <form id="report_header_form" name="report_header_form" class="form-horizontal">
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-lg-6 col-12">
                            <div class="form-group">
                                <label class="col-sm-12 control-label">ชื่อโครงการ (ไทย)</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="project_name_th" name="project_name_th" placeholder="" placeholder="" value="" title="" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-12">
                            <div class="form-group">
                                <label class="col-sm-12 control-label">ชื่อโครงการ (อังกฤษ)</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="project_name_en" name="project_name_en" placeholder="" placeholder="" value="" title="" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 col-12">
                            <div class="form-group">
                                <label class="col-sm-12 control-label">วันที่เริ่มเปิดสอบ</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="exam_date_open" name="exam_date_open" placeholder="" placeholder="" value="" title="" required>
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

        var table = $('.table-report-header').DataTable({
            processing: true
            , serverSide: false
            , ajax: "{{ route('admin.report_header.index') }}"
            , columns: [{
                    data: 'id'
                    , name: 'id'
                    , orderable: false
                    , searchable: false
                }
                , {
                    data: 'project_name_th'
                    , name: 'project_name_th'
                    , width: '300px'
                }
                , {
                    data: 'project_name_en'
                    , name: 'project_name_en'
                    , width: '300px'
                }
                , {
                    data: 'exam_date_open'
                    , name: 'exam_date_open'
                }
                , {
                    data: 'action'
                    , name: 'action'
                    , orderable: false
                    , searchable: false
                }
            , ]
        });

        $('#createReportHeader').click(function() {
            $('#saveBtn').val("บันทึก");
            $('#id').val('');
            $('#report_header_form').trigger("reset");
            $('#modelHeading').html("เพิ่มข้อมูล");
            $('#ajaxModel').modal('show');
        });

        $('body').on('click', '.editReportHeader', function() {
            var id = $(this).data('id');
            $.get("{{ route('admin.report_header.index') }}" + '/' + id + '/edit', function(
                data) {
                $('#modelHeading').html("แก้ไขข้อมูล");
                $('#saveBtn').val("edit-user");
                $('#ajaxModel').modal('show');
                $('#id').val(data.id);
                $('#project_name_th').val(data.project_name_th);
                $('#project_name_en').val(data.project_name_en);
                $('#exam_date_open').val(data.exam_date_open);
            })
        });

        $('#saveBtn').click(function(e) {
            e.preventDefault();

            let form = $('#report_header_form')[0];
            let formData = new FormData(form);
            if ($('#project_name_th').val() == '') {
                Swal.fire({
                    title: "แจ้งเตือน!"
                    , text: "กรุณาระบุชื่อโครงการ (ไทย)!"
                    , icon: "warning"
                });
            } else if ($('#project_name_en').val() == '') {
                Swal.fire({
                    title: "แจ้งเตือน!"
                    , text: "กรุณาระบุชื่อโครงการ (อังกฤษ)!"
                    , icon: "warning"
                });
            } else if ($('#exam_date_open').val() == '') {
                Swal.fire({
                    title: "แจ้งเตือน!"
                    , text: "กรุณาระบุวันเปิดสอบ!"
                    , icon: "warning"
                });
            } else {
                $.ajax({
                    url: "{{ route('admin.report_header.store') }}"
                    , type: "POST"
                    , data: formData
                    , processData: false
                    , contentType: false
                    , dataType: 'json'
                    , success: function(data) {
                        $('#report_header_form')[0].reset();
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

            let id = $(this).data("id");

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
                    let url = "{{ route('admin.report_header.destroy', ':id') }}";
                    url = url.replace(':id', id);
                    $.ajax({
                        type: "DELETE"
                        , url: url
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
    });

</script>
@endsection

