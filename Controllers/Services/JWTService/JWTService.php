<?php

namespace Controllers\Services\JWTService;

use DateTimeImmutable;
use Symfony\Component\Mime\Encoder\Base64Encoder;

class JWTService
{
    //on génère le token

    /**
     * *Génération du JWT
     * @param array $header
     * @param array $payload
     * @param string $secret
     * @param int $validity (expiration du token)
     * @return string
     */

    public function generate(array $header, array $payload, string $secret, int $validity): string
    {

        if ($validity > 0) {
            $now = new DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;

            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }


        //on encode en base64
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        //on "nettoie" les valeurs encodées (retrait de +, / et =)
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header);
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);

        //génère la signature

        $secret = base64_encode($secret);

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);

        $base64Signature = base64_encode($signature);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64Signature);

        //on créer le token
        $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;

        return $jwt;
    }

    //on Vérifie que le token est valide (correctement formé)
    public function isValid(string $token): bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
            $token
        ) === 1;
    }

    //on récupère le payload (qui nous servira pour la vérification de l'expiration)
    public function getPayload(string $token)
    {
        //on démonte le token
        $array = explode('.', $token);

        //on décode la payload
        $payload = json_decode(base64_decode($array[1]), true);

        return $payload;
    }

    public function getHeader(string $token)
    {
        //on démonte le token
        $array = explode('.', $token);

        //on décode la Header
        $header = json_decode(base64_decode($array[0]), true);

        return $header;
    }
    // on vérif si le token est expiré
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);
        $now = new DateTimeImmutable();

        return $payload['exp'] < $now->getTimestamp();
    }

    //on vérifie la signature du token
    public function check(string $token, string $secret)
    {
        //on récupère le header
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        //on génénère un token

        $verifToken = $this->generate($header, $payload, $secret, 0);

        return $token === $verifToken;
    }
}
