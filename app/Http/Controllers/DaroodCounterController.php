<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DaroodCount;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DaroodCounterController extends Controller
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum', except: [])
        ];
    }
    /**
     * Display a listing of all counts daily Monthly and Yearly
     */
    public function index(Request $request)
    {

        $totalDailyCounts = DaroodCount::whereDate('created_at', Carbon::today())->where('isActive', 1)->sum('counts');
        $totalMonthlyCounts = DaroodCount::whereMonth('created_at', Carbon::now()->month())->where('isActive', 1)->sum('counts');
        $totalYearlyCounts = DaroodCount::whereYear('created_at', Carbon::now()->year())->where('isActive', 1)->sum('counts');

        return response()->json([
            'totalDailyCounts' => (int) $totalDailyCounts,
            'totalMonthlyCounts' => (int) $totalMonthlyCounts,
            'totalYearlyCounts' => $totalYearlyCounts,
        ], 200);
    }

    /**
     * Display a listing of user specific counts daily Monthly and Yearly
     */
    public function userCounts(Request $request)
    {
        $totalDailyCounts = DaroodCount::where('user_id', $request->user()->id)->whereDate('created_at', Carbon::today())->where('isActive', 1)->sum('counts');
        $totalMonthlyCounts = DaroodCount::where('user_id', $request->user()->id)->whereMonth('created_at', Carbon::now()->month())->where('isActive', 1)->sum('counts');
        $totalYearlyCounts = DaroodCount::where('user_id', $request->user()->id)->whereYear('created_at', Carbon::now()->year())->where('isActive', 1)->sum('counts');

        return response()->json([
            'totalDailyCounts' => (int) $totalDailyCounts,
            'totalMonthlyCounts' => (int) $totalMonthlyCounts,
            'totalYearlyCounts' => (int) $totalYearlyCounts,
        ], 200);
    }

    /**
     * Store a newly created Darood Count
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'counts' => ['required', 'numeric']
        ], [
            'counts.required' => 'Please enter Darood Counts, It is mandatory!',
            'counts.numeric' => 'Please enter counting!'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $counts = $request->user()->daroodCounts()->create([
            'counts' => $request->counts,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        return response()->json([
            'message' => 'Your Darood Counts Addes successfully!',
            'counts' => $counts,
            'user' => $counts->user,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DaroodCount $count)
    {
        return response()->json([
            'message' => 'Resource found',
            'counts' => $count,
            'user' => $count->user,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DaroodCount $count)
    {
        Gate::authorize('modify', $count);
        $validator = Validator::make($request->all(), [
            'counts' => ['required', 'numeric']
        ], [
            'counts.required' => 'Please enter Darood Counts, It is mandatory!',
            'counts.numeric' => 'Please enter counting!'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $count->update([
            'counts' => $request->counts,
            'updated_at' => Carbon::now(),
        ]);
        return response()->json([
            'message' => 'Darood Counter update successfully!',
            'counts' => $count,
            'user' => $count->user,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DaroodCount $count)
    {
        Gate::authorize('modify', $count);
        $count->update([
            'isActive' => false,
            'updated_at' => Carbon::now(),
        ]);
        return response()->json(['message' => 'Your desired count is deleted'], 200);
    }
}
