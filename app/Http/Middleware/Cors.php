<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $trusted_domains = [
                                "http://localhost:4200",
                                "http://localhost:4300",
//                                "https://dev.privacy4you.online",
//                                "http://dev.privacy4you.online",
                                "https://qa.privacy4you.online",
                                "http://qa.privacy4you.online",
                                "https://www.qa.privacy4you.online",
                                "http://www.qa.privacy4you.online",
                                "http://192.168.18.15:4200",
                                "http://binder.technocares.com",
                                "https://binder.technocares.com"
//                                "https://privacy4you.online",
//                                "http://privacy4you.online",
//                                "https://www.privacy4you.online",
//                                "http://www.privacy4you.online"
        ];

        if(isset($request->server()['HTTP_ORIGIN'])) {
            $origin = $request->server()['HTTP_ORIGIN'];

            if(in_array($origin, $trusted_domains)) {
                header('Access-Control-Allow-Origin: ' .$origin);
                header('Access-Control-Allow-Credentials:true');
                header('Access-Control-Allow-Methods:GET,POST,PUT,PATCH,DELETE,OPTIONS');
                header('Access-Control-Allow-Headers: Origin, Content-Type,X-Requested-With,X-XSRF-TOKEN,Authorization,Accept,X-localization,responseType,observe,Role-ID,observe,X-Frame-Options');
            }
        }
        return $next($request);
    }
}
