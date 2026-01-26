<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use App\Models\ServiceAssign;
use App\User;
use App\Models\ServiceRequest;
use App\Models\Priority;
use App\Models\ServiceStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController
{
    public function index()
    {
        return view('home');
    }
}
