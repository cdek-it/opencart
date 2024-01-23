<?php

namespace CDEK;

use RuntimeException;

class CdekHttpClient
{
    public function sendRequest($url, $method, $token, $data = null, $raw = false)
    {
        if (strtoupper($method) === 'GET' && is_array($data)) {
            $data = http_build_query($data);
            $url .= '?' . $data;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        $headers = [
            "Authorization: Bearer $token",
            'Content-Type: application/json'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if (strtoupper($method) === 'POST' && is_array($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        $output = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($output, 0, $headerSize);
        $result = substr($output, $headerSize);
        $addedHeaders = $this->getHeaderValue($headers);
        if ($result === false) {
            throw new RuntimeException(curl_error($ch), curl_errno($ch));
        }

        return ['result' => $raw ? $result : json_decode($result), 'addedHeaders' => $addedHeaders];
    }

    private function getHeaderValue($headers): array
    {
        $headerLines = explode("\r\n", $headers);
        $addedHeaders = [];
        foreach ($headerLines as $line) {
            if (!empty($line)) {
                $parts = explode(': ', $line, 2);
                $key = $parts[0];
                $value = isset($parts[1]) ? $parts[1] : '';
                $addedHeaders[$key] = $value;
            }
        }
        return $addedHeaders;
    }

    public function sendRequestAuth($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        $postDataStr = http_build_query($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataStr);
        $response = curl_exec($ch);
        curl_close($ch);

        $body = json_decode($response);

        if ($body === null || property_exists($body, 'error')) {
            return false;
        }

        return $body->access_token;
    }

    public function sendRequestBill($url, $token)
    {
        $ch = curl_init($url);
        $headers = [
            "Authorization: Bearer $token",
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);

        header('Content-Type: application/pdf');
        return $output;
    }

}
