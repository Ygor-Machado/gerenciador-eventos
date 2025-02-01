<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AddressServices
{
    public function getZipCode(string $zipcode): array
    {
        $zipcode = preg_replace('/[^0-9]/', '', $zipcode);

        if (strlen($zipcode) !== 8) {
            throw new \Exception('CEP inválido');
        }

        $response = Http::withoutVerifying()
        ->get("https://viacep.com.br/ws/{$zipcode}/json/");

        if ($response->json('erro') === true) {
            throw new \Exception('CEP não encontrado');
        }

        return $response->json();
    }
}
