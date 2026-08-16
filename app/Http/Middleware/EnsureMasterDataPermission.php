<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMasterDataPermission
{
    public function handle(Request $request, Closure $next, string $action): Response
    {
        $entity = (string) $request->route('entity');
        $permissions = [
            "{$entity}-{$action}-{$entity}",
            "{$entity}-manage-{$entity}",
        ];

        abort_unless($request->user()?->hasAnyPermission($permissions), 403);

        return $next($request);
    }
}
