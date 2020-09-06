<?php

declare(strict_types=1);

namespace SimpleRouter;


class Router
{
    private $method;
    private $requestURL;
    private $paramsRequestURL = [];
    private $request;
    private $foundPagesFlag = false;


    function __construct(array $server, array $request)
    {
        $this->method = $server['REQUEST_METHOD'];
        $this->request = $request;
        $this->cleanURL($server['REQUEST_URI']);
    }


    private function cleanURL(string $requestURL): void
    {
        $routeLink = explode('?', $requestURL);
        $this->requestURL = trim($routeLink[0]);
    }


    private function cleanParamsReuqestURL(): void
    {
        $this->paramsRequestURL = [];
    }


    private function parserURL(string $routeLink): bool
    {
        if ($this->foundPagesFlag) {
            return false;
        }

        if ($routeLink == $this->requestURL) {
            return true;
        }

        $explodeRequestURL = explode("/", $this->requestURL);
        $explodeRouteLink = explode("/", $routeLink);

        if (count($explodeRouteLink) != count($explodeRequestURL)) {
            return false;
        }

        $this->cleanParamsReuqestURL();

        foreach ($explodeRequestURL as $key => $sectionRequestURL) {
            if (!$this->parseSectionUrl($sectionRequestURL, $explodeRouteLink[$key])) {
                return false;
            }
        }

        return true;
    }


    private function parseSectionUrl(string $sectionRequestURL, string $sectionRouteLink): bool
    {
        if ($sectionRequestURL == $sectionRouteLink) {
            return true;
        }

        if (preg_match("/^\<int:(\w+)\>$/", $sectionRouteLink, $keys)) { // поиск <int:name>
            if (preg_match("/^(\d+)$/", $sectionRequestURL, $numbers)) { // поиск строки только с числами
                $this->paramsRequestURL[$keys[1]] = (int) $numbers[0];
                return true;
            }
        }


        if (preg_match("/^\<string:(\w+)\>$/", $sectionRouteLink, $keys)) { // поиск <string:name>
            if (preg_match("/^(\w+)$/", $sectionRequestURL, $matches)) { // поиск строки с A-z, 0-9 и _
                $this->paramsRequestURL[$keys[1]] = (string) $matches[0];
                return true;
            }
        }

        return false;
    }


    private function startRouter(string $method, string $routeLink, callable $callback): bool
    {
        if ($this->parserURL($routeLink) && ($method == $this->method || $method == "ANY")) {
            $this->foundPagesFlag = true;
            $callback($this->paramsRequestURL, $this->request);
            return true;
        } else {
            return false;
        }
    }


    public function get(string $routeLink, callable $callback): bool
    {
        return $this->startRouter("GET", $routeLink, $callback);
    }


    public function post(string $routeLink, callable $callback): bool
    {
        return $this->startRouter("POST", $routeLink, $callback);
    }


    public function put(string $routeLink, callable $callback): bool
    {
        return $this->startRouter("PUT", $routeLink, $callback);
    }


    public function patch(string $routeLink, callable $callback): bool
    {
        return $this->startRouter("PATCH", $routeLink, $callback);
    }


    public function delete(string $routeLink, callable $callback): bool
    {
        return $this->startRouter("DELETE", $routeLink, $callback);
    }


    public function options(string $routeLink, callable $callback): bool
    {
        return $this->startRouter("OPTIONS", $routeLink, $callback);
    }


    public function match(array $methods, string $routeLink, callable $callback): bool
    {
        foreach ($methods as $method) {
            if ($this->startRouter($method, $routeLink, $callback)) {
                return true;
            }
        }
        return false;
    }


    public function any(string $routeLink, callable $callback)
    {
        return $this->startRouter("ANY", $routeLink, $callback);
    }


    public function getFoundPagesFlag(): bool
    {
        return $this->foundPagesFlag;
    }
}
