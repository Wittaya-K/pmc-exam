<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('admin.home') }}" class="brand-link">
        <img src="{{ url('image/logo.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-gray">PMC Exam</span>
    </a>
    {{-- <a href="{{ route('admin.home') }}" class="brand-link">
    <span class="brand-text font-weight-gray">ระบบบริหารการจัดสอบ</span>
    </a> --}}
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent nav-flat" data-widget="treeview" role="menu" data-accordion="false">
                {{-- <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false"> --}}
                <li class="nav-item">
                    <a href="{{ route('admin.home') }}" class="nav-link">
                        <p>
                            <i class="fad fa-chart-area"></i>
                            <span>{{ trans('global.dashboard') }}</span>
                        </p>
                    </a>
                </li>
                @can('user_management_access')
                <li class="nav-item has-treeview {{ request()->is('admin/test_center*') ? 'menu-open' : '' }} {{ request()->is('admin/report_header*') ? 'menu-open' : '' }}">
                    <a class="nav-link nav-dropdown-toggle">
                        <i class="fad fa-folder"></i>
                        <p>
                            <span>ตั้งค่า</span>
                            <i class="right fa fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.test_center.index') }}" class="nav-link {{ request()->is('admin/test_center') || request()->is('admin/test_center/*') ? 'active' : '' }}">
                                <i class="fad fa-chevron-circle-right"></i>
                                <p>
                                    <span>ศูนย์สอบ</span>
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.report_header.index') }}" class="nav-link {{ request()->is('admin/report_header') || request()->is('admin/report_header/*') ? 'active' : '' }}">
                                <i class="fad fa-chevron-circle-right"></i>
                                <p>
                                    <span>หัวรายงาน</span>
                                </p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
                @can('file_import_access')
                <li class="nav-item has-treeview {{ request()->is('admin/file_import*') ? 'menu-open' : '' }} {{ request()->is('admin/student_update*') ? 'menu-open' : '' }} {{ request()->is('admin/student_transfer*') ? 'menu-open' : '' }}">
                    <a class="nav-link nav-dropdown-toggle">
                        <i class="fad fa-folder"></i>
                        <p>
                            <span>ข้อมูลผู้เข้าสอบ</span>
                            <i class="right fa fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.file_import.index') }}" class="nav-link {{ request()->is('admin/file_import') || request()->is('admin/file_import/*') ? 'active' : '' }}">
                                <i class="fad fa-chevron-circle-right"></i>
                                <p>
                                    <span>นำเข้าไฟล์ข้อมูล</span>
                                </p>
                            </a>
                        </li>
                        {{-- <li class="nav-item">
                            <a href="{{ route('admin.student_update.index') }}" class="nav-link {{ request()->is('admin/student_update') || request()->is('admin/student_update/*') ? 'active' : '' }}">
                                <i class="fad fa-chevron-circle-right"></i>
                                <p>
                                    <span>ปรับปรุงข้อมูล</span>
                                </p>
                            </a>
                        </li> --}}
                        {{-- <li class="nav-item">
                            <a href="{{ route('admin.student_transfer.index') }}" class="nav-link {{ request()->is('admin/student_transfer') || request()->is('admin/student_transfer/*') ? 'active' : '' }}">
                                <i class="fad fa-chevron-circle-right"></i>
                                <p>
                                    <span>ย้ายศูนย์สอบ</span>
                                </p>
                            </a>
                        </li> --}}
                    </ul>
                </li>
                @endcan
                @can('arrange_seat_access')
                <li class="nav-item has-treeview {{ request()->is('admin/arrange_seat*') ? 'menu-open' : '' }}">
                    <a class="nav-link nav-dropdown-toggle">
                        <i class="fad fa-folder"></i>
                        <p>
                            <span>จัดที่นั่งสอบ</span>
                            <i class="right fa fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.arrange_seat.index') }}" class="nav-link {{ request()->is('admin/arrange_seat') || request()->is('admin/arrange_seat/*') ? 'active' : '' }}">
                                <i class="fad fa-chevron-circle-right"></i>
                                <p>
                                    <span>จัดที่นั่งสอบอัตโนมัติ</span>
                                </p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
                @can('student_recheck_access')
                <li class="nav-item has-treeview {{ request()->is('admin/student_recheck*') ? 'menu-open' : '' }}">
                    <a class="nav-link nav-dropdown-toggle">
                        <i class="fad fa-folder"></i>
                        <p>
                            <span>ตรวจสอบข้อมูล</span>
                            <i class="right fa fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.student_recheck.index') }}" class="nav-link {{ request()->is('admin/student_recheck') || request()->is('admin/student_recheck/*') ? 'active' : '' }}">
                                <i class="fad fa-chevron-circle-right"></i>
                                <p>
                                    <span>เช็คชื่อผู้เข้าสอบ</span>
                                </p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
                @can('report_access')
                <li class="nav-item has-treeview {{ request()->is('admin/reports*') ? 'menu-open' : '' }}">
                    <a class="nav-link nav-dropdown-toggle">
                        <i class="fad fa-folder"></i>
                        <p>
                            <span>รายงาน</span>
                            <i class="right fa fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->is('admin/reports') || request()->is('admin/reports/*') ? 'active' : '' }}">
                                <i class="fad fa-chevron-circle-right"></i>
                                <p>
                                    <span>ใบเซ็นชื่อ</span>
                                </p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
                @can('user_management_access')
                <li class="nav-item has-treeview {{ request()->is('admin/permissions*') ? 'menu-open' : '' }} {{ request()->is('admin/roles*') ? 'menu-open' : '' }} {{ request()->is('admin/users*') ? 'menu-open' : '' }}">
                    <a class="nav-link nav-dropdown-toggle">
                        <i class="fad fa-users"></i>
                        <p>
                            <span>{{ trans('global.userManagement.title') }}</span>
                            <i class="right fa fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('permission_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.permissions.index') }}" class="nav-link {{ request()->is('admin/permissions') || request()->is('admin/permissions/*') ? 'active' : '' }}">
                                <i class="fad fa-user-lock"></i>
                                <p>
                                    <span>การเข้าถึง</span>
                                </p>
                            </a>
                        </li>
                        @endcan
                        @can('role_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->is('admin/roles') || request()->is('admin/roles/*') ? 'active' : '' }}">
                                <i class="fad fa-shield"></i>
                                <p>
                                    <span>สิทธิ์</span>
                                </p>
                            </a>
                        </li>
                        @endcan
                        @can('user_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->is('admin/users') || request()->is('admin/users/*') ? 'active' : '' }}">
                                <i class="fad fa-user"></i>
                                <p>
                                    <span>ผู้ใช้งาน</span>
                                </p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

