<?php

declare(strict_types=1);

namespace Evasystem\Controllers\Mybank;

use Evasystem\Core\Mybank\MybankModel;

class MybankService
{
    public function ensureAccount(string $userRandomnId): array
    {
        $account = MybankModel::getAccountByUserRandomnId($userRandomnId);

        if ($account) {
            return $account;
        }

        $accountData = [
            'randomn_id'        => MybankModel::uid('acc_'),
            'user_randomn_id'   => $userRandomnId,
            'plan_name'         => 'Premium',
            'plan_price'        => 19.00,
            'billing_cycle'     => 'monthly',
            'payment_status'    => 'paid',
            'auto_renew'        => 1,
            'next_billing_date' => date('Y-m-d', strtotime('+30 days')),
            'currency'          => 'EUR',
        ];

        MybankModel::createAccount($accountData);

        return MybankModel::getAccountByUserRandomnId($userRandomnId) ?? $accountData;
    }

    public function seedDemoDataIfEmpty(string $accountRandomnId): void
    {
        $card = MybankModel::getPrimaryCard($accountRandomnId);
        $billing = MybankModel::getBilling($accountRandomnId);
        $transactions = MybankModel::getTransactions($accountRandomnId);
        $invoices = MybankModel::getInvoices($accountRandomnId);

        if (!$card) {
            MybankModel::createCard([
                'randomn_id'        => MybankModel::uid('card_'),
                'account_randomn_id'=> $accountRandomnId,
                'card_holder'       => 'Radu Galac',
                'brand'             => 'VISA',
                'last4'             => '2345',
                'exp_month'         => '05',
                'exp_year'          => '2028',
                'is_primary'        => 1,
                'status'            => 'active',
            ]);
        }

        if (!$billing) {
            MybankModel::createBilling([
                'randomn_id'        => MybankModel::uid('bill_'),
                'account_randomn_id'=> $accountRandomnId,
                'company_name'      => 'Galac-Web SRL',
                'vat_idno'          => '1021602002600',
                'country'           => 'Moldova',
                'city'              => 'Drochia',
                'address_line'      => '27 August 32, of. 37',
                'invoice_email'     => 'contact@quizdigo.com',
                'postal_code'       => '5201',
            ]);
        }

        if (empty($transactions)) {
            $seedTx = [
                ['2026-03-10 10:00:00', 'Premium Subscription - Monthly', 'Visa **** 2345', 19.00, 'paid',    '#INV-2026-0310'],
                ['2026-02-10 10:00:00', 'Premium Subscription - Monthly', 'Visa **** 2345', 19.00, 'paid',    '#INV-2026-0210'],
                ['2026-01-10 10:00:00', 'Premium Subscription - Monthly', 'Visa **** 2345', 19.00, 'paid',    '#INV-2026-0110'],
                ['2025-12-10 10:00:00', 'Premium Subscription - Monthly', 'Visa **** 2345', 19.00, 'pending', '#INV-2025-1210'],
            ];

            foreach ($seedTx as $row) {
                MybankModel::createTransaction([
                    'randomn_id'         => MybankModel::uid('trx_'),
                    'account_randomn_id' => $accountRandomnId,
                    'transaction_date'   => $row[0],
                    'description'        => $row[1],
                    'payment_method'     => $row[2],
                    'amount'             => $row[3],
                    'currency'           => 'EUR',
                    'status'             => $row[4],
                    'invoice_no'         => $row[5],
                ]);
            }
        }

        if (empty($invoices)) {
            $seedInv = [
                ['#INV-2026-0310', '2026-03-10', 'Premium Subscription', 19.00],
                ['#INV-2026-0210', '2026-02-10', 'Premium Subscription', 19.00],
                ['#INV-2026-0110', '2026-01-10', 'Premium Subscription', 19.00],
                ['#INV-2025-1210', '2025-12-10', 'Premium Subscription', 19.00],
            ];

            foreach ($seedInv as $row) {
                MybankModel::createInvoice([
                    'randomn_id'         => MybankModel::uid('inv_'),
                    'account_randomn_id' => $accountRandomnId,
                    'invoice_no'         => $row[0],
                    'invoice_date'       => $row[1],
                    'description'        => $row[2],
                    'amount'             => $row[3],
                    'currency'           => 'EUR',
                    'pdf_file'           => '',
                    'status'             => 'issued',
                ]);
            }
        }
    }

