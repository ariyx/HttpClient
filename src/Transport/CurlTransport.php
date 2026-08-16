<?php

declare(strict_types=1);

namespace Ariyx\HttpClient\Transport;

use Ariyx\HttpClient\Contracts\TransportInterface;
use Ariyx\HttpClient\Exception\NetworkException;
use Ariyx\HttpClient\Exception\RequestException;
use Ariyx\HttpClient\Exception\ResponseException;
use Ariyx\HttpClient\Exception\TimeoutException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class CurlTransport implements TransportInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly CurlOptions $options = new CurlOptions(),
    ) {}

    public function handle(RequestInterface $request): ResponseInterface
    {
        $this->validateRequest($request);
        $body = $this->readBody($request);
        $collector = new CurlHeaderCollector();
        $responseBody = fopen('php://temp', 'w+b');
        if ($responseBody === false) {
            throw new ResponseException('Unable to create the response body stream.');
        }
        $handle = curl_init((string) $request->getUri());
        if ($handle === false) {
            fclose($responseBody);
            throw new NetworkException('Unable to initialize cURL.', $request);
        }

        try {
            if (!$this->configure($handle, $request, $body, $collector, $responseBody)) {
                throw new NetworkException('Unable to configure cURL.', $request);
            }
            if (curl_exec($handle) === false) {
                if ($collector->error() !== null) {
                    throw new ResponseException($collector->error());
                }
                $this->throwCurlError($handle, $request);
            }
            if ($collector->error() !== null || $collector->statusCode() === 0) {
                throw new ResponseException($collector->error() ?? 'cURL returned no valid HTTP response.');
            }
            rewind($responseBody);
            $stream = $this->streamFactory->createStreamFromResource($responseBody);
            $responseBody = null;
            $response = $this->responseFactory
                ->createResponse($collector->statusCode(), $collector->reasonPhrase())
                ->withBody($stream);
            foreach ($collector->headers() as $name => $values) {
                $response = $response->withHeader($name, $values);
            }
            return $response;
        } finally {
            if (is_resource($responseBody)) {
                fclose($responseBody);
            }
            unset($handle);
        }
    }

    private function configure(\CurlHandle $handle, RequestInterface $request, string $body, CurlHeaderCollector $collector, mixed $responseBody): bool
    {
        $method = $request->getMethod();
        $userAgent = $this->options->userAgent;
        if ($method === '' || $userAgent === '' || !is_resource($responseBody)) {
            throw new \LogicException('Curl transport received invalid configured values.');
        }
        $configured = curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method)
            && curl_setopt($handle, CURLOPT_HTTPHEADER, $this->headers($request))
            && curl_setopt($handle, CURLOPT_HEADERFUNCTION, static fn(\CurlHandle $handle, string $line): int => $collector->collect($line))
            && curl_setopt($handle, CURLOPT_WRITEFUNCTION, static function (\CurlHandle $handle, string $data) use ($responseBody): int {
                $written = fwrite($responseBody, $data);
                return $written === false ? 0 : $written;
            })
            && curl_setopt($handle, CURLOPT_CONNECTTIMEOUT_MS, $this->options->connectTimeoutMs)
            && curl_setopt($handle, CURLOPT_TIMEOUT_MS, $this->options->timeoutMs)
            && curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, $this->options->verifyPeer)
            && curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, $this->options->verifyHost ? 2 : 0)
            && curl_setopt($handle, CURLOPT_FOLLOWLOCATION, $this->options->followRedirects)
            && curl_setopt($handle, CURLOPT_MAXREDIRS, $this->options->maxRedirects)
            && curl_setopt($handle, CURLOPT_USERAGENT, $userAgent);
        if (strtoupper($request->getMethod()) === 'HEAD') {
            return $configured && curl_setopt($handle, CURLOPT_NOBODY, true);
        }
        return $configured && ($body === '' || curl_setopt($handle, CURLOPT_POSTFIELDS, $body));
    }

    private function throwCurlError(\CurlHandle $handle, RequestInterface $request): never
    {
        $code = curl_errno($handle);
        $message = curl_error($handle) ?: 'cURL request failed.';
        if ($code === CURLE_OPERATION_TIMEDOUT) {
            throw new TimeoutException($message, $request, $code);
        }
        throw new NetworkException($message, $request, $code);
    }

    private function validateRequest(RequestInterface $request): void
    {
        $uri = $request->getUri();
        if (!in_array(strtolower($uri->getScheme()), ['http', 'https'], true) || $uri->getHost() === '') {
            throw new RequestException('Only absolute HTTP and HTTPS requests with a host are supported.', $request);
        }
        if ($request->getMethod() === '' || preg_match('/[\r\n]/', $request->getMethod()) === 1) {
            throw new RequestException('Invalid HTTP method.', $request);
        }
        foreach ($request->getHeaders() as $name => $values) {
            if (preg_match('/[\r\n]/', $name) === 1 || preg_match('/^[!#$%&\'*+.^_`|~0-9A-Za-z-]+$/', $name) !== 1) {
                throw new RequestException('Invalid HTTP header name.', $request);
            }
            foreach ($values as $value) {
                if (preg_match('/[\r\n]/', $value) === 1) {
                    throw new RequestException('Invalid HTTP header value.', $request);
                }
            }
        }
    }

    private function readBody(RequestInterface $request): string
    {
        $stream = $request->getBody();
        if (!$stream->isReadable()) {
            throw new RequestException('The request body is not readable.', $request);
        }
        $position = null;
        try {
            if ($stream->isSeekable()) {
                $position = $stream->tell();
                $stream->rewind();
            }
            return $stream->getContents();
        } catch (\Throwable $exception) {
            throw new RequestException('Unable to read the request body.', $request, 0, $exception);
        } finally {
            if ($position !== null) {
                $stream->seek($position);
            }
        }
    }

    /** @return list<string> */
    private function headers(RequestInterface $request): array
    {
        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $headers[] = $name . ': ' . $value;
            }
        }
        return $headers;
    }
}
