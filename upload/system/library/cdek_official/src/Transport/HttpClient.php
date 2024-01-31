<?php

namespace CDEK\Transport;

use JsonException;

class HttpClient
{
    /**
     * @throws JsonException
     */
    public static function sendCdekRequest(string $url, string $method, string $token, $data = null, bool $raw = false)
    {
        return self::sendRequest($url, $method, $data, ["Authorization: Bearer $token"], $raw);
    }

    /**
     * @param array|string $data
     * @return array|string
     * @throws JsonException
     */
    public static function sendRequest(
        string $url,
        string $method = 'GET',
        $data = null,
        array $headers = [],
        bool $raw = false
    ) {
        if (is_array($data) && strtoupper($method) === 'GET') {
            $data = http_build_query($data);
            $url  .= '?' . $data;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (is_array($data)) {
                $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
            } else {
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);

        return $raw ? $response : json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}
