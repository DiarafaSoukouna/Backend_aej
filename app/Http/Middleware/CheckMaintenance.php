<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Configuration;

class CheckMaintenance
{
    public function handle(Request $request, Closure $next)
    {
        $configuration = Configuration::first();
        
        if ($configuration && $configuration->mise_en_maintenance) {
            $allowedRoutes = [
                'login',
                'auth/login',
                'auth/send-otp',
                'auth/verify-otp',
                'auth/refresh',
                'configurations',
                'configurations/index',
                'configurations/store',
                'configurations/patch',
            ];
            
            $currentRoute = $request->route() ? $request->route()->getName() : $request->path();
            
            if (!in_array($currentRoute, $allowedRoutes) && !$request->is('api/configurations*')) {
                return response()->json([
                    'message' => 'Le système est actuellement en maintenance',
                    'data' => [
                        'sigle_systeme' => $configuration->sigle_systeme,
                        'intitule_systeme' => $configuration->intitule_systeme,
                    ]
                ], 503);
            }
        }
        
        return $next($request);
    }
}
