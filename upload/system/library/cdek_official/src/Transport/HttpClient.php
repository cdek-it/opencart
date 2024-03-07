<?php

namespace CDEK\Transport;

use CDEK\RegistrySingleton;
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
        $headers[] = 'X-App-Name: opencart';
        curl_setopt_array($ch, array(
            CURLOPT_USERAGENT => 'oc/2.0',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
        ));
        $response = curl_exec($ch);
        curl_close($ch);

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $result = substr($response, $headerSize);
        $addedHeaders = array_filter(explode("\r\n", $headers), static fn ($line) =>
            !empty($line) && stripos($line, 'X-') !== false
        );

        if(count($addedHeaders)){
            $response = RegistrySingleton::getInstance()->get('response');
            foreach($addedHeaders as $header){
                $response->addHeader($header);
            }
        }

        return $raw ? $result : json_decode($result, true, 512, JSON_THROW_ON_ERROR);
    }
}
