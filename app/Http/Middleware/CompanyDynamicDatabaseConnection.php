<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\CompanyDatabaseService;

class CompanyDynamicDatabaseConnection
{

    protected $companyDatabaseService;

    public function __construct(CompanyDatabaseService $companyDatabaseService)
    {
        $this->companyDatabaseService = $companyDatabaseService;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->attributes->get('decoded_token')->get('id');
        $companyId = $request->attributes->get('decoded_token')->get('companyId');

        $connection = $this->companyDatabaseService->connect($companyId);
        
        // Return error if connection fails
        if (!$connection['status']) {
            return response()->json([
                'status'     => false,
                'message'    => $connection['message'],
                'errors'     => $connection['errors'],
                'statusCode' => $connection['statusCode'],
            ], $connection['statusCode']);
        }

         // Attach connection details to request
         $request->merge([
            'dbName' => $connection['dbName'],
            'userId'    => $userId,
            'companyId'    => $companyId,
        ]);
        return $next($request);
    }
}
