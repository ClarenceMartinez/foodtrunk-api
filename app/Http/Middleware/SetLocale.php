<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Idiomas soportados por la plataforma. Agregar uno nuevo es tan
     * simple como sumarlo aquí y crear su carpeta en /lang.
     */
    private const SUPPORTED_LOCALES = ['es', 'en'];

    private const DEFAULT_LOCALE = 'es';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        // 1. Parámetro explícito ?lang=en (el más específico, gana siempre).
        $queryLocale = $request->query('lang');
        if ($queryLocale && in_array($queryLocale, self::SUPPORTED_LOCALES, true)) {
            return $queryLocale;
        }

        // 2. Header Accept-Language que manda el navegador o la app móvil.
        $header = $request->header('Accept-Language');
        if ($header) {
            $preferred = substr($header, 0, 2);
            if (in_array($preferred, self::SUPPORTED_LOCALES, true)) {
                return $preferred;
            }
        }

        // 3. Por defecto.
        return self::DEFAULT_LOCALE;
    }
}
