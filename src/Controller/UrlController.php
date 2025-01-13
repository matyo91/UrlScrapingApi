<?php

namespace App\Controller;

use App\Dto\UrlDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use OpenAI;
use Flow\Flow\Flow;
use Flow\FlowFactory;
use Flow\Ip;

class UrlController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $openaiApiKey
    ) {}

    public function __invoke(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            // Decode JSON request body
            $data = json_decode($request->getContent(), true);

            // Deserialize into UrlDto
            $urlDto = $serializer->denormalize($data, UrlDto::class);

            // Integrate Flow
            $flow = (new FlowFactory())->create(function() use (&$data, $urlDto) {
                yield function ($url) use (&$data, $urlDto) {
                    // Fetch URL content
                    $response = $this->httpClient->request('GET', $urlDto->url);
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
                    $data = $result->choices[0]->message->content;
                };
            });

            $ip = new Ip($urlDto->url);
            $flow($ip);
            $flow->await();

            $parts = explode('TAGS:', $data);
            $summary = trim($parts[0]);
            $tags = isset($parts[1]) ? array_map('trim', explode(',', trim($parts[1]))) : [];

            return new JsonResponse([
                'url' => $urlDto->url,
                'summary' => $summary,
                'tags' => $tags,
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}