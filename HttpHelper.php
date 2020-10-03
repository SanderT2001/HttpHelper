<?php

namespace HttpHelper;

class HttpHelper
{
    protected $curl_info_keys = [
        'url',
        'content_type',
        'http_code',
        'redirect_url',
        'http_version',
        'protocol',
        'scheme'
    ];

    public function getCurlInfoKeys(): array
    {
        return $this->curl_info_keys;
    }

    public function setCurlInfoKeys(array $keys): self
    {
        $this->curl_info_keys = $keys;
        return $this;
    }

    public function get(
        string $url,
        array $headers = [],
        array $curl_options = []
    ): object {
        if (!empty($headers))
            $curl_options['httpheader'] = $headers;

        return $this->getCurlResult($this->getCurlHandler($url, $curl_options));
    }

    public function post(
        string $url,
        string $postdata,
        array $headers = [],
        array $curl_options = []
    ): object {
        $curl_options = array_merge($curl_options, [
            'post' => 1,
            'postfields' => $postdata
        ]);
        if (!empty($headers))
            $curl_options['httpheader'] = $headers;

        return $this->getCurlResult($this->getCurlHandler($url, $curl_options));
    }

    public function put(
        string $url,
        string $postdata,
        array $headers = [],
        array $curl_options = []
    ): object {
        $curl_options = array_merge($curl_options, [
            'customrequest' => 'PUT',
            'postfields' => $postdata
        ]);
        if (!empty($headers))
            $curl_options['httpheader'] = $headers;

        return $this->getCurlResult($this->getCurlHandler($url, $curl_options));
    }

    public function delete(
        string $url,
        string $postdata,
        array $headers = [],
        array $curl_options = []
    ): object {
        $curl_options = array_merge($curl_options, [
            'customrequest' => 'DELETE',
            'postfields' => $postdata
        ]);
        if (!empty($headers))
            $curl_options['httpheader'] = $headers;

        return $this->getCurlResult($this->getCurlHandler($url, $curl_options));
    }

    protected function getCurlHandler(string $url, array $options)
    {
        $ch = curl_init($url);
        $options['returntransfer'] = true;
        foreach (($options['httpheader'] ?? []) as $header => $headerval) {
            $options['httpheader'][] = sprintf("%s: %s", $header, $headerval);
            unset($options['httpheader'][$header]);
        }
        foreach ($options as $option => $optionval)
            curl_setopt($ch, constant('CURLOPT_' . strtoupper($option)), $optionval);
        return $ch;
    }

    protected function getCurlResult($ch): object
    {
        if (is_resource($ch) === false)
            throw new InvalidArgumentException('getCurlResult function expects first parameter to be a CURL resource, "' . gettype($ch) . '" given');

        $result = curl_exec($ch);
        $info = $this->getCurlInfo($ch);
        $output = new stdClass();
        $output->headers = $info;
        $output->body = $result;
        curl_close($ch);
        return $output;
    }

    protected function getCurlInfo($ch): object
    {
        if (is_resource($ch) === false)
            throw new InvalidArgumentException('getCurlResult function expects first parameter to be a CURL resource, "' . gettype($ch) . '" given');

        $output = new stdClass();
        $info = curl_getinfo($ch);
        $allowed_keys = $this->getCurlInfoKeys();
        foreach ($info as $key => $val)
            if (in_array($key, $allowed_keys))
                $output->{$key} = $val;
        return $output;
    }
}
