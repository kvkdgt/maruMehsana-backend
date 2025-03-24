<?php

namespace App\Http\Controllers;
use App\Models\BusinessEnquiry;
use Illuminate\Http\Request;

class BusinessEnquiryController extends Controller {
    // 1️⃣ Create Business Enquiry (Status defaults to "Pending")
    public function create(Request $request) {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:15',
            'whatsapp_no' => 'required|string|max:15',
        ]);

        $enquiry = BusinessEnquiry::create($request->only(['business_name', 'owner_name', 'mobile_no', 'whatsapp_no']));

        return response()->json(['message' => 'Business enquiry created successfully!', 'data' => $enquiry], 201);
    }


    // 3️⃣ Update Status of a Business Enquiry
    public function updateStatus(Request $request, $id) {
        $request->validate([
            'status' => 'required|in:Pending,Done',
        ]);

        $enquiry = BusinessEnquiry::find($id);
        if (!$enquiry) {
            return response()->json(['message' => 'Enquiry not found'], 404);
        }

        $enquiry->update(['status' => $request->status]);

        return response()->json(['message' => 'Status updated successfully!', 'data' => $enquiry]);
    }
}
