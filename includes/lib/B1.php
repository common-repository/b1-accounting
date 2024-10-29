<?php

/**
 * Library that allows to communicate with B1 API.
 * @author Serj Ivanov
 */
class B1
{

    /**
     * Version.
     */
    const VERSION = '2.2.55';
    /**
     * Platform.
     */
    const PLATFORM = 'woocommerce';
    /**
     * Server scheme
     */
    private $scheme = 'https';
    /**
     * Server host
     */
    private $host = 'www.b1.lt';
    /**
     * API base path.
     */
    private $basePath = '/api/';
    /**
     * @var string API key. Required for all requests to B1.
     */
    private $apiKey;
    /**
     * @var string Private key. Required for all requests to B1.
     */
    private $privateKey;
    /**
     * @var int Request timeout.
     */
    private $timeout = 30;
    /**
     * @var array Attributes that are allowed to be set.
     */
    private $configKeys = ['scheme', 'host', 'apiKey', 'privateKey', 'timeout'];

    public function __construct($config)
    {
        foreach ($this->configKeys as $key) {
            if (isset($config[$key])) {
                $this->$key = $config[$key];
            }
        }
        if (empty($this->apiKey)) {
            throw new B1Exception('API key is not provided', $config);
        }
        if (empty($this->privateKey)) {
            throw new B1Exception('Private key is not provided', $config);
        }
    }

    /**
     * @param string $path Path in B1 system.
     * @param array $data Data to send to B1.
     * @param array $headers Headers that need to be incorporated in the request.
     * @return B1Response
     * @throws B1Exception
     */
    public function request($path, $data = [], $headers = [])
    {
        $content = json_encode($data);
        return $this->executeRequest($path, $content, $headers);
    }

    /**
     * @param string $path Path in B1 system.
     * @param string $fileContent File content.
     * @param array $headers Headers that need to be incorporated in the request.
     * @return B1Response
     * @throws B1Exception
     */
    public function upload($path, $fileContent, $headers = [])
    {
        if (!isset($headers['Id'])) {
            throw new B1Exception("'Id' is not specified in the headers array");
        }
        if (!isset($headers['File-Name'])) {
            throw new B1Exception("'File-Name' is not specified in the headers array");
        }
        if (!isset($headers['Content-Type'])) {
            throw new B1Exception("'Content-Type' is not specified in the headers array");
        }
        return $this->executeRequest($path, $fileContent, $headers);
    }

