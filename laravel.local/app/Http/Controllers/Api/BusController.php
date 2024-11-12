<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Stop;
use App\Services\ArrivalTimeService;
use App\Services\SchedulerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function findBus(Request $request, SchedulerService $schedullerService): JsonResponse
    {
        $request->validate([
            'from' => 'required|exists:stops,id',
            'to' => 'required|exists:stops,id',
        ]);

        try {
            $stopFrom = Stop::find($request->input('from'));
            $stopTo = Stop::find($request->input('to'));

            $buses = $schedullerService->getNextBuses($request->query('from'), $request->query('to'));
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception], 404);
        }

        return response()->json([
            'from' => $stopFrom->name,
            'to' => $stopTo->name,
            'buses' => $buses
        ]);
    }
}
