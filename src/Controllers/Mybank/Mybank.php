<?php

declare(strict_types=1);

namespace Evasystem\Controllers\Mybank;

class Mybank
{
    private MybankService $service;

    public function __construct(MybankService $service)
    {
        $this->service = $service;
    }

    public function index(string $userRandomnId): array
    {
        return $this->service->getDashboardData($userRandomnId);
    }

    public function saveBilling(string $accountRandomnId, array $data): array
    {
        $ok = $this->service->saveBilling($accountRandomnId, $data);

        return [
            'success' => $ok,
            'message' => $ok ? 'Billing info salvată.' : 'Eroare la salvare billing.'
        ];
    }

    public function addCard(string $accountRandomnId, array $data): array
    {
        $ok = $this->service->addCard($accountRandomnId, $data);

        return [
            'success' => $ok,
            'message' => $ok ? 'Card adăugat cu succes.' : 'Eroare la adăugare card.'
        ];
    }
    public function purchaseSubscription(string $userRandomnId, array $data): array
    {
        return $this->service->purchaseSubscription($userRandomnId, $data);
    }
    public function removeCard(string $accountRandomnId): array
    {
        $ok = $this->service->removePrimaryCard($accountRandomnId);

        return [
            'success' => $ok,
            'message' => $ok ? 'Card șters.' : 'Eroare la ștergere card.'
        ];
    }
}