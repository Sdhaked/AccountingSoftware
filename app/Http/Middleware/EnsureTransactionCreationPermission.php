<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTransactionCreationPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $type = (string) $request->route('type');
        abort_unless(in_array($type, ['income', 'expense'], true), 404);

        abort_unless($request->user()?->hasAnyPermission([
            "transactions-create-{$type}",
            'transactions-manage-transactions',
        ]), 403);

        return $next($request);
    }
}
