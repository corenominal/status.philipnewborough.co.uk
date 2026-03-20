<?php

namespace App\Libraries;

class Markdown
{
    /**
     * Class Markdown
     *
     * This class provides functionality to interact with the Markdown API.
     * It initializes a cURL client and retrieves the API master key from the configuration.
     *
     * Properties:
     * @property string $apikey   The API master key used for authentication with the Markdown API.
     * @property object $client   The cURL client used to make HTTP requests.
     * @property string $baseUrl  The base URL of the Markdown API.
     * @property mixed  $markdown Placeholder for markdown-related data or operations.
     *
     * Methods:
     * __construct() Initializes the cURL client and retrieves the API master key from the configuration.
     */
    private $apikey;
    private $client;
    private $markdown;

    public function __construct()
    {
        // Initialize the curl client
        $this->client = service('curlrequest');
        // Get API master key from config
        $config = config('ApiKeys');
        $this->apikey = $config->masterKey;
    }

    /**
     * Set the Markdown.
     *
     * @param string $title The markdown to set.
     * @return void
     */
    public function setMarkdown($markdown)
    {
        // Validate the markdown is a string
        if (!is_string($markdown)) {
            throw new \InvalidArgumentException("Markdown must be a string");
        }
        // Validate the markdown is not empty
        if (empty($markdown)) {
            throw new \InvalidArgumentException("Markdown cannot be empty");
        }
        $this->markdown = $markdown;
    }

    /**
     * Get the Markdown.
     *
     * @return string The Markdown.
     */
    public function getMarkdown()
    {
        return $this->markdown ?? null;
    }

    /**
     * Converts the provided Markdown content to another format using an external service.
     *
     * This method sends a POST request to the specified converter endpoint with the
     * Markdown content. It requires the `markdown` property to be set before calling
     * this method. If the `markdown` property is empty, an exception will be thrown.
     *
     * @throws \InvalidArgumentException If the `markdown` property is not set.
     * @return array The decoded response from the external service.
     */
    public function convert()
    {
        // Prepare the data to be sent
        $data = ['markdown' => $this->markdown];

        // Throw an exception if any required fields are missing
        if (empty($this->markdown)) {
            throw new \InvalidArgumentException("Markdown field is required");
        }

        // Send the notification using cURL
        $response = $this->client->post(config('Urls')->markdown . 'api/converter', [
            'headers' => [
            'ApiKey' => $this->apikey,
            'Content-Type' => 'application/json',
            ],
            'body' => json_encode($data),
        ]);

        // Decode the response
        $response = json_decode($response->getBody(), true);
        // Return response
        return $response;
    }
}