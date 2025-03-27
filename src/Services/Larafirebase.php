<?php

namespace Aweiand\Larafirebase\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Aweiand\Larafirebase\Exceptions\UnsupportedTokenFormat;
use Google\Client;

class Larafirebase
{
    const PRIORITY_NORMAL = 'normal';

    private $title;

    private $body;

    private $clickAction;

    private $image;

    private $icon;

    private $additionalData;

    private $sound;

    private $priority = self::PRIORITY_NORMAL;

    private $fromArray;

    private $authenticationKey;

    private $fromRaw;    

    public function withTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function withBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function withClickAction($clickAction)
    {
        $this->clickAction = $clickAction;

        return $this;
    }

    public function withImage($image)
    {
        $this->image = $image;

        return $this;
    }

    public function withIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    public function withSound($sound)
    {
        $this->sound = $sound;

        return $this;
    }

    public function withPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    public function withAdditionalData($additionalData)
    {
        $this->additionalData = $additionalData;

        return $this;
    }

    public function withAuthenticationKey($authenticationKey)
    {
        $this->authenticationKey = $authenticationKey;

        return $this;
    }

    public function fromArray($fromArray)
    {
        $this->fromArray = $fromArray;

        return $this;
    }

    public function fromRaw($fromRaw)
    {
        $this->fromRaw = $fromRaw;

        return $this;
    }

    public function sendNotification($tokens)
    {
        $fields = array(
            'message' => [
                'token' => $this->validateToken($tokens),
                'notification' => ($this->fromArray) ? $this->fromArray : [
                    'title' => $this->title,
                    'body' => $this->body,
                    'image' => $this->image,
                ],
                'data' => $this->additionalData,
                'android' => [
                    'notification' => [
                        'icon' => $this->icon,
                        'sound' => $this->sound,
                        'click_action' => $this->clickAction,
                    ],
                    'priority' => $this->priority,
                ],
            ],
        );

        return $this->callApi($fields);
    }

    public function sendMessage($tokens)
    {
        $data = ($this->fromArray) ? $this->fromArray : [
            'title' => $this->title,
            'body' => $this->body,
        ];

        $data = $this->additionalData ? array_merge($data, $this->additionalData) : $data;

        $fields = array(
            'message' => [
                'token' => $this->validateToken($tokens),
            ],
            'data' => $data,
        );

        return $this->callApi($fields);
    }

    public function send()
    {
        return $this->callApi($this->fromRaw);
    }

    private function callApi($fields): Response
    {
        $api_url = 'https://fcm.googleapis.com/v1/projects/'. config('larafirebase.firebase_project_id') . '/messages:send';

        $credentialsFilePath = base_path(config('larafirebase.firebase_credentials_file'));
        $client = new Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token['access_token'],
            'Content-Type'  => 'application/json'
        ])->withOptions([
            'verify' => false, // Equivalente ao CURLOPT_SSL_VERIFYPEER, false (não recomendado em produção)
            'debug'  => false,  // Equivalente ao CURLOPT_VERBOSE, true (para depuração)
        ])->post($api_url, $fields);

        return $response;
    }

    private function curl__callApi($fields)
    {
        $api_url = 'https://fcm.googleapis.com/v1/projects/'. config('larafirebase.firebase_project_id') . '/messages:send';

        $credentialsFilePath = base_path(config('larafirebase.firebase_credentials_file'));
        $client = new Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();

        $access_token = $token['access_token'];

        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json'
        ];

        $payload = json_encode($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output for debugging
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        dd($response);
        if ($err) {
            return response()->json([
                'message' => 'Curl Error: ' . $err
            ], 500);
        } else {
            return response()->json([
                'message' => 'Notification has been sent',
                'response' => json_decode($response, true)
            ]);
        }
    }

    private function validateToken($tokens)
    {
        if (is_array($tokens)) {
            throw new UnsupportedTokenFormat('Please pass tokens as string.');
        } else {
            return $tokens;
        }
    }
}
