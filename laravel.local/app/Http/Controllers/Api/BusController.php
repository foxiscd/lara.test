<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Stop;
use App\Services\ArrivalTimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function findBus(Request $request): JsonResponse
    {

        $fromStopId = $request->query('from');
        $toStopId = $request->query('to');

        $stopFrom = Stop::find($fromStopId);
        $stopTo = Stop::find($toStopId);

        if (!$stopFrom || !$stopTo) {
            return response()->json(['error' => 'Остановка не найдена'], 404);
        }

        $routes = Route::whereHas('routeStops', function ($query) use ($fromStopId, $toStopId) {
            $query->whereIn('stop_id', [$fromStopId, $toStopId]);
        })
            ->with(['routeStops' => function ($query) use ($fromStopId, $toStopId) {
                $query->whereIn('stop_id', [$fromStopId, $toStopId])->orderBy('stop_order');
            }])
            ->get();

        $buses = [];

        foreach ($routes as $route) {
            $fromStop = $route->routeStops->firstWhere('stop_id', $fromStopId);
            $toStop = $route->routeStops->firstWhere('stop_id', $toStopId);

            if ($fromStop && $toStop && $fromStop->stop_order < $toStop->stop_order) {

                $ArrivalTimeService = new ArrivalTimeService;
                $nextArrivals = $ArrivalTimeService->getArrivalsTime($fromStop, $route);

                $endRouteStop = RouteStop::where('route_id', $fromStop->route_id)
                    ->orderBy('stop_order', 'desc')->first();

                $buses[] = [
                    'route' => "Автобус No{$route->bus_number} в сторону ост.{$endRouteStop->stop->name}",
                    'next_arrivals' => $nextArrivals
                ];
            }

        }

        return response()->json([
            'from' => $stopFrom->name,
            'to' => $stopTo->name,
            'buses' => $buses ?? []
        ]);
    }
}
