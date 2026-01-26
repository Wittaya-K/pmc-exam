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
                        <li class="breadcrumb-item active">จัดที่นั่งสอบอัตโนมัติ</li>
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
            <i class="fad fa-stream"></i> จัดการห้องสอบ
        </div>

        <div class="card-body">
            <div class="d-flex align-items-center mb-2">
                <form method="POST" class="needs-validation" id="assign_seats_form"
                    action="{{ route('admin.arrange_seat.assignSeats') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <button type="submit" class="btn btn-primary mr-2" {{ ($countSeatAssign > 0) ? 'disabled' : '' }}>
                        <i class="fad fa-play"></i> จัดห้องสอบ
                    </button>
                    <a class="btn bg-secondary mr-2" href="{{ route('admin.arrange_seat.view') }}">
                        <i class="fad fa-list-ol"></i> ที่นั่งสอบ
                    </a>
                </form>

                <form method="POST" id="export_file_form" action="{{ route('admin.arrange_seat.exportFile') }}">
                    @csrf
                    <button type="submit" class="btn btn-success mr-2" {{ ($countSeatAssign < 1) ? 'disabled' : '' }}>
                        <i class="fad fa-file-spreadsheet"></i> ส่งออกไฟล์ Excel
                    </button>
                </form>

                <form action="{{ route('admin.arrange_seat.resetAssignSeats') }}" method="POST">
                    @csrf
                    <button class="btn btn-danger mr-2" onclick="return confirm('ยืนยันการรีเซ็ตการจัดสอบทั้งหมด ?')">
                        <i class="fad fa-trash-alt"></i> รีเซ็ตการจัดสอบ
                    </button>
                </form>
            </div>
        </div>

    </div>

    <div class="card">
        <div class="card-header bg-secondary">
            <i class="fad fa-stream"></i> แดชบอร์ด
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-indigo">
                        <div class="inner">
                            <h3>{{ number_format($countStudent) }} คน</h3>

                            <p>นักเรียนทั้งหมด</p>
                        </div>
                        <div class="icon">
                            <i class="fad fa-user"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-maroon">
                        <div class="inner">
                            <h3>{{ number_format($countRoom) }} ห้อง</h3>

                            <p>ห้องสอบที่จัดแล้ว</p>
                        </div>
                        <div class="icon">
                            <i class="fad fa-cube"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-olive">
                        <div class="inner">
                            <h3>{{ number_format($countSeatAssign) }} ที่นั่ง</h3>

                            <p>ที่นั่งที่จัดแล้ว</p>
                        </div>
                        <div class="icon">
                            <i class="fad fa-loveseat"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-lightblue">
                        <div class="inner">
                            <h3>30 ที่นั่ง</h3>

                            <p>ต่อห้องโดยประมาณ</p>
                        </div>
                        <div class="icon">
                            <i class="fad fa-loveseat"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-secondary">
            <i class="fad fa-stream"></i> ศูนย์สอบที่จัดแล้ว
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-lg-12 col-12">
                    <div class="form-group">
                        {{-- <label>ศูนย์สอบ</label> --}}
                        <select multiple="" class="form-control">
                            @foreach ($selectTestcenter as $row)
                                <option value="{{ $row->test_center }}">
                                    {{ $row->test_center }} {{ number_format($row->total) }} คน
                                </option>
                            @endforeach
                        </select>
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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#assign_seats_form").on('submit', function(e) {
            e.preventDefault();
            let url = $(this).attr('action');
            let formData = new FormData(this);
            $.ajax({
                type: "POST",
                url: url,
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                beforeSend: function() {
                    waitingDialog.show('กำลังจัดห้องสอบ...', {
                        onShow: function() {},
                        onHide: function() {}
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
                                // window.history.back();
                            }
                        });
                    } else {
                        console.log(response);
                    }
                },
                complete: function() {
                    waitingDialog.hide();
                    location.reload();
                },
                error: function(error) {
                    console.log(error);
                }
            });
        });

        $("#export_file_form").on('submit', function(e) {
            e.preventDefault();
            let url = $(this).attr('action');
            let token = $('input[name="_token"]', this).val();

            waitingDialog.show('กำลังส่งออกไฟล์ Excel...', {
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
                    let filename = 'รายชื่อที่นั่งสอบ.xlsx';

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
                        text: "ส่งออกไฟล์สำเร็จ",
                        icon: "success",
                        timer: 3000
                    });
                },
                error: function(xhr, status, error) {
                    console.log(error);
                    Swal.fire({
                        title: "Error",
                        text: "เกิดข้อผิดพลาดในการส่งออกไฟล์",
                        icon: "error"
                    });
                },
                complete: function() {
                    waitingDialog.hide();
                }
            });
        });
    </script>
@endsection
