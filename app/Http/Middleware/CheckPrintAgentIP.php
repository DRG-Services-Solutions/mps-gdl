<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPrintAgentIP
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = config('rfid.allowed_ips', []);
        $clientIp = $request->ip();

        // Verificar si la IP está en la whitelist
        foreach ($allowedIps as $allowedIp) {
            if ($this->ipInRange($clientIp, $allowedIp)) {
                return $next($request);
            }
        }

        // IP no autorizada
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized IP address',
        ], 403);
    }

    /**
     * Verificar si una IP está en un rango CIDR
     */
    private function ipInRange(string $ip, string $range): bool
    {
        // Si no es un rango CIDR, comparación directa
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        // Soporte para CIDR (ej: 192.168.0.0/24)
        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        return ($ip & $mask) == $subnet;
    }
}