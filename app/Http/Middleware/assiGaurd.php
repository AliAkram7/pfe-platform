<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\JWTAuth;
class assiGaurd extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $gaurd = null )
    {

        if ($gaurd != null){
            auth()->shouldUse($gaurd);
            $token = $request->header('auth-token');
            $request->headers->set('auth-token',(string)$token,true );
            $request->headers->set('Authorization','Bearer  '.$token ,true );
            try {
                // $user = $this->auth->authenticate($request);
                $user = \Tymon\JWTAuth\Facades\JWTAuth::parseToken()->authenticate();

            } catch (TokenExpiredException $th) {
                return response('token expired', 401);
            }
            catch(JWTException $e){
                return response('token refused', 401);
            }

        }

        return $next($request);
    }
}
