<?php

namespace App\Http\Middleware;

use App\Facades\HttpResponse;
use App\Facades\Jwt;
use App\Models\Admin as AdminModel;
use App\Utils\HttpResponse\HttpResponseCode;
use Closure;

class AuthCheck
{
    private $whiteList = [
        '/api/login'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $pathInfo  = $request->getPathInfo();

        if ( !in_array($pathInfo, $this->whiteList) )
        {
            $token = $request->header('Authorization');

            if (!$token)
            {
                return HttpResponse::failedResponse(HttpResponseCode::LOGIN_INVALID_CODE_MESSAGE, HttpResponseCode::LOGIN_INVALID_CODE);
            }

            $status = Jwt::validate($token);

            if ( (int) $status !== 0 )
            {
                return HttpResponse::failedResponse(HttpResponseCode::LOGIN_INVALID_CODE_MESSAGE, HttpResponseCode::LOGIN_INVALID_CODE);
            }

            $admin = (new AdminModel())->findByToken($token);

            if ( !$admin )
            {
                return HttpResponse::failedResponse(HttpResponseCode::LOGIN_INVALID_CODE_MESSAGE, HttpResponseCode::LOGIN_INVALID_CODE);
            }

            if ( !$admin->status )
            {
                return HttpResponse::failedResponse(HttpResponseCode::LOGIN_INVALID_CODE_MESSAGE, HttpResponseCode::LOGIN_INVALID_CODE);
            }

            if ( (string) $admin->token !== (string) $token )
            {
                return HttpResponse::failedResponse(HttpResponseCode::LOGIN_INVALID_CODE_MESSAGE, HttpResponseCode::LOGIN_INVALID_CODE);
            }
        }

        return $next($request);
    }
}
