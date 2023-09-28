<?php

class CdekLog
{
    public static function sendLog($text)
    {
        file_put_contents('test_log.txt', $text . "\n", FILE_APPEND);
    }
}