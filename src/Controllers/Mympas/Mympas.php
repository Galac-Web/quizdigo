<?php

declare(strict_types=1);

namespace Evasystem\Controllers\Mympas;

class Mympas
{
    private MympasService $service;

    public function __construct(MympasService $service)
    {
        $this->service = $service;
    }

    public function index(string $userRandomnId): array
    {
        return $this->service->getDashboardData($userRandomnId);
    }

    public function saveCard(string $userRandomnId, array $data): array
    {
        $ok = $this->service->saveCard($userRandomnId, $data);
        return ['success' => $ok, 'message' => $ok ? 'Card salvat.' : 'Eroare la salvare card.'];
    }

    public function savePoint(string $userRandomnId, array $data): array
    {
        $ok = $this->service->savePoint($userRandomnId, $data);
        return ['success' => $ok, 'message' => $ok ? 'Punct hartă salvat.' : 'Eroare la salvare punct.'];
    }

    public function saveWeekActivity(string $userRandomnId, array $data): array
    {
        $ok = $this->service->saveWeekActivity($userRandomnId, $data);
        return ['success' => $ok, 'message' => $ok ? 'Activitate salvată.' : 'Eroare la salvare activitate.'];
    }

    public function savePlanning(string $userRandomnId, array $data): array
    {
        $ok = $this->service->savePlanning($userRandomnId, $data);
        return ['success' => $ok, 'message' => $ok ? 'Planning salvat.' : 'Eroare la salvare planning.'];
    }

    public function deleteItem(string $type, string $randomnId): array
    {
        $ok = $this->service->deleteByType($type, $randomnId);
        return ['success' => $ok, 'message' => $ok ? 'Element șters.' : 'Eroare la ștergere.'];
    }
}