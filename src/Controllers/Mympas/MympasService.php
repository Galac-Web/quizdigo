<?php

declare(strict_types=1);

namespace Evasystem\Controllers\Mympas;

use Evasystem\Core\Mympas\MympasModel;

class MympasService
{
    public function seedDemoIfEmpty(string $userRandomnId): void
    {
        $cards = MympasModel::getDashboardCards($userRandomnId);
        if (empty($cards)) {
            $demoCards = [
                ['French', '35 participanti', 75, 'blue', '1_img.png', 1],
                ['English', '28 participanti', 68, 'orange', '2_img.png', 2],
                ['German', '19 participanti', 54, 'green', '3_img.png', 3],
                ['Italian', '12 participanti', 41, 'yellow', '4_img.png', 4],
            ];

            foreach ($demoCards as $item) {
                MympasModel::createCard([
                    'randomn_id' => MympasModel::uid('card_'),
                    'user_randomn_id' => $userRandomnId,
                    'title' => $item[0],
                    'subtitle' => $item[1],
                    'percent_value' => $item[2],
                    'card_color' => $item[3],
                    'icon_file' => $item[4],
                    'sort_order' => $item[5],
                    'status' => 'active',
                ]);
            }
        }

        $points = MympasModel::getMapPoints($userRandomnId);
        if (empty($points)) {
            $demoPoints = [
                ['Chișinău', 47.0245, 28.8323],
                ['București', 44.4268, 26.1025],
                ['Paris', 48.8566, 2.3522],
                ['London', 51.5074, -0.1278],
                ['New York', 40.7128, -74.0060],
            ];

            foreach ($demoPoints as $point) {
                MympasModel::createPoint([
                    'randomn_id' => MympasModel::uid('point_'),
                    'user_randomn_id' => $userRandomnId,
                    'point_name' => $point[0],
                    'lat' => $point[1],
                    'lng' => $point[2],
                    'popup_text' => $point[0],
                    'status' => 'active',
                ]);
            }
        }

        $week = MympasModel::getWeekActivity($userRandomnId);
        if (empty($week)) {
            $days = [
                ['Mon', 210, 1],
                ['Tues', 188, 2],
                ['Wed', 132, 3],
                ['Thurs', 276, 4],
                ['Fri', 167, 5],
                ['Sat', 232, 6],
                ['Sun', 199, 7],
            ];

            foreach ($days as $d) {
                MympasModel::createWeekActivity([
                    'randomn_id' => MympasModel::uid('day_'),
                    'user_randomn_id' => $userRandomnId,
                    'day_key' => $d[0],
                    'value_number' => $d[1],
                    'sort_order' => $d[2],
                ]);
            }
        }

        $planning = MympasModel::getPlanning($userRandomnId);
        if (empty($planning)) {
            for ($i = 1; $i <= 4; $i++) {
                MympasModel::createPlanning([
                    'randomn_id' => MympasModel::uid('plan_'),
                    'user_randomn_id' => $userRandomnId,
                    'title' => 'Puzzel',
                    'start_time' => '8:00 AM',
                    'end_time' => '10:00 AM',
                    'icon_file' => 'icon_element.png',
                    'event_date' => date('Y-m-d'),
                    'sort_order' => $i,
                    'status' => 'active',
                ]);
            }
        }
    }

    public function getDashboardData(string $userRandomnId): array
    {
        $this->seedDemoIfEmpty($userRandomnId);

        return [
            'cards' => MympasModel::getDashboardCards($userRandomnId),
            'points' => MympasModel::getMapPoints($userRandomnId),
            'week' => MympasModel::getWeekActivity($userRandomnId),
            'planning' => MympasModel::getPlanning($userRandomnId),
        ];
    }

    public function saveCard(string $userRandomnId, array $data): bool
    {
        $payload = [
            'title' => trim((string)($data['title'] ?? '')),
            'subtitle' => trim((string)($data['subtitle'] ?? '')),
            'percent_value' => (int)($data['percent_value'] ?? 0),
            'card_color' => trim((string)($data['card_color'] ?? 'blue')),
            'icon_file' => trim((string)($data['icon_file'] ?? '1_img.png')),
            'sort_order' => (int)($data['sort_order'] ?? 0),
            'status' => trim((string)($data['status'] ?? 'active')),
        ];

        $randomnId = trim((string)($data['randomn_id'] ?? ''));
        if ($randomnId !== '') {
            return MympasModel::updateCard($randomnId, $payload);
        }

        $payload['randomn_id'] = MympasModel::uid('card_');
        $payload['user_randomn_id'] = $userRandomnId;
        return MympasModel::createCard($payload);
    }

    public function savePoint(string $userRandomnId, array $data): bool
    {
        $payload = [
            'point_name' => trim((string)($data['point_name'] ?? '')),
            'lat' => (float)($data['lat'] ?? 0),
            'lng' => (float)($data['lng'] ?? 0),
            'popup_text' => trim((string)($data['popup_text'] ?? '')),
            'status' => trim((string)($data['status'] ?? 'active')),
        ];

        $randomnId = trim((string)($data['randomn_id'] ?? ''));
        if ($randomnId !== '') {
            return MympasModel::updatePoint($randomnId, $payload);
        }

        $payload['randomn_id'] = MympasModel::uid('point_');
        $payload['user_randomn_id'] = $userRandomnId;
        return MympasModel::createPoint($payload);
    }

    public function saveWeekActivity(string $userRandomnId, array $data): bool
    {
        $payload = [
            'day_key' => trim((string)($data['day_key'] ?? '')),
            'value_number' => (int)($data['value_number'] ?? 0),
            'sort_order' => (int)($data['sort_order'] ?? 0),
        ];

        $randomnId = trim((string)($data['randomn_id'] ?? ''));
        if ($randomnId !== '') {
            return MympasModel::updateWeekActivity($randomnId, $payload);
        }

        $payload['randomn_id'] = MympasModel::uid('day_');
        $payload['user_randomn_id'] = $userRandomnId;
        return MympasModel::createWeekActivity($payload);
    }

    public function savePlanning(string $userRandomnId, array $data): bool
    {
        $payload = [
            'title' => trim((string)($data['title'] ?? '')),
            'start_time' => trim((string)($data['start_time'] ?? '')),
            'end_time' => trim((string)($data['end_time'] ?? '')),
            'icon_file' => trim((string)($data['icon_file'] ?? 'icon_element.png')),
            'event_date' => trim((string)($data['event_date'] ?? date('Y-m-d'))),
            'sort_order' => (int)($data['sort_order'] ?? 0),
            'status' => trim((string)($data['status'] ?? 'active')),
        ];

        $randomnId = trim((string)($data['randomn_id'] ?? ''));
        if ($randomnId !== '') {
            return MympasModel::updatePlanning($randomnId, $payload);
        }

        $payload['randomn_id'] = MympasModel::uid('plan_');
        $payload['user_randomn_id'] = $userRandomnId;
        return MympasModel::createPlanning($payload);
    }

    public function deleteByType(string $type, string $randomnId): bool
    {
        switch ($type) {
            case 'card':
                return MympasModel::deleteCard($randomnId);
            case 'point':
                return MympasModel::deletePoint($randomnId);
            case 'week':
                return MympasModel::deleteWeekActivity($randomnId);
            case 'planning':
                return MympasModel::deletePlanning($randomnId);
            default:
                throw new \Exception('Tip invalid pentru delete.');
        }
    }
}