<?php

namespace App\Http\Controllers;

use App\Models\Year_scholar;
use Illuminate\Http\Request;

class YearScholarController extends Controller
{
    public function fetchYearsScholar(Request $request)
    {

        return Year_scholar::select()->orderBy('start_date', 'desc')
        ->get();

    }
}
