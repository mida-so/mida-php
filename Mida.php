<?php

require_once 'vendor/autoload.php';

use GuzzleHttp\Client;

class Mida {
    protected $publicKey;
    protected $host;
    protected $user_id;
    
    function __construct($publicKey, $options = []) {
        if (!$publicKey) {
            throw new Exception("You must pass your Mida project key");
        }
        
        $this->publicKey = $publicKey;
        $this->host = 'https://api.mida.so';
        $this->user_id = null;
    }

    function getExperiment($experimentKey, $distinctId) {
        if (!$experimentKey || !$this->user_id) {
            throw new Exception("You must pass your Mida experiment key. You must pass your user distinct ID");
        }

        $data = [
            'key' => $this->publicKey,
            'experiment_key' => $experimentKey,
            'distinct_id' => $this->user_id
        ];

        $headers = [];

        $client = new Client([
            'base_uri' => $this->host
        ]);

        $response = $client->request('POST', '/experiment/query', [
            'headers' => $headers,
            'json' => $data
        ]);

        $json = json_decode($response->getBody()->getContents(), true);

        if ($json['version']) {
            return $json['version'];
        }

        return null;
    }

    function setEvent($eventName, $distinctId) {
        if (!$eventName || !$distinctId) {
            throw new Exception("You need to set an event name. You must pass your user distinct ID");
        }

        $data = [
            'key' => $this->publicKey,
            'name' => $eventName,
            'distinct_id' => $distinctId || $this->user_id
        ];

        $headers = [];

        $client = new Client([
            'base_uri' => $this->host
        ]);

        $response = $client->request('POST', '/experiment/event', [
            'headers' => $headers,
            'json' => $data
        ]);

        return true;
    }
}
