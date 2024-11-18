<?php

namespace PerSeo\Middleware\GZIP;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Stream as Stream;

class GZIP implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        
        if (!$request->hasHeader('Accept-Encoding') || stripos($request->getHeaderLine('Accept-Encoding'), 'gzip') === false || $response->hasHeader('Content-Encoding')) {         
            return $response;
        }

        $deflateContext = deflate_init(ZLIB_ENCODING_GZIP);
        $compressed = deflate_add($deflateContext, (string)$response->getBody(), \ZLIB_FINISH);

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $compressed);
        rewind($stream);

        return $response
        ->withHeader('Content-Encoding', 'gzip')
        ->withHeader('Content-Length', strlen($compressed))
        ->withBody(new Stream($stream));
    }
}