<?php

namespace App\Http\Controllers\Api;

use App\Enums\DirectionEnum;
use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RouteController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $request->validate([
            'bus_number' => 'required|string',
            'stops' => 'required|array|min:2',
            'frequency' => 'required|integer|min:1',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'reverse_stops_to_start' => 'required|boolean',
            'stops_to_start' => 'required_if:reverse_stops_to_start,false|array|min:2',
        ]);

        DB::beginTransaction();
        try {
            $routeToEnd = Route::create([
                'bus_number' => $request->bus_number,
                'direction' => DirectionEnum::DIRECTION_TO_END,
            ]);

            foreach ($request->stops as $order => $stopId) {
                RouteStop::create([
                    'route_id' => $routeToEnd->id,
                    'stop_id' => $stopId,
                    'stop_order' => $order,
                ]);
            }

            $scheduleToEnd = Schedule::create([
                'route_id' => $routeToEnd->id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'frequency' => $request->frequency,
            ]);

            $routeToStart = Route::create([
                'bus_number' => $request->bus_number,
                'direction' => DirectionEnum::DIRECTION_TO_START,
            ]);

            $stopsToStart = $request->reverse_stops_to_start
                ? array_reverse($request->stops)
                : $request->stops_to_start;

            foreach ($stopsToStart as $order => $stopId) {
                RouteStop::create([
                    'route_id' => $routeToStart->id,
                    'stop_id' => $stopId,
                    'stop_order' => $order,
                ]);
            }

            $scheduleToStart = Schedule::create([
                'route_id' => $routeToStart->id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'frequency' => $request->frequency,
            ]);


            DB::commit();
            return response()->json(['message' => 'Маршрут успешно создан'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка создания маршрута'], 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'bus_number' => 'required|string',
            'stops' => 'required|array|min:2',
            'reverse_stops_to_start' => 'required|boolean',
            'stops_to_start' => 'required_if:reverse_stops_to_start,false|array|min:2',
        ]);

        DB::beginTransaction();
        try {
            $routeToEnd = Route::where('bus_number', $request->bus_number)->where('direction', DirectionEnum::DIRECTION_TO_END)->firstOrFail();
            $routeToStart = Route::where('bus_number', $request->bus_number)->where('direction', DirectionEnum::DIRECTION_TO_START)->firstOrFail();

            RouteStop::where('route_id', $routeToEnd->id)->delete();
            foreach ($request->stops as $order => $stopId) {
                RouteStop::create([
                    'route_id' => $routeToEnd->id,
                    'stop_id' => $stopId,
                    'stop_order' => $order,
                ]);
            }

            RouteStop::where('route_id', $routeToStart->id)->delete();
            $stopsToStart = $request->reverse_stops_to_start
                ? array_reverse($request->stops)
                : $request->stops_to_start;

            foreach ($stopsToStart as $order => $stopId) {
                RouteStop::create([
                    'route_id' => $routeToStart->id,
                    'stop_id' => $stopId,
                    'stop_order' => $order,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Маршрут успешно обновлен'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка обновления маршрута'], 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'bus_number' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $routes = Route::where('bus_number', $request->bus_number)->get();
            foreach ($routes as $route) {
                RouteStop::where('route_id', $route->id)->delete();
                Schedule::where('route_id', $route->id)->delete();
                $route->delete();
            }

            DB::commit();
            return response()->json(['message' => 'Маршрут успешно удален'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка удаления маршрута'], 500);
        }
    }
}
