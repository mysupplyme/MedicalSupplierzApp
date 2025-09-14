<?php

namespace App\Http\Controllers;

use App\Reports\MyReport;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $report = new MyReport;
        $report->run();
        
        return view('report', compact('report'));
    }
}