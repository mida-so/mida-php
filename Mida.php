<?php
require_once 'vendor/autoload.php';
use GuzzleHttp\Client;

class Mida {
    protected $publicKey;
    protected $host;
    protected $user_id;
    protected $enabled_features;
    protected $maxCacheSize;
    protected $featureFlagCache;

    function __construct($publicKey, $options = []) {
        if (!$publicKey) {
            throw new Exception("You must pass your Mida project key");
        }

        $this->publicKey = $publicKey;
        $this->host = 'https://api.mida.so';
        $this->user_id = null;
        $this->enabled_features = [];
        $this->maxCacheSize = isset($options['maxCacheSize']) ? $options['maxCacheSize'] : 50000;
        $this->featureFlagCache = new ArrayObject();
    }

    function getExperiment($experimentKey, $distinctId) {
        if (!$experimentKey || !$distinctId || !$this->user_id) {
            throw new Exception("You must pass your Mida experiment key. You must pass your user distinct ID");
        }
        $data = [
            'key' => $this->publicKey,
            'experiment_key' => $experimentKey,
            'distinct_id' => $distinctId || $this->user_id
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

    function setEvent($eventName, $distinctId, $properties = []) {
        if (!$eventName || !$distinctId) {
            throw new Exception("You need to set an event name. You must pass your user distinct ID");
        }
        $data = [
            'key' => $this->publicKey,
            'name' => $eventName,
            'distinct_id' => $distinctId || $this->user_id,
            'properties' => json_encode($properties)
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

    function setAttribute($distinctId, $properties = []) {
        if (!$distinctId || !$this->user_id) {
            throw new Exception("You must pass your user distinct ID");
        }
        if (!$properties) {
            throw new Exception("You must pass your user properties");
        }
        $data = $properties;
        $data['id'] = $distinctId || $this->user_id;
        $headers = [];
        $client = new Client([
            'base_uri' => $this->host
        ]);
        $response = $client->request('POST', '/track/' . $this->publicKey, [
            'headers' => $headers,
            'json' => $data
        ]);
        return true;
    }

    function cachedFeatureFlag() {
        $cacheKey = $this->publicKey . ':' . $this->user_id;
        if ($this->featureFlagCache->offsetExists($cacheKey)) {
            return $this->featureFlagCache->offsetGet($cacheKey);
        }
        return [];
    }

    function isFeatureEnabled($key) {
        $this->enabled_features = $this->cachedFeatureFlag();
        return in_array($key, $this->enabled_features);
    }

    function onFeatureFlags($distinctId = null) {
        $cachedItems = count($this->cachedFeatureFlag());
        try {
            $this->reloadFeatureFlags($distinctId);
            if (!$cachedItems) {
                return true;
            }
        } catch (Exception $e) {
            throw $e;
        }
        if ($cachedItems) {
            return true;
        }
    }

    function reloadFeatureFlags($distinctId = null) {
        $data = [
            'key' => $this->publicKey,
            'user_id' => $distinctId
        ];
        $headers = [];
        $client = new Client([
            'base_uri' => $this->host
        ]);
        $response = $client->request('POST', '/feature-flag', [
            'headers' => $headers,
            'json' => $data
        ]);
        $this->enabled_features = json_decode($response->getBody()->getContents(), true);
        $cacheKey = $this->publicKey . ':' . $this->user_id;
        $this->featureFlagCache->offsetSet($cacheKey, $this->enabled_features);

        if ($this->featureFlagCache->count() > $this->maxCacheSize) {
            $oldestKey = $this->featureFlagCache->key();
            $this->featureFlagCache->offsetUnset($oldestKey);
        }
        return true;
    }
}