    public function getDashboardData(string $userRandomnId): array
    {
        $account = $this->ensureAccount($userRandomnId);
        $this->seedDemoDataIfEmpty($account['randomn_id']);

        $account = MybankModel::getAccountByUserRandomnId($userRandomnId) ?? $account;
        $card = MybankModel::getPrimaryCard($account['randomn_id']);
        $billing = MybankModel::getBilling($account['randomn_id']);
        $transactions = MybankModel::getTransactions($account['randomn_id']);
        $invoices = MybankModel::getInvoices($account['randomn_id']);

        return [
            'account' => $account,
            'card' => $card,
            'billing' => $billing,
            'transactions' => $transactions,
            'invoices' => $invoices,
        ];
    }

    public function saveBilling(string $accountRandomnId, array $data): bool
    {
        $payload = [
            'company_name'  => trim((string)($data['company_name'] ?? '')),
            'vat_idno'      => trim((string)($data['vat_idno'] ?? '')),
            'country'       => trim((string)($data['country'] ?? '')),
            'city'          => trim((string)($data['city'] ?? '')),
            'address_line'  => trim((string)($data['address_line'] ?? '')),
            'invoice_email' => trim((string)($data['invoice_email'] ?? '')),
            'postal_code'   => trim((string)($data['postal_code'] ?? '')),
        ];

        $exists = MybankModel::getBilling($accountRandomnId);
        if ($exists) {
            return MybankModel::updateBilling($accountRandomnId, $payload);
        }

        $payload['randomn_id'] = MybankModel::uid('bill_');
        $payload['account_randomn_id'] = $accountRandomnId;

        return MybankModel::createBilling($payload);
    }

