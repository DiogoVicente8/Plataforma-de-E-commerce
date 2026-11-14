<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Consulta os códigos postais portugueses no GEO API PT (https://geoapi.pt).
class PostalCodeService
{
    // Devolve a localidade do código postal, ou null se o serviço não a souber ou não responder.
    public function locality(string $postalCode): ?string
    {
        try {
            $response = Http::baseUrl(config('services.geoapi.url'))
                ->timeout(config('services.geoapi.timeout'))
                ->acceptJson()
                ->get("/cp/{$postalCode}");
        } catch (ConnectionException $exception) {
            Log::warning('GEO API PT indisponível.', [
                'postal_code' => $postalCode,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        // 404 quer dizer que o serviço não conhece o código: não é uma falha do serviço.
        if ($response->failed() && ! $response->notFound()) {
            Log::warning('GEO API PT respondeu com erro.', [
                'postal_code' => $postalCode,
                'status' => $response->status(),
            ]);
        }

        return $response->json('Localidade') ?: null;
    }
}
