<?php

namespace Evasystem\Core\Mympas;

use Evasystem\Core\AdvancedCRUD;

class MympasModel
{
    public static function uid(string $prefix = 'map_'): string
    {
        try {
            return $prefix . bin2hex(random_bytes(8));
        } catch (\Throwable $e) {
            return $prefix . uniqid();
        }
    }

    public static function getDashboardCards(string $userRandomnId): array
    {
        return AdvancedCRUD::select(
            'mympas_dashboard_cards',
            '*',
            "WHERE user_randomn_id = '" . addslashes($userRandomnId) . "' AND status = 'active' ORDER BY sort_order ASC, id ASC"
        );
    }

    public static function getMapPoints(string $userRandomnId): array
    {
        return AdvancedCRUD::select(
            'mympas_map_points',
            '*',
            "WHERE user_randomn_id = '" . addslashes($userRandomnId) . "' AND status = 'active' ORDER BY id ASC"
        );
    }

    public static function getWeekActivity(string $userRandomnId): array
    {
        return AdvancedCRUD::select(
            'mympas_week_activity',
            '*',
            "WHERE user_randomn_id = '" . addslashes($userRandomnId) . "' ORDER BY sort_order ASC, id ASC"
        );
    }

    public static function getPlanning(string $userRandomnId): array
    {
        return AdvancedCRUD::select(
            'mympas_planning',
            '*',
            "WHERE user_randomn_id = '" . addslashes($userRandomnId) . "' AND status = 'active' ORDER BY sort_order ASC, id ASC"
        );
    }

    public static function createCard(array $data): bool
    {
        return AdvancedCRUD::create('mympas_dashboard_cards', $data);
    }

    public static function createPoint(array $data): bool
    {
        return AdvancedCRUD::create('mympas_map_points', $data);
    }

    public static function createWeekActivity(array $data): bool
    {
        return AdvancedCRUD::create('mympas_week_activity', $data);
    }

    public static function createPlanning(array $data): bool
    {
        return AdvancedCRUD::create('mympas_planning', $data);
    }

    public static function updateCard(string $randomnId, array $data): bool
    {
        return AdvancedCRUD::update('mympas_dashboard_cards', $data, "WHERE randomn_id = '" . addslashes($randomnId) . "'");
    }

    public static function updatePoint(string $randomnId, array $data): bool
    {
        return AdvancedCRUD::update('mympas_map_points', $data, "WHERE randomn_id = '" . addslashes($randomnId) . "'");
    }

    public static function updateWeekActivity(string $randomnId, array $data): bool
    {
        return AdvancedCRUD::update('mympas_week_activity', $data, "WHERE randomn_id = '" . addslashes($randomnId) . "'");
    }

    public static function updatePlanning(string $randomnId, array $data): bool
    {
        return AdvancedCRUD::update('mympas_planning', $data, "WHERE randomn_id = '" . addslashes($randomnId) . "'");
    }

    public static function deleteCard(string $randomnId): bool
    {
        return AdvancedCRUD::delete('mympas_dashboard_cards', "WHERE randomn_id = '" . addslashes($randomnId) . "'");
    }

    public static function deletePoint(string $randomnId): bool
    {
        return AdvancedCRUD::delete('mympas_map_points', "WHERE randomn_id = '" . addslashes($randomnId) . "'");
    }

    public static function deleteWeekActivity(string $randomnId): bool
    {
        return AdvancedCRUD::delete('mympas_week_activity', "WHERE randomn_id = '" . addslashes($randomnId) . "'");
    }

    public static function deletePlanning(string $randomnId): bool
    {
        return AdvancedCRUD::delete('mympas_planning', "WHERE randomn_id = '" . addslashes($randomnId) . "'");
    }
}