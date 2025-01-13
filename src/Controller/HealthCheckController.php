<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Liip\MonitorBundle\Helper\ArrayReporter;
use Liip\MonitorBundle\Runner;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HealthCheckController extends AbstractController
{
    private $runner;

    public function __construct(Runner $runner)
    {
        $this->runner = $runner;
    }

    #[Route('/ping', name: 'health_check')]
    public function ping(): Response
    {
        return new Response(json_encode(['status' => 'OK']), Response::HTTP_OK, ['Content-Type' => 'application/json']);

        /*$reporter = new ArrayReporter();
        $this->runner->addReporter($reporter);
        $this->runner->run();

        $results = $reporter->getResults();
        $status = 'OK';

        foreach ($results as $result) {
            if (!$result->isSuccessful()) {
                $status = 'FAIL';
                break;
            }
        }

        return new Response($status);*/
    }
}