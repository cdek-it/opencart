<?php

namespace CDEK\Transport;

use CDEK\Exceptions\HttpServerException;
use CDEK\Exceptions\UnparsableAnswerException;
use CDEK\RegistrySingleton;
use JsonException;

class HttpClient
{
    /**
     * @param string $url
     * @param string $method
     * @param string $token
     * @param null $data
     * @param bool $raw
     * @return array|string
     * @throws HttpServerException
     * @throws UnparsableAnswerException
     */
    public static function sendCdekRequest(string $url, string $method, string $token, $data = null, bool $raw = false)
    {
        return self::sendRequest($url, $method, $data, ["Authorization: Bearer $token"], $raw);
    }

    /**
     * @param string $url
     * @param string $method
     * @param null $data
     * @param array $headers
     * @param bool $raw
     * @return array|string
     * @throws HttpServerException
     * @throws UnparsableAnswerException
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

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if(self::isServerError($httpCode)){
            throw new HttpServerException(
                [
                    'message' => 'Server request error',
                    'code' => $httpCode,
                    'url' => $url,
                    'method' => $method,
                ]
            );
        }

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

        try {
            return $raw ? $result : json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new UnparsableAnswerException(
                'CDEK API response is not valid JSON',
                $url,
                $method
            );
        }
    }

    private static function isServerError(int $httpCode): bool
    {
        return $httpCode >= 500 && $httpCode < 600;
    }
}
