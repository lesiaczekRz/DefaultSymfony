<?php

declare(strict_types=1);

namespace App\OpenApi;

use ArrayObject;
use ApiPlatform\Core\OpenApi\Model;
use ApiPlatform\Core\OpenApi\OpenApi;
use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;

final class JwtDecorator implements OpenApiFactoryInterface
{
    private $decorated;

    public function __construct(OpenApiFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $schemas = $openApi->getComponents()->getSchemas();

        $schemas['Token'] = new ArrayObject([
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'readOnly' => true,
                ],
            ],
        ]);
        $schemas['Credentials'] = new ArrayObject([
            'type' => 'object',
            'properties' => [
                'username' => [
                    'type' => 'string',
                    'example' => 'nazwiskoimie',
                ],
                'password' => [
                    'type' => 'string',
                    'example' => 'hasło',
                ],
            ],
        ]);

        $responses = [
            '200' => [
                'description' => 'Get JWT token',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Token',
                        ],
                    ],
                ]
            ]
        ];

        $content = new ArrayObject([
            'application/json' => [
                'schema' => [
                    '$ref' => '#/components/schemas/Credentials',
                ],
            ],
        ]);

        $requestBody = new Model\RequestBody('Generate new JWT Token', $content);
        $post = new Model\Operation('postCredentialsItem', [], $responses, 'Get JWT token to login.', '', null, [], $requestBody);
        $pathItem = new Model\PathItem('JWT Token', null, null, null, null, $post);

        $openApi->getPaths()->addPath('/authentication_token', $pathItem);

        return $openApi;
    }
}
