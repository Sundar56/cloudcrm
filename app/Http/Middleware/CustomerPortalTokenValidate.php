<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use App\Http\Controllers\Api\BaseController;

class CustomerPortalTokenValidate extends BaseController
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $authorizationHeader = $request->header('Authorization');
        if ($authorizationHeader && preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            $token = $matches[1];

            try {
                // Decode and validate the token
                $decoded = JWTAuth::setToken($token)->getPayload();
                if ($decoded->get('exp') < time()) {
                    return $this->sendError('Unauthorised.', ['error' => 'Token has expired'], 401);
                }
                // Attach the decoded token to the request if needed
                $request->attributes->add(['decoded_token' => $decoded]);
            } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                return $this->sendError('Unauthorised.', ['error' => 'Token has expired'], 401);
            } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                return $this->sendError('Unauthorised.', ['error' => 'Token has invalid'], 401);
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                return $this->sendError('Unauthorised.', ['error' => 'Token could not be parsed'], 401);
            } catch (Exception $e) {
                return $this->sendError('Unauthorised.', ['error' => 'An error occurred while processing the token'], 401);
            }
        } else {
            return $this->sendError('Unauthorised.', ['error' => 'Authorization header not found'], 401);
        }

        return $next($request);
    }
}
