<?php

namespace PerSeo\Middleware\GZIP\Test;

use PerSeo\Middleware\GZIP\GZIP;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Stream as Stream;

class GzipMiddlewareTest extends TestCase
{
    public function testCompressesResponseWhenGzipAccepted(): void
    {

        $middleware = new GZIP();
		$request = (new ServerRequestFactory())->createServerRequest('GET', '/')
			->withHeader('Accept-Encoding', 'gzip');
		$responseFactory = new ResponseFactory();
		$handler = $this->createMock(RequestHandlerInterface::class);
		
		$originalBody = 'Test response';
		$stream = fopen('php://memory', 'r+');
		if ($stream === false) {
			throw new \RuntimeException('Unable to open memory stream.');
		}
		fwrite($stream, $originalBody);
		rewind($stream);
		
		$response = $responseFactory->createResponse()->withBody(new Stream($stream));
		$handler->method('handle')->willReturn($response);
		
		// Act
		$compressedResponse = $middleware->process($request, $handler);
		
		// Assert
		$compressedResponse->getBody()->rewind();
		$compressedContent = $compressedResponse->getBody()->getContents();
		
		$this->assertEquals('gzip', $compressedResponse->getHeaderLine('Content-Encoding'));
		$this->assertEquals(strlen($compressedContent), (int)$compressedResponse->getHeaderLine('Content-Length'));
		$this->assertNotEquals($originalBody, $compressedContent);
		$this->assertEquals($originalBody, gzinflate(substr($compressedContent, 10, -8)));
    }

    public function testDoesNotCompressResponseWhenGzipNotAccepted(): void
    {
        // Arrange
        $middleware = new GZIP();
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/');
		$responseFactory = new ResponseFactory();
		$handler = $this->createMock(RequestHandlerInterface::class);
		
		$originalBody = 'Test response';
		$stream = fopen('php://memory', 'r+');
		if ($stream === false) {
			throw new \RuntimeException('Unable to open memory stream.');
		}
		fwrite($stream, $originalBody);
		rewind($stream);
		
		$response = $responseFactory->createResponse()->withBody(new Stream($stream));
		$handler->method('handle')->willReturn($response);
		
		// Act
		$plainResponse = $middleware->process($request, $handler);
		
		// Assert
		$plainResponse->getBody()->rewind(); // Torna all'inizio dello stream
		$content = $plainResponse->getBody()->getContents();
		
		$this->assertEmpty($plainResponse->getHeaderLine('Content-Encoding'), 'Content-Encoding header should not be set');
		$this->assertEquals($originalBody, $content, 'The response body should remain unchanged');
    }
}
