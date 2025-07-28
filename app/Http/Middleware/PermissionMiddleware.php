<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Models\Permission;
use App\Models\RoleHasPermission;
use App\Http\Controllers\Api\BaseController;

class PermissionMiddleware extends BaseController
{
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, $permission = null, $guard = null)
    {
        $permission = $request->route()->getName();
        // dd($request->moduleType);
        $routeNameExplode = explode('.',$permission);

        /* add this condition for crm,cms and shop  permission module*/
        if($routeNameExplode[1] == "privileges"){
            $routeNameExplode[1] = $request->moduleType;
            $permission = implode('.',$routeNameExplode);
        }

        $roleId = $request->attributes->get('decoded_token')->get('roleId');
        $permissions = array($permission);
       
        $permission_id = Permission::where('name', $permissions)->pluck('id')->first();
        if (isset($permission_id)) {

            $hasPermission = RoleHasPermission::where('permission_id', $permission_id)->where('role_id', $roleId)->first();
                    
            if (isset($hasPermission)) {
                return $next($request);
            } else {
                return $this->sendError('Unauthorised.', ['error' => 'Permission Denied'], 401);
            }
        }
    }
}
