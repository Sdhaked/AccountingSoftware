<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeveloperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $isDeveloperAdmin = $request->user()?->roleModel()
            ->where('slug', 'developer-admin')
            ->exists() ?? false;

        abort_unless($isDeveloperAdmin, 403);

        return $next($request);
    }
}