    public function addCard(string $accountRandomnId, array $data): bool
    {
        $cardNumber = preg_replace('/\D+/', '', (string)($data['card_number'] ?? ''));
        $holder = trim((string)($data['card_holder'] ?? ''));
        $brand = strtoupper(trim((string)($data['brand'] ?? 'VISA')));
        $expMonth = str_pad(trim((string)($data['exp_month'] ?? '')), 2, '0', STR_PAD_LEFT);
        $expYear = trim((string)($data['exp_year'] ?? ''));

        if ($holder === '' || strlen($cardNumber) < 4 || $expMonth === '' || $expYear === '') {
            throw new \Exception('Date card incomplete.');
        }

        $last4 = substr($cardNumber, -4);

        MybankModel::unsetPrimaryCards($accountRandomnId);

        return MybankModel::createCard([
            'randomn_id'         => MybankModel::uid('card_'),
            'account_randomn_id' => $accountRandomnId,
            'card_holder'        => $holder,
            'brand'              => $brand,
            'last4'              => $last4,
            'exp_month'          => $expMonth,
            'exp_year'           => $expYear,
            'is_primary'         => 1,
            'status'             => 'active',
        ]);
    }
    public function purchaseSubscription(string $userRandomnId, array $data): array
    {
        $account = $this->ensureAccount($userRandomnId);
        $accountRandomnId = (string)$account['randomn_id'];

        $planName = trim((string)($data['plan_name'] ?? ''));
        $billingCycle = trim((string)($data['billing_cycle'] ?? 'monthly'));
        $currency = trim((string)($data['currency'] ?? 'USD'));
        $paymentReference = trim((string)($data['payment_reference'] ?? ''));
        $note = trim((string)($data['note'] ?? ''));
        $paymentCardRandomnId = trim((string)($data['payment_card_randomn_id'] ?? ''));

        $prices = [
            'Free' => [
                'monthly' => 0,
                'yearly'  => 0,
            ],
            'Premium' => [
                'monthly' => 16,
                'yearly'  => 160,
            ],
            'Advance' => [
                'monthly' => 0,
                'yearly'  => 0,
            ],
        ];

        if (!isset($prices[$planName])) {
            throw new \Exception('Plan inexistent.');
        }

        if (!in_array($billingCycle, ['monthly', 'yearly'], true)) {
            throw new \Exception('billing_cycle invalid.');
        }

        if ($paymentCardRandomnId === '') {
            throw new \Exception('Nu a fost selectat niciun card.');
        }

        $cards = \Evasystem\Core\Mybank\MybankModel::getCards($accountRandomnId);
        $selectedCard = null;

        foreach ($cards as $card) {
            if ((string)$card['randomn_id'] === $paymentCardRandomnId) {
                $selectedCard = $card;
                break;
            }
        }

        if (!$selectedCard) {
            throw new \Exception('Cardul selectat nu aparține contului.');
        }

        $amount = $prices[$planName][$billingCycle];
        $invoiceNo = '#INV-' . date('Ymd-His') . '-' . strtoupper(substr(md5((string)microtime(true)), 0, 6));
        $cardMask = (string)$selectedCard['brand'] . ' **** ' . (string)$selectedCard['last4'];

        $orderData = [
            'randomn_id'         => \Evasystem\Core\Mybank\MybankModel::uid('ord_'),
            'account_randomn_id' => $accountRandomnId,
            'user_randomn_id'    => $userRandomnId,
            'plan_name'          => $planName,
            'billing_cycle'      => $billingCycle,
            'amount'             => $amount,
            'currency'           => $currency,
            'payment_method'     => 'saved_card',
            'payment_status'     => 'paid',
            'beneficiary'        => '',
            'iban'               => '',
            'bank_name'          => '',
            'swift'              => '',
            'payment_reference'  => $paymentReference,
            'note'               => $note,
            'invoice_no'         => $invoiceNo,
        ];

        \Evasystem\Core\Mybank\MybankModel::createSubscriptionOrder($orderData);

        \Evasystem\Core\Mybank\MybankModel::createInvoice([
            'randomn_id'         => \Evasystem\Core\Mybank\MybankModel::uid('inv_'),
            'account_randomn_id' => $accountRandomnId,
            'invoice_no'         => $invoiceNo,
            'invoice_date'       => date('Y-m-d'),
            'description'        => $planName . ' Subscription - ' . ucfirst($billingCycle),
            'amount'             => $amount,
            'currency'           => $currency,
            'pdf_file'           => '',
            'status'             => 'paid',
        ]);

        \Evasystem\Core\Mybank\MybankModel::createTransaction([
            'randomn_id'         => \Evasystem\Core\Mybank\MybankModel::uid('trx_'),
            'account_randomn_id' => $accountRandomnId,
            'transaction_date'   => date('Y-m-d H:i:s'),
            'description'        => $planName . ' Subscription - ' . ucfirst($billingCycle),
            'payment_method'     => $cardMask,
            'amount'             => $amount,
            'currency'           => $currency,
            'status'             => 'paid',
            'invoice_no'         => $invoiceNo,
        ]);

        $daysToAdd = $billingCycle === 'yearly' ? 365 : 30;

        \Evasystem\Core\Mybank\MybankModel::updateAccount($accountRandomnId, [
            'plan_name'         => $planName,
            'plan_price'        => $amount,
            'billing_cycle'     => $billingCycle,
            'payment_status'    => 'paid',
            'currency'          => $currency,
            'next_billing_date' => date('Y-m-d', strtotime('+' . $daysToAdd . ' days')),
        ]);

        return [
            'success' => true,
            'message' => 'Abonamentul a fost achitat cu succes.',
            'invoice_no' => $invoiceNo,
            'amount' => $amount,
            'currency' => $currency,
            'plan_name' => $planName,
            'billing_cycle' => $billingCycle,
            'card_mask' => $cardMask,
        ];
    }
    public function removePrimaryCard(string $accountRandomnId): bool
    {
        $card = MybankModel::getPrimaryCard($accountRandomnId);
        if (!$card) {
            throw new \Exception('Nu există card principal.');
        }

        return MybankModel::deleteCard((string)$card['randomn_id']);
    }
}