<?php

namespace Inilim\QueryBuild;

// ------------------------------------------------------------------
// Exception
// ------------------------------------------------------------------
use Inilim\QueryBuild\Exception\EmptyKeyException;

class QueryBuild
{
    protected string $query;
    protected array $query_as_array;
    protected bool $null_as_empty_string = false;

    public function __construct(?string $url_or_query = null, ?bool $null_as_empty_string = null)
    {
        if (is_bool($null_as_empty_string)) $this->null_as_empty_string = $null_as_empty_string;

        if (is_null($url_or_query)) $result = '';
        else {
            $result = parse_url($url_or_query);
            $result = $result['query'] ?? $result['path'] ?? '';
        }

        if ($result === '') $this->query = '';
        // исключаем query типа "/path/path/..."
        elseif (str_contains($result, '/')) $this->query = '';
        else $this->query = $result;

        if ($this->query === '') $output = [];
        else parse_str($this->query, $output);

        $this->query_as_array = $output;
    }

    /**
     * @param mixed $value
     */
    public function addParam(string|int|float $key, $value): self
    {
        $key = strval($key);
        if ($key === '') throw new EmptyKeyException;
        $this->query_as_array[$key] = $value;
        return $this;
    }

    /**
     * @param array<int|string,mixed> $params
     */
    public function addParams(array $params): self
    {
        foreach ($params as $key => $value) {
            $this->addParam($key, $value);
        }
        return $this;
    }

    /**
     * @param (string|int|float)[]|string|int|float $keys
     */
    public function removeParams(array|string|int|float $keys): self
    {
        if (!is_array($keys)) $keys = [strval($keys)];
        foreach ($keys as $key) {
            $key = strval($key);
            // if ($key === '') throw new EmptyKeyException;
            unset($this->query_as_array[$key]);
        }
        return $this;
    }

    /**
     * array_key_exists
     */
    public function hasParam(string|int|float $key): bool
    {
        $key = strval($key);
        // if ($key === '') throw new EmptyKeyException;
        return array_key_exists($key, $this->query_as_array);
    }

    public function getParam(string|int|float $key): mixed
    {
        $key = strval($key);
        // if ($key === '') throw new EmptyKeyException;
        return $this->query_as_array[$key] ?? null;
    }

    public function getQueryAsArray(?bool $null_as_empty_string = null): array
    {
        $null_as_empty_string ??= $this->null_as_empty_string;

        if ($null_as_empty_string) return $this->nullToString($this->query_as_array);

        return $this->query_as_array;
    }

    public function getQuery(?bool $null_as_empty_string = null): string
    {
        $null_as_empty_string ??= $this->null_as_empty_string;

        if ($null_as_empty_string) return http_build_query(
            $this->nullToString($this->query_as_array)
        );

        return http_build_query($this->query_as_array);
    }

    /**
     * тип null в виде строки ""
     */
    public function nullAsEmptyString(bool $yes_no): self
    {
        $this->null_as_empty_string = $yes_no;
        return $this;
    }

    protected function nullToString(array $array): array
    {
        array_walk_recursive($array, function (&$value) {
            if (is_null($value)) $value = '';
        });

        return $array;
    }
}