    /**
     * Populates the headers array with required lines.
     * @param array $headers Headers that need to be incorporated in the request.
     * @param string $content
     * @throws B1Exception
     */
    private function populateRequestHeaders(&$headers, $content)
    {
        $headers['Api-Key'] = $this->apiKey;
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json; charset=utf-8';
        }
        $headers['Content-Length'] = strlen($content);
        $headers['Time'] = time();
        $headers['Version'] = B1::VERSION;
        $headers['Platform'] = B1::PLATFORM;
        ksort($headers);
    }

    /**
     * Generates the request signature.
     * @param array $headers Headers.
     * @param string $content Content.
     */
    private function signRequest(&$headers, $content)
    {
        $data = '';
        foreach ($headers as $name => $value) {
            $data .= strtolower($name) . $value;
        }
        $data .= $content;
        $headers['Signature'] = hash_hmac('sha512', $data, $this->privateKey);
    }

    /**
     * Builds the request headers.
     * @param array $headers Headers.
     * @return array
     */
    private function buildRequestHeaders(&$headers)
    {
        $headersToSend = [];
        foreach ($headers as $name => $value) {
            if (!in_array($name, ['Content-Type', 'Content-Length'])) {
                $headersToSend[] = 'B1-' . $name . ': ' . $value;
            } else {
                $headersToSend[] = $name . ': ' . $value;
            }
        }
        return $headersToSend;
    }

    /**
     * Makes the call to the API.
     * @param string $path Path in B1 system.
     * @param string $requestContent Request content.
     * @param array $headers Request headers.
     * @return B1Response
     * @throws B1Exception
     */
    private function executeRequest($path, $requestContent, $headers)
    {
        $url = $this->scheme . '://' . $this->host . $this->basePath . $path;
        $this->populateRequestHeaders($headers, $requestContent);
        $this->signRequest($headers, $requestContent);
        $headers = $this->buildRequestHeaders($headers);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestContent);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $response = curl_exec($ch);
        $responseHeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $responseHeader = substr($response, 0, $responseHeaderSize);
        $responseContent = substr($response, $responseHeaderSize);
        $requestHeaders = curl_getinfo($ch, CURLINFO_HEADER_OUT);

        $debug = [
            'request' => [
                'headers' => $requestHeaders,
                'content' => $requestContent,
            ],
            'response' => [
                'headers' => $responseHeader,
                'content' => $responseContent,
            ],
        ];

        $debugToLog =[
            'request' => [
                'headers' => $requestHeaders,
                'content' => json_decode($requestContent),
            ],
            'response' => [
                'headers' => $responseHeader,
                'content' => json_decode($responseContent),
            ],
        ];

        if ($response === false) {
            B1_Accounting_Logger::save(false, $debugToLog);
            $message = curl_error($ch);
            curl_close($ch);
            throw new B1Exception($message, $debug);
        } else {
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $response = new B1Response($responseHeader, $responseContent, $responseCode);
            B1_Accounting_Logger::save($responseCode == 200, $debugToLog);
            switch ($responseCode) {
                case 200:
                    return $response;
                case 400:
                    throw new B1ValidationException($response,"B1 API data validation failure.",  $debug);
                case 404:
                    throw new B1ResourceNotFoundException($response,"B1 API resource not found.",  $debug);
                case 409:
                    throw new B1DuplicateException($response,"Object already exists in the B1 system.",  $debug);
                case 480:
                    throw new B1PartialCompletionException($response,"Partial completion in the B1 system.",  $debug);
                case 500:
                    throw new B1InternalErrorException($response,"B1 API internal error.",  $debug);
                case 503:
                    throw new B1ServiceUnavailableException($response,"B1 API is currently unavailable.",  $debug);
                default:
                    throw new B1Exception("B1 API fatal error.", $debug);
            }
        }
    }

}

class B1Response
{

    private $headers;
    private $content;
    private $code;

    public function __construct($headers, $content, $code)
    {
        foreach (explode("\r\n", $headers) as $i => $line) {
            if (strpos($line, ': ') !== false) {
                list($key, $value) = explode(': ', $line);
                $this->headers[strtolower($key)] = $value;
            }
        }
        if ($this->isJson()) {
            $this->content = json_decode($content, true);
        } else {
            $this->content = $content;
        }
        $this->code = $code;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getFileName()
    {
        return isset($this->headers['file-name']) ? $this->headers['file-name'] : null;
    }

    public function getContentType()
    {
        return isset($this->headers['content-type']) ? $this->headers['content-type'] : null;
    }

    private function isJson()
    {
        return isset($this->headers['content-type']) && strpos($this->headers['content-type'], 'application/json') !== false;
    }

}

/**
 * Base exception class for all exceptions in this library
 */
class B1Exception extends Exception
{

    /**
     * @var array
     */
    protected $extraData;

    /**
     * @param string $message
     * @param array $extraData
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $extraData = [], $code = 0, Throwable $previous = null)
    {
        $this->extraData = $extraData;
        parent::__construct($message, $code, $previous);
    }

    public function getExtraData()
    {
        return $this->extraData;
    }

}

class B1ClientException extends B1Exception
{

    /**
     * @var B1Response
     */
    protected $response;

    /**
     * @param B1Response $response
     * @param string $message
     * @param array $extraData
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($response, $message = "", $extraData = [], $code = 0, Throwable $previous = null)
    {
        $this->response = $response;
        parent::__construct($message, $extraData, $code, $previous);
    }

    public function getResponse()
    {
        return $this->response;
    }

}

class B1ValidationException extends B1ClientException
{

}

class B1ResourceNotFoundException extends B1ClientException
{

}

class B1DuplicateException extends B1ClientException
{

}

class B1InternalErrorException extends B1ClientException
{

}

class B1ServiceUnavailableException extends B1ClientException
{

}

class B1PartialCompletionException extends B1ClientException
{

}
