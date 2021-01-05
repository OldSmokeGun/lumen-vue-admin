<?php

namespace App\Http\Middleware;

use App\Facades\HttpResponse;
use App\Models\Admin as AdminModel;
use App\Utils\HttpResponse\HttpResponseCode;
use Closure;

class PermissionCheck
{
    private $whiteList = [
        '/api/login',
        '/api/logout',
        '/api/admins/info',
        '/api/admins/update-password',
        '/upload/file'
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $pathInfo = $request->getPathInfo();

        if (!in_array($pathInfo, $this->whiteList)) {
            $admin = $request->admin;

            if (!$admin) {
                return HttpResponse::failedResponse(HttpResponseCode::LOGIN_INVALID_CODE_MESSAGE, HttpResponseCode::LOGIN_INVALID_CODE);
            }

            if ((int)$admin->id !== 1) {
                $hasPermission = $admin->hasAdminPermissionByToken($token, $pathInfo);

                if (!$hasPermission) {
                    return HttpResponse::failedResponse(HttpResponseCode::PERMISSION_DENY_CODE_MESSAGE, HttpResponseCode::PERMISSION_DENY_CODE);
                }
            }
        }

        return $next($request);
    }
}
