<?php

namespace App\Controller;

use App\Dto\UrlDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class UrlController extends AbstractController
{
    public function __invoke(UrlDto $data): JsonResponse
    {
        

        return new JsonResponse([
            'url' => $data->url,
            'message' => 'URL received successfully'
        ]);
    }
}