<?php

namespace App\Http\Controllers;

use App\Support\WebVisit;
use Illuminate\Http\Request;

/** POST /visit: the browser reports one real website view (see App\Support\WebVisit). */
class WebVisitController extends Controller
{
    public function store(Request $request)
    {
        WebVisit::record($request);

        // Same empty reply either way, so a caller cannot probe what is counted.
        return response()->noContent();
    }
}
