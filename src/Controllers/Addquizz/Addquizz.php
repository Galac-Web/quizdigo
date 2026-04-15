<?php
declare(strict_types=1);

namespace Evasystem\Controllers\Addquizz;

class Addquizz
{
    protected AddquizzService $service;

    public function __construct(?AddquizzService $service = null)
    {
        $this->service = $service ?? new AddquizzService();
    }

    public function save(array $payload, int $idUser): array
    {
        return $this->service->saveQuiz($payload, $idUser);
    }
}