<?php

declare(strict_types=1);

namespace Vjik\TelegramBot\Api\Client;

use Http\Message\MultipartStream\MultipartStreamBuilder;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface as HttpRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Vjik\TelegramBot\Api\Request\HttpMethod;
use Vjik\TelegramBot\Api\Request\TelegramRequestInterface;
use Vjik\TelegramBot\Api\Request\TelegramRequestWithFilesInterface;

final readonly class PsrTelegramClient implements TelegramClientInterface
{
    private ApiUrlGenerator $apiUrlGenerator;

    public function __construct(
        private string $token,
        private ClientInterface $httpClient,
        private RequestFactoryInterface $httpRequestFactory,
        private StreamFactoryInterface $streamFactory,
        private string $baseUrl = 'https://api.telegram.org',
    ) {
        $this->apiUrlGenerator = new ApiUrlGenerator($this->token, $this->baseUrl);
    }

    public function send(TelegramRequestInterface $request): TelegramResponse
    {
        $httpResponse = $this->httpClient->sendRequest(
            match ($request->getHttpMethod()) {
                HttpMethod::GET => $this->createGetRequest($request),
                HttpMethod::POST => $this->createPostRequest($request),
            }
        );

        return new TelegramResponse(
            $httpResponse->getStatusCode(),
            $httpResponse->getBody()->getContents(),
        );
    }

    private function createPostRequest(TelegramRequestInterface $request): HttpRequestInterface
    {
        $httpRequest = $this->httpRequestFactory->createRequest(
            'POST',
            $this->apiUrlGenerator->generate($request->getApiMethod()),
        );

        $data = $request->getData();
        $files = $request instanceof TelegramRequestWithFilesInterface ? $request->getFiles() : [];
        if (empty($data) && empty($files)) {
            return $httpRequest;
        }

        if (empty($files)) {
            $content = json_encode($data, JSON_THROW_ON_ERROR);
            $body = $this->streamFactory->createStream($content);
            $contentType = 'application/json; charset=utf-8';
        } else {
            $streamBuilder = new MultipartStreamBuilder($this->streamFactory);
            foreach ($data as $key => $value) {
                $streamBuilder->addResource($key, json_encode($value, JSON_THROW_ON_ERROR));
            }
            foreach ($files as $key => $file) {
                $streamBuilder->addResource(
                    $key,
                    $file->resource,
                    array_filter(
                        ['filename' => $file->filename],
                        static fn(mixed $value): bool => $value !== null,
                    )
                );
            }
            $body = $streamBuilder->build();
            $contentType = 'multipart/form-data; boundary=' . $streamBuilder->getBoundary() . '; charset=utf-8';
        }

        return $httpRequest
            ->withHeader('Content-Length', (string) $body->getSize())
            ->withHeader('Content-Type', $contentType)
            ->withBody($body);
    }

    private function createGetRequest(TelegramRequestInterface $request): HttpRequestInterface
    {
        return $this->httpRequestFactory->createRequest(
            'GET',
            $this->apiUrlGenerator->generate($request->getApiMethod(), $request->getData()),
        );
    }
}
