<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/dataloader/load', //Отключаем токен для пакетной загрузки,
                            //потому что при загрузке больше двух лет он успевает просрочиться и прерывает процесс загрузки.
                            //а загрузка может быть и за 10 лет.
        '/analytic/getchartdata',    //чтобы не перезагружать страницу аналитики
    ];
}
