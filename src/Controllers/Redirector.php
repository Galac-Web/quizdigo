<?php

namespace Evasystem\Controllers;
class Redirector
{
    private string $url;

    /**
     * Constructor initializes the base URL.
     */
    public function __construct()
    {
        $this->url = $this->ProtocolUrl();
    }

    /**
     * Get the current base URL.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set a new base URL.
     *
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * Determine and set the full URL with protocol.
     *
     * @throws \Exception
     * @return string
     */
    private function ProtocolUrl(): string
    {
        if (empty($_SERVER['HTTP_HOST'])) {
            throw new \Exception("Unable to determine the full URL due to missing server information.");
        }
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        return rtrim($protocol . $_SERVER['HTTP_HOST'], '/');
    }

    /**
     * Get the last segment of the current URL path.
     *
     * @return string
     */
    public function thispagesurl(): string
    {
        return basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    }

    /**
     * Redirect to the specified URL.
     *
     * @param string $url
     */
    public function redirect(string $url): void
    {
        $baseLink = rtrim($this->getUrl(), '/');
        $finalLink = $baseLink . '/' . ltrim($url, '/');

        header("Location: $finalLink", true, 302);
        exit;
    }

    /**
     * Get the full URL with an optional subfolder.
     *
     * @param string $folder
     * @return string
     */
    public function geturlthis(string $folder = ''): string
    {
        return $this->getUrl() . '/' . ltrim($folder, '/');
    }
}
