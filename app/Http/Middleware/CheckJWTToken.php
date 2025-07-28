<?php 

use App\Http\Controllers\API\BaseController;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Illuminate\Http\Request;
use Closure;

class CheckJWTToken
{
    protected $baseController;

    public function __construct(BaseController $baseController)
    {
        $this->baseController = $baseController;
    }

    public function handle(Request $request, Closure $next)
    {
        try {
            // Check if the token is valid
            JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            // Return a custom error response for expired token
            return $this->baseController->sendError('Token expired', ['error' => 'The token has expired. Please log in again.'], 401);
        } catch (TokenBlacklistedException $e) {
            // Return a custom error response for blacklisted token
            return $this->baseController->sendError('Token blacklisted', ['error' => 'The token has been blacklisted.'], 401);
        } catch (UnauthorizedHttpException $e) {
            // Catch UnauthorizedHttpException thrown by tymon/jwt-auth
            return $this->baseController->sendError('Unauthorized', ['error' => 'Invalid or blacklisted token.'], 401);
        } catch (JWTException $e) {
            // Return a custom error response for general JWT exceptions
            return $this->baseController->sendError('Unauthorized', ['error' => 'Token is invalid or malformed.'], 401);
        }

        return $next($request);
    }
}
