<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSurgeryPackage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $surgery = $request->route('surgery');
        if ($surgery->preparation  && $surgery->preAssembledPackage){
            return redirect()->route('surgeries.show', $surgery->id)
            ->with('error', 'Esta cirugia ya tiene un paquete asignado');

        }
        ;
        return $next($request);
    }
}
