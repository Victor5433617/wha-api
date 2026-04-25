<?php
$token = '2b$10$Tebv4lzK2JJQuU470moOcuu1j_XLbDTj1nVaLxvimLqQreRT8QKeK'; // token de la wppconnect-api
$url = 'http://localhost:21465/api/victor/start-session';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

header('Content-Type: application/json');
echo $response;
