<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\StatsService;

class StatsController extends Controller
{
    public function __construct(private readonly StatsService $statsService)
    {
    }

    public function index()
    {
        return response()->json($this->statsService->tableauDeBord());
    }
}
