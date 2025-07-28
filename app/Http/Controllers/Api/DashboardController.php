<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Api\Systemadmin\Modules\Company\Models\Company;
use App\Api\Systemadmin\Modules\Adminuser\Models\User;

class DashboardController extends BaseController
{
    /**
     * Display the dashboard with relevant statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $userCount = User::count();
            $companyCount = Company::count(); 

            $statistics = [
                'userCount' => $userCount,
                'companyCount' => $companyCount,
            ];

            return $this->sendResponse($statistics, 'Dashboard statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('An error occurred while fetching dashboard statistics.', ['error' => $e->getMessage()], 500);
        }
    }
}
