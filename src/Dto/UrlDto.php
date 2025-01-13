<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Controller\UrlController;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/scrape-url',
            description: 'Scrap a URL',
            controller: UrlController::class,
            read: false,
            output: false
        )
    ]
)]
class UrlDto
{
    #[Assert\NotBlank]
    #[Assert\Url]
    public ?string $url = null;
}