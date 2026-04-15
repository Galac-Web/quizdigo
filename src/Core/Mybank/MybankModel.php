<?php

namespace Evasystem\Core\Mybank;

use Evasystem\Core\AdvancedCRUD;

class MybankModel
{
    public static function uid(string $prefix = 'mb_'): string
    {
        try {
            return $prefix . bin2hex(random_bytes(8));
        } catch (\Throwable $e) {
            return $prefix . uniqid();
        }
    }

    public static function getAccountByUserRandomnId(string $userRandomnId): ?array
    {
        $rows = AdvancedCRUD::select('mybank_accounts', '*', "WHERE user_randomn_id = '" . addslashes($userRandomnId) . "' LIMIT 1");
        return $rows[0] ?? null;
    }

    public static function getAccountByRandomnId(string $accountRandomnId): ?array
    {
        $rows = AdvancedCRUD::select('mybank_accounts', '*', "WHERE randomn_id = '" . addslashes($accountRandomnId) . "' LIMIT 1");
        return $rows[0] ?? null;
    }

    public static function createAccount(array $data): bool
    {
        return AdvancedCRUD::create('mybank_accounts', $data);
    }

    public static function updateAccount(string $accountRandomnId, array $data): bool
    {
        return AdvancedCRUD::update('mybank_accounts', $data, "WHERE randomn_id = '" . addslashes($accountRandomnId) . "'");
    }

    public static function getPrimaryCard(string $accountRandomnId): ?array
    {
        $rows = AdvancedCRUD::select('mybank_cards', '*', "WHERE account_randomn_id = '" . addslashes($accountRandomnId) . "' AND is_primary = 1 LIMIT 1");
        return $rows[0] ?? null;
    }
    public static function createSubscriptionOrder(array $data): bool
    {
        return AdvancedCRUD::create('mybank_subscription_orders', $data);
    }

    public static function getSubscriptionOrders(string $accountRandomnId): array
    {
        return AdvancedCRUD::select(
            'mybank_subscription_orders',
            '*',
            "WHERE account_randomn_id = '" . addslashes($accountRandomnId) . "' ORDER BY id DESC"
        );
    }
    public static function getCards(string $accountRandomnId): array
    {
        return AdvancedCRUD::select('mybank_cards', '*', "WHERE account_randomn_id = '" . addslashes($accountRandomnId) . "' ORDER BY id DESC");
    }

    public static function createCard(array $data): bool
    {
        return AdvancedCRUD::create('mybank_cards', $data);
    }

    public static function updateCard(string $cardRandomnId, array $data): bool
    {
        return AdvancedCRUD::update('mybank_cards', $data, "WHERE randomn_id = '" . addslashes($cardRandomnId) . "'");
    }

    public static function unsetPrimaryCards(string $accountRandomnId): bool
    {
        return AdvancedCRUD::update('mybank_cards', ['is_primary' => 0], "WHERE account_randomn_id = '" . addslashes($accountRandomnId) . "'");
    }

    public static function deleteCard(string $cardRandomnId): bool
    {
        return AdvancedCRUD::delete('mybank_cards', "WHERE randomn_id = '" . addslashes($cardRandomnId) . "'");
    }

    public static function getBilling(string $accountRandomnId): ?array
    {
        $rows = AdvancedCRUD::select('mybank_billing', '*', "WHERE account_randomn_id = '" . addslashes($accountRandomnId) . "' LIMIT 1");
        return $rows[0] ?? null;
    }

    public static function createBilling(array $data): bool
    {
        return AdvancedCRUD::create('mybank_billing', $data);
    }

    public static function updateBilling(string $accountRandomnId, array $data): bool
    {
        return AdvancedCRUD::update('mybank_billing', $data, "WHERE account_randomn_id = '" . addslashes($accountRandomnId) . "'");
    }

    public static function getTransactions(string $accountRandomnId): array
    {
        return AdvancedCRUD::select('mybank_transactions', '*', "WHERE account_randomn_id = '" . addslashes($accountRandomnId) . "' ORDER BY transaction_date DESC");
    }

    public static function createTransaction(array $data): bool
    {
        return AdvancedCRUD::create('mybank_transactions', $data);
    }

    public static function getInvoices(string $accountRandomnId): array
    {
        return AdvancedCRUD::select('mybank_invoices', '*', "WHERE account_randomn_id = '" . addslashes($accountRandomnId) . "' ORDER BY invoice_date DESC");
    }

    public static function createInvoice(array $data): bool
    {
        return AdvancedCRUD::create('mybank_invoices', $data);
    }

    public static function getInvoiceByNo(string $accountRandomnId, string $invoiceNo): ?array
    {
        $rows = AdvancedCRUD::select(
            'mybank_invoices',
            '*',
            "WHERE account_randomn_id = '" . addslashes($accountRandomnId) . "' AND invoice_no = '" . addslashes($invoiceNo) . "' LIMIT 1"
        );

        return $rows[0] ?? null;
    }
}