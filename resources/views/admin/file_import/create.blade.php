@extends('layouts.admin')
@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    {{-- <h3>นำเข้าไฟล์ข้อมูลผู้สอบ</h3> --}}
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="">แดชบอร์ด</a></li>
                        <li class="breadcrumb-item active">นำเข้าไฟล์ข้อมูลผู้สอบ</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-secondary">
            <i class="fad fa-stream"></i> นำเข้าไฟล์ข้อมูล
        </div>

        <div class="card-body">
        <form method="POST" class="needs-validation" id="file_import_form" action="{{ route('admin.file_import.save') }}" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="id" id="id" />
                <div class="row">
                    <div class="col-lg-8 col-12">
                        <div class="row">
                            <div class="col-lg-6 col-12">
                                <div class="btcd-f-input">
                                    <label class="col-sm-12 control-label">ไฟล์ excel </label>
                                    <div class="btcd-f-wrp">
                                        <button class="btcd-inpBtn" type="button"> <img src=""
                                                alt=""> <span>
                                                เลือกไฟล์</span></button>
                                        <span class="btcd-f-title">ไม่มีไฟล์ที่เลือก</span>
                                        <small class="f-max"> (สูงสุด 100 MB)</small>
                                        <input type="file" name="fileUpload[]" multiple
                                            id="fileUpload"
                                            accept=".xlsx, .xls, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                                    </div>
                                    <div class="btcd-files">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> {{-- end row --}}

                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-success" id="saveBtn" value="create"><i
                            class="fad fa-save"></i> ยืนยัน
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    @parent
    <script type="text/javascript" src="{{ asset('js/sweetalert2@11.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/bootstrap-waitingfor.min.js') }}"></script>
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#file_import_form").on('submit', function(e) {
            e.preventDefault();

            // ตรวจว่ามีไฟล์หรือไม่
            if ($('#fileUpload')[0].files.length === 0) {
                Swal.fire({
                    title: "แจ้งเตือน!",
                    text: "กรุณาเลือกไฟล์ที่ต้องการนำเข้าข้อมูล!",
                    icon: "warning"
                });
                return false; // หยุดการ submit ตรงนี้
            }

            let url = $(this).attr('action');
            let formData = new FormData(this); // use 'this' directly to refer to the HTML form element

            $.ajax({
                type: "POST",
                url: url,
                data: formData,
                dataType: 'json',
                processData: false, // important for file uploads
                contentType: false, // important for file uploads
                beforeSend: function() {
                    // $('#loadingModal').modal('show');
                    waitingDialog.show('กำลังนำเข้ารายชื่อผู้เข้าสอบ...', {
                        onShow: function() {

                        },
                        onHide: function() {

                        }
                    });
                },
                success: function(response) {
                    if (response.status) {
                        Swal.fire({
                            title: "Success",
                            text: response.message,
                            icon: "success"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.history.back();
                            }
                        });
                    } else {
                        console.log(response);
                    }
                },
                complete: function() {
                    waitingDialog.hide();
                    // location.reload();
                    window.history.back();
                },
                error: function(error) {
                    console.log(error);
                }
            });
        });
    </script>
@endsection
