<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/scrape-url',
            controller: \App\Controller\UrlController::class
        )
    ]
)]
class UrlDto
{
    #[ApiProperty(description: 'The URL to scrape')]
    #[Assert\NotBlank]
    #[Assert\Url]
    public ?string $url = null;
}