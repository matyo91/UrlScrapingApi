<?php

namespace App\Controller;

use App\Dto\UrlDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use OpenAI;

class UrlController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $openaiApiKey
    ) {}

    public function __invoke(UrlDto $data): JsonResponse
    {
        try {
            // Fetch URL content
            $response = $this->httpClient->request('GET', $data->url);
            $content = $response->getContent();

            // Initialize OpenAI client
            $client = OpenAI::client($this->openaiApiKey);

            // Get summary and tags from ChatGPT
            $result = $client->chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'First provide a concise summary of the webpage content. Then on a new line after "TAGS:", list up to 10 relevant single-word or short-phrase tags, separated by commas.'],
                    ['role' => 'user', 'content' => $content],
                ],
                'max_tokens' => 400
            ]);

            // Split the response into summary and tags
            $response = $result->choices[0]->message->content;
            $parts = explode('TAGS:', $response);
            $summary = trim($parts[0]);
            $tags = isset($parts[1]) ? array_map('trim', explode(',', trim($parts[1]))) : [];

            return new JsonResponse([
                'url' => $data->url,
                'summary' => $summary,
                'tags' => $tags,
                'message' => 'URL processed successfully'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}