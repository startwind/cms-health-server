<?php

namespace App\Business\Storage;

class FileStorage implements Storage
{
    private string $directory;

    public function __construct(string $directory)
    {
        $this->directory = $directory;

        if (!file_exists($directory)) {
            mkdir($directory);
        }
    }

    public function storeHealthCheckResult(string $endpoint, array $healthCheckFormat): void
    {
        $recentCheck = $this->getHealthCheckResult($endpoint);

        if ($recentCheck['status'] == $healthCheckFormat['status']) {
            if (array_key_exists('status_since', $recentCheck['_internal'])) {
                $healthCheckFormat['_internal']['status_since'] = $recentCheck['_internal']['status_since'];
            } else {
                $healthCheckFormat['_internal']['status_since'] = time();
            }
        } else {
            $healthCheckFormat['_internal']['status_since'] = time();
        }

        file_put_contents($this->directory . '/' . md5($endpoint) . ',json', json_encode($healthCheckFormat));
    }

    public function getHealthCheckResult(string $endpoint): array
    {
        $rawJson = file_get_contents($this->directory . '/' . md5($endpoint) . ',json');

        return json_decode($rawJson, true);
    }
}
