<?php

namespace App\Http\Middleware;

use App\Models\HttpReqLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogHttpRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $type): Response
    {
        $requestData = [
            'url'       => $request->fullUrl(),
            'by'    => $type,
            'method'    => $request->method(),
            'ip'        => $request->ip(),
            'payload'      => json_encode($request->all()),
            'agent'      => $request->userAgent(),
            'referer'   => $request->headers->get('referer'),
        ];

        // Use Eloquent to save data to the requests table
        $httpLog = HttpReqLog::create($requestData);

        $response = $next($request);

        $httpLog->response_content = $response;
        $httpLog->status = 'Success';
        $httpLog->save();

        return $response;
    }
}
