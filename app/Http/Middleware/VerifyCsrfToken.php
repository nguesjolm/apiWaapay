<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
       //
        /* --------------
          SYSTEM DE PAIEMENT
        ----------------*/
        'api/notify_pay',
        'api/return_pay',
        'api/notify_transfert',
        'api/return_transfert'
    ];
}
