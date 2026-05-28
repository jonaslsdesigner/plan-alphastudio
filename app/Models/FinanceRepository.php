<?php

namespace App\Models;

use App\Core\Database;

class FinanceRepository
{
    private ?array $cachedUser = null;

    public function __construct(private int $userId)
    {
        $this->checkDatabaseIntegrity();
    }

    private function checkDatabaseIntegrity(): void
    {
        $this->ensureCardInvoiceTable();
        $this->ensureCardPurchaseInstallmentsColumns();
        $this->ensureMonthlyBillsMonthColumn();
        $this->ensureMonthlyBillsPaymentOffsetColumn();
        $this->ensureMonthlyBillsAlwaysActive();
        $this->ensureIncomePlanningColumns();
        $this->ensureMonthlyItemStatusesTable();
        $this->ensureSortOrderColumns();
        $this->ensureMonthlySortOrdersTable();
    }

    public function dashboard(string $month): array
    {
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));
        $salary = 0.0;
        $otherIncome = $this->sumIncomeSources($month);
        $billsTotal = $this->sumBills($month);
        $transactionsExpenses = $this->sumTransactions('expense', $start, $end, null);
        $transactionsIncome = $this->sumTransactions('income', $start, $end, null);
        $cardsTotal = $this->sumCardInvoices($month);
        $commitmentsMonth = $this->sumCommitmentsForMonth($month);
        $commitmentsTotal = $this->sumCommitmentsForYear((int) date('Y', strtotime($start)), $month);
        $totalIncome = $otherIncome + $transactionsIncome;
        $plannedExpenses = $billsTotal + $transactionsExpenses + $cardsTotal;
        $paidTransactions = $this->sumTransactions('expense', $start, $end, 'paid');
        $pendingTransactions = $this->sumTransactions('expense', $start, $end, 'pending');
        $pendingCosts = $billsTotal + $cardsTotal + $pendingTransactions;

        return [
            'user' => $this->user(),
            'salary' => $salary,
            'otherIncome' => $otherIncome,
            'income' => $totalIncome,
            'billsTotal' => $billsTotal,
            'cardsTotal' => $cardsTotal,
            'commitmentsMonth' => $commitmentsMonth,
            'commitmentsTotal' => $commitmentsTotal,
            'expenses' => $plannedExpenses,
            'paid' => $paidTransactions,
            'pending' => $pendingCosts,
            'remaining' => $totalIncome - $plannedExpenses,
            'byCategory' => $this->costBreakdown($month, $transactionsExpenses, $commitmentsMonth),
            'latest' => $this->latestMovements($month),
            'bills' => $this->monthlyBills($month),
            'goals' => $this->goals(),
            'cards' => $this->creditCardsWithTotals($month),
            'commitments' => $this->commitmentsForYear((int) date('Y', strtotime($start)), $month),
        ];
    }

    public function user(): array
    {
        if ($this->cachedUser !== null) {
            return $this->cachedUser;
        }

        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$this->userId]);
        $user = $stmt->fetch() ?: [];
        $this->cachedUser = $user ? $this->normalizeAvatarPath($user) : [];
        return $this->cachedUser;
    }

    public function categories(string $month, ?string $type = null): array
    {
        $sql = "SELECT c.*
                FROM categories c
                LEFT JOIN monthly_sort_orders mso
                  ON mso.user_id = c.user_id
                 AND mso.table_name = 'categories'
                 AND mso.item_id = c.id
                 AND mso.reference_month = ?
                WHERE c.user_id = ?";
        $params = [$month, $this->userId];
        if ($type) {
            $sql .= ' AND c.type = ?';
            $params[] = $type;
        }
        $sql .= ' ORDER BY CASE WHEN mso.sort_order IS NULL THEN 1 ELSE 0 END ASC, mso.sort_order ASC, c.created_at ASC, c.id ASC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return array_map(fn (array $purchase) => $this->withCardPurchaseDisplayTitle($purchase), $stmt->fetchAll());
    }

    public function accounts(string $month): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT a.*
             FROM accounts a
             LEFT JOIN monthly_sort_orders mso
               ON mso.user_id = a.user_id
              AND mso.table_name = 'accounts'
              AND mso.item_id = a.id
              AND mso.reference_month = ?
             WHERE a.user_id = ?
             ORDER BY CASE WHEN mso.sort_order IS NULL THEN 1 ELSE 0 END ASC, mso.sort_order ASC, a.created_at ASC, a.id ASC"
        );
        $stmt->execute([$month, $this->userId]);
        return $stmt->fetchAll();
    }

    public function incomeSources(string $month): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT i.*
             FROM income_sources i
             LEFT JOIN monthly_sort_orders mso
               ON mso.user_id = i.user_id
              AND mso.table_name = 'income_sources'
              AND mso.item_id = i.id
              AND mso.reference_month = ?
             WHERE i.user_id = ? AND i.reference_month = ?
             ORDER BY CASE WHEN mso.sort_order IS NULL THEN 1 ELSE 0 END ASC, mso.sort_order ASC, i.created_at ASC, i.id ASC"
        );
        $stmt->execute([$month, $this->userId, $month]);
        return $stmt->fetchAll();
    }

    public function saveIncomeSource(array $data): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO income_sources (user_id, reference_month, title, amount, income_type, received_date, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $this->userId,
            $data['month'],
            trim($data['title']),
            money_value($data['amount']),
            $data['income_type'] ?? 'other',
            $this->dateInMonth($data['received_date'] ?? null, $data['month']),
            $data['status'] ?? 'pending',
        ]);
    }

    public function updateSalary(array $data): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET monthly_income = ? WHERE id = ?');
        $stmt->execute([money_value($data['monthly_income']), $this->userId]);
        $this->cachedUser = null;
    }

    public function updateIncomeSource(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE income_sources SET reference_month=?, title=?, amount=?, income_type=?, received_date=?, status=? WHERE id=? AND user_id=?'
        );
        $stmt->execute([
            $data['month'],
            trim($data['title']),
            money_value($data['amount']),
            $data['income_type'] ?? 'other',
            $this->dateInMonth($data['received_date'] ?? null, $data['month']),
            $data['status'] ?? 'pending',
            $id,
            $this->userId,
        ]);
    }

    public function transactions(string $month): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT t.*, c.name category, c.color, a.name account
             FROM transactions t
             LEFT JOIN categories c ON c.id = t.category_id
             LEFT JOIN accounts a ON a.id = t.account_id
             WHERE t.user_id = ? AND t.due_date BETWEEN ? AND ?
             ORDER BY t.due_date ASC, t.id DESC'
        );
        $start = $month . '-01';
        $stmt->execute([$this->userId, $start, date('Y-m-t', strtotime($start))]);
        return $stmt->fetchAll();
    }

    public function monthlyBills(string $month): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT b.*, c.name category, c.color
             FROM monthly_bills b
             LEFT JOIN monthly_sort_orders mso
               ON mso.user_id = b.user_id
              AND mso.table_name = 'monthly_bills'
              AND mso.item_id = b.id
              AND mso.reference_month = ?
             LEFT JOIN categories c ON c.id = b.category_id
             WHERE b.user_id = ? AND b.reference_month = ?
             ORDER BY CASE WHEN mso.sort_order IS NULL THEN 1 ELSE 0 END ASC, mso.sort_order ASC, b.created_at ASC, b.id ASC"
        );
        $stmt->execute([$month, $this->userId, $month]);
        return $stmt->fetchAll();
    }

    public function goals(): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM goals WHERE user_id = ? ORDER BY due_date IS NULL, due_date ASC, id DESC');
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll();
    }

    public function creditCards(string $month): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT c.*
             FROM credit_cards c
             LEFT JOIN monthly_sort_orders mso
               ON mso.user_id = c.user_id
              AND mso.table_name = 'credit_cards'
              AND mso.item_id = c.id
              AND mso.reference_month = ?
             WHERE c.user_id = ?
             ORDER BY CASE WHEN mso.sort_order IS NULL THEN 1 ELSE 0 END ASC, mso.sort_order ASC, c.created_at ASC, c.id ASC"
        );
        $stmt->execute([$month, $this->userId]);
        return $stmt->fetchAll();
    }

    public function creditCardsWithTotals(string $month): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT c.*,
                    COALESCE(p.responsible_total, 0) responsible_total,
                    COALESCE(i.amount, 0) invoice_total,
                    CASE
                        WHEN i.amount IS NULL THEN COALESCE(p.responsible_total, 0)
                        ELSE GREATEST(i.amount - COALESCE(p.responsible_total, 0), 0)
                    END total
             FROM credit_cards c
             LEFT JOIN monthly_sort_orders mso
               ON mso.user_id = c.user_id
              AND mso.table_name = 'credit_cards'
              AND mso.item_id = c.id
              AND mso.reference_month = ?
             LEFT JOIN (
                SELECT credit_card_id, user_id, reference_month, SUM(amount) responsible_total
                FROM card_purchases
                WHERE reference_month = ?
                GROUP BY credit_card_id, user_id, reference_month
             ) p ON p.credit_card_id = c.id AND p.user_id = c.user_id
             LEFT JOIN card_invoice_totals i ON i.credit_card_id = c.id AND i.reference_month = ? AND i.user_id = c.user_id
             WHERE c.user_id = ?
             ORDER BY CASE WHEN mso.sort_order IS NULL THEN 1 ELSE 0 END ASC, mso.sort_order ASC, c.created_at ASC, c.id ASC"
        );
        $stmt->execute([$month, $month, $month, $this->userId]);
        return $stmt->fetchAll();
    }

    public function saveCardInvoiceTotal(array $data): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO card_invoice_totals (user_id, credit_card_id, reference_month, amount)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE amount = VALUES(amount), updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            $this->userId,
            (int) $data['credit_card_id'],
            $data['month'],
            money_value($data['amount'] ?? 0),
        ]);
    }

    public function cardPurchases(string $month, ?int $cardId = null): array
    {
        $params = [$this->userId, $month];
        $cardFilter = '';
        if ($cardId) {
            $cardFilter = ' AND p.credit_card_id = ?';
            $params[] = $cardId;
        }

        $stmt = Database::connection()->prepare(
            "SELECT p.*, c.name card_name, c.color
             FROM card_purchases p
             LEFT JOIN monthly_sort_orders mso
               ON mso.user_id = p.user_id
              AND mso.table_name = 'card_purchases'
              AND mso.item_id = p.id
              AND mso.reference_month = ?
             LEFT JOIN credit_cards c ON c.id = p.credit_card_id
             WHERE p.user_id = ? AND p.reference_month = ?" . $cardFilter . "
             ORDER BY CASE WHEN mso.sort_order IS NULL THEN 1 ELSE 0 END ASC, mso.sort_order ASC, p.created_at ASC, p.id ASC"
        );
        array_unshift($params, $month);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function saveCreditCard(array $data): void
    {
        $closingDay = $this->dayFromDateOrValue($data['closing_date'] ?? null, $data['closing_day'] ?? null);
        $dueDay = $this->dayFromDateOrValue($data['due_date'] ?? null, $data['due_day'] ?? null);
        $stmt = Database::connection()->prepare('INSERT INTO credit_cards (user_id, name, closing_day, due_day, color) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $this->userId,
            trim($data['name']),
            $closingDay ?: null,
            $dueDay ?: null,
            $data['color'] ?: '#191929',
        ]);

        $cardId = (int) Database::connection()->lastInsertId();

        if (trim($data['invoice_amount'] ?? '') !== '') {
            $this->saveCardInvoiceTotal([
                'credit_card_id' => $cardId,
                'month' => $data['month'],
                'amount' => $data['invoice_amount'],
            ]);
        }
    }

    public function updateCreditCard(int $id, array $data): void
    {
        $closingDay = $this->dayFromDateOrValue($data['closing_date'] ?? null, $data['closing_day'] ?? null);
        $dueDay = $this->dayFromDateOrValue($data['due_date'] ?? null, $data['due_day'] ?? null);
        $stmt = Database::connection()->prepare('UPDATE credit_cards SET name=?, closing_day=?, due_day=?, color=? WHERE id=? AND user_id=?');
        $stmt->execute([
            trim($data['name']),
            $closingDay ?: null,
            $dueDay ?: null,
            $data['color'] ?: '#191929',
            $id,
            $this->userId,
        ]);

        if (trim($data['invoice_amount'] ?? '') !== '') {
            $this->saveCardInvoiceTotal([
                'credit_card_id' => $id,
                'month' => $data['month'],
                'amount' => $data['invoice_amount'],
            ]);
        } elseif (isset($data['month'])) {
            $stmtDel = Database::connection()->prepare('DELETE FROM card_invoice_totals WHERE user_id = ? AND credit_card_id = ? AND reference_month = ?');
            $stmtDel->execute([$this->userId, $id, $data['month']]);
        }
    }

    public function saveCardPurchase(array $data): void
    {
        $current = max(1, (int) ($data['installment_number'] ?? 1));
        $total = max($current, (int) ($data['installment_total'] ?? 1));
        $autoInstallments = $total > 1 && !empty($data['generate_installments']);
        $first = $autoInstallments && !empty($data['generate_previous_installments']) ? 1 : $current;
        $group = $total > 1 ? bin2hex(random_bytes(12)) : null;
        $last = $autoInstallments ? $total : $current;

        $stmt = Database::connection()->prepare(
            'INSERT INTO card_purchases (user_id, credit_card_id, reference_month, title, description, amount, purchase_date, installment_group, installment_number, installment_total, installment_auto)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        for ($number = $first; $number <= $last; $number++) {
            $offset = $number - $current;
            $stmt->execute([
                $this->userId,
                $data['credit_card_id'] ?: null,
                $this->monthOffset($data['month'], $offset),
                $this->normalizeCardPurchaseTitle($data['title'] ?? '', $total),
                trim($data['description'] ?? ''),
                money_value($data['amount']),
                $this->dateOffset($data['purchase_date'] ?? null, $offset),
                $group,
                $number,
                $total,
                $autoInstallments ? 1 : 0,
            ]);
        }
    }

    public function updateCardPurchase(int $id, array $data): void
    {
        $current = max(1, (int) ($data['installment_number'] ?? 1));
        $total = max($current, (int) ($data['installment_total'] ?? 1));
        $autoInstallments = $total > 1 && !empty($data['generate_installments']);
        $first = $autoInstallments && !empty($data['generate_previous_installments']) ? 1 : $current;
        $existing = $this->find('card_purchases', $id) ?: [];
        $group = (string) ($existing['installment_group'] ?? '');
        if ($autoInstallments && $group === '') {
            $group = bin2hex(random_bytes(12));
        }

        $stmt = Database::connection()->prepare(
            'UPDATE card_purchases SET credit_card_id=?, reference_month=?, title=?, description=?, amount=?, purchase_date=?, installment_group=?, installment_number=?, installment_total=?, installment_auto=?
             WHERE id=? AND user_id=?'
        );
        $stmt->execute([
            $data['credit_card_id'] ?: null,
            $data['month'],
            $this->normalizeCardPurchaseTitle($data['title'] ?? '', $total),
            trim($data['description'] ?? ''),
            money_value($data['amount']),
            $data['purchase_date'] ?: null,
            $group ?: null,
            $current,
            $total,
            $autoInstallments ? 1 : (int) ($existing['installment_auto'] ?? 0),
            $id,
            $this->userId,
        ]);

        if ($autoInstallments) {
            $delete = Database::connection()->prepare('DELETE FROM card_purchases WHERE user_id = ? AND installment_group = ? AND id <> ?');
            $delete->execute([$this->userId, $group, $id]);

            $insert = Database::connection()->prepare(
                'INSERT INTO card_purchases (user_id, credit_card_id, reference_month, title, description, amount, purchase_date, installment_group, installment_number, installment_total, installment_auto)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            for ($number = $first; $number <= $total; $number++) {
                if ($number === $current) {
                    continue;
                }

                $offset = $number - $current;
                $insert->execute([
                    $this->userId,
                    $data['credit_card_id'] ?: null,
                    $this->monthOffset($data['month'], $offset),
                    $this->normalizeCardPurchaseTitle($data['title'] ?? '', $total),
                    trim($data['description'] ?? ''),
                    money_value($data['amount']),
                    $this->dateOffset($data['purchase_date'] ?? null, $offset),
                    $group,
                    $number,
                    $total,
                    1,
                ]);
            }
        }
    }

    public function deleteCardPurchaseSeries(int $id): void
    {
        $purchase = $this->find('card_purchases', $id);
        if (!$purchase) {
            return;
        }

        $group = trim((string) ($purchase['installment_group'] ?? ''));
        if ($group === '') {
            $this->delete('card_purchases', $id);
            return;
        }

        $cleanup = Database::connection()->prepare(
            "DELETE mso
             FROM monthly_sort_orders mso
             INNER JOIN card_purchases p
               ON p.id = mso.item_id
              AND mso.table_name = 'card_purchases'
             WHERE mso.user_id = ? AND p.user_id = ? AND p.installment_group = ?"
        );
        $cleanup->execute([$this->userId, $this->userId, $group]);

        $stmt = Database::connection()->prepare('DELETE FROM card_purchases WHERE user_id = ? AND installment_group = ?');
        $stmt->execute([$this->userId, $group]);
    }

    public function deleteBillSeries(int $id): void
    {
        $bill = $this->find('monthly_bills', $id);
        if (!$bill) {
            return;
        }

        $categorySql = $bill['category_id'] === null ? 'category_id IS NULL' : 'category_id = ?';
        $params = [$this->userId, $bill['title'], $bill['amount'], $bill['due_day']];
        if ($bill['category_id'] !== null) {
            $params[] = $bill['category_id'];
        }

        $cleanup = Database::connection()->prepare(
            "DELETE mso
             FROM monthly_sort_orders mso
             INNER JOIN monthly_bills b
               ON b.id = mso.item_id
              AND mso.table_name = 'monthly_bills'
             WHERE mso.user_id = ?
               AND b.user_id = ?
               AND b.title = ?
               AND b.amount = ?
               AND b.due_day = ?
               AND {$categorySql}"
        );
        $cleanupParams = array_merge([$this->userId], $params);
        $cleanup->execute($cleanupParams);

        $stmt = Database::connection()->prepare(
            "DELETE FROM monthly_bills
             WHERE user_id = ?
               AND title = ?
               AND amount = ?
               AND due_day = ?
               AND {$categorySql}"
        );
        $stmt->execute($params);
    }

    public function commitments(string $month): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT c.*
             FROM commitments c
             LEFT JOIN monthly_sort_orders mso
               ON mso.user_id = c.user_id
              AND mso.table_name = 'commitments'
              AND mso.item_id = c.id
              AND mso.reference_month = ?
             WHERE c.user_id = ?
             ORDER BY CASE WHEN mso.sort_order IS NULL THEN 1 ELSE 0 END ASC, mso.sort_order ASC, c.created_at ASC, c.id ASC"
        );
        $stmt->execute([$month, $this->userId]);
        return $stmt->fetchAll();
    }

    public function commitmentsForMonth(string $month): array
    {
        $target = (int) str_replace('-', '', $month);
        return array_values(array_filter($this->commitments($month), function (array $commitment) use ($target) {
            if (($commitment['status'] ?? '') !== 'active') {
                return false;
            }
            $start = ((int) $commitment['start_year'] * 100) + (int) $commitment['start_month'];
            $endDate = strtotime(sprintf('%04d-%02d-01 +%d months', $commitment['start_year'], $commitment['start_month'], ((int) $commitment['duration_months']) - 1));
            $end = (int) date('Ym', $endDate);
            return $target >= $start && $target <= $end;
        }));
    }

    public function commitmentsForYear(int $year, string $month): array
    {
        return array_values(array_filter($this->commitments($month), function (array $commitment) use ($year) {
            return ($commitment['status'] ?? '') === 'active' && (int) $commitment['start_year'] === $year;
        }));
    }

    public function saveCommitment(array $data): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO commitments (user_id, title, description, amount, start_year, start_month, duration_months, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $this->userId,
            trim($data['title']),
            trim($data['description'] ?? ''),
            money_value($data['amount']),
            (int) $data['start_year'],
            (int) ($data['start_month'] ?? 1),
            max(1, (int) ($data['duration_months'] ?? 12)),
            $data['status'] ?? 'active',
        ]);
    }

    public function updateCommitment(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE commitments SET title=?, description=?, amount=?, start_year=?, start_month=?, duration_months=?, status=?
             WHERE id=? AND user_id=?'
        );
        $stmt->execute([
            trim($data['title']),
            trim($data['description'] ?? ''),
            money_value($data['amount']),
            (int) $data['start_year'],
            (int) ($data['start_month'] ?? 1),
            max(1, (int) ($data['duration_months'] ?? 12)),
            $data['status'] ?? 'active',
            $id,
            $this->userId,
        ]);
    }

    public function saveTransaction(array $data): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO transactions (user_id, category_id, account_id, type, title, amount, due_date, paid_at, status, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $status = $data['status'] ?? 'pending';
        $stmt->execute([
            $this->userId,
            $data['category_id'] ?: null,
            $data['account_id'] ?: null,
            $data['type'],
            trim($data['title']),
            money_value($data['amount']),
            $data['due_date'],
            $status === 'paid' ? ($data['paid_at'] ?: date('Y-m-d')) : null,
            $status,
            trim($data['notes'] ?? ''),
        ]);
    }

    public function updateTransaction(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE transactions SET category_id=?, account_id=?, type=?, title=?, amount=?, due_date=?, paid_at=?, status=?, notes=? WHERE id=? AND user_id=?'
        );
        $status = $data['status'] ?? 'pending';
        $stmt->execute([
            $data['category_id'] ?: null,
            $data['account_id'] ?: null,
            $data['type'],
            trim($data['title']),
            money_value($data['amount']),
            $data['due_date'],
            $status === 'paid' ? ($data['paid_at'] ?: date('Y-m-d')) : null,
            $status,
            trim($data['notes'] ?? ''),
            $id,
            $this->userId,
        ]);
    }

    public function findTransaction(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM transactions WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $this->userId]);
        return $stmt->fetch() ?: null;
    }

    public function roadmap(string $month): array
    {
        $items = [];
        $statusMap = $this->monthlyStatusMap($month);
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));

        foreach ($this->incomeSources($month) as $income) {
            $statusMap['income_source:' . (int) $income['id']] = [
                'status' => $income['status'] ?? 'pending',
                'actual_date' => $income['received_date'] ?? null,
                'actual_amount' => null,
            ];
            $items[] = $this->roadmapItem(
                'income_source',
                (int) $income['id'],
                $income['title'],
                $this->incomeTypeLabel((string) ($income['income_type'] ?? 'other')),
                'income',
                (float) $income['amount'],
                $income['received_date'] ?: $month . '-01',
                $statusMap
            );
        }

        foreach ($this->monthlyBills($month) as $bill) {
            $items[] = $this->roadmapItem(
                'monthly_bill',
                (int) $bill['id'],
                $bill['title'],
                $bill['category'] ? 'Conta - ' . $bill['category'] : 'Conta',
                'expense',
                (float) $bill['amount'],
                $this->paymentDateForMonth($month, (int) $bill['due_day'], (int) ($bill['payment_month_offset'] ?? 0)),
                $statusMap
            );
        }

        foreach ($this->creditCardsWithTotals($month) as $card) {
            $amount = (float) ($card['total'] ?? 0);
            if ($amount <= 0) {
                continue;
            }

            $items[] = $this->roadmapItem(
                'card_invoice',
                (int) $card['id'],
                'Fatura ' . $card['name'],
                'Cartao',
                'expense',
                $amount,
                $this->paymentDateForMonth($month, (int) ($card['due_day'] ?: 1)),
                $statusMap
            );
        }

        foreach ($this->transactions($month) as $transaction) {
            $items[] = [
                'item_type' => 'transaction',
                'item_id' => (int) $transaction['id'],
                'title' => $transaction['title'],
                'category' => $transaction['category'] ?? 'Lancamento',
                'direction' => $transaction['type'],
                'amount' => (float) $transaction['amount'],
                'actual_amount' => (float) $transaction['amount'],
                'date' => $transaction['paid_at'] ?: $transaction['due_date'],
                'status' => $transaction['status'] === 'paid' ? 'paid' : 'pending',
                'is_realized' => $transaction['status'] === 'paid',
                'source_url' => '/transactions?month=' . urlencode($month) . '&edit=' . (int) $transaction['id'],
            ];
        }

        usort($items, static function (array $a, array $b): int {
            $date = strcmp((string) $a['date'], (string) $b['date']);
            if ($date !== 0) {
                return $date;
            }

            if ($a['direction'] !== $b['direction']) {
                return $a['direction'] === 'income' ? -1 : 1;
            }

            return strcmp((string) $a['title'], (string) $b['title']);
        });

        $running = 0.0;
        foreach ($items as $index => $item) {
            $signed = ($item['direction'] === 'income' ? 1 : -1) * (float) ($item['actual_amount'] ?? $item['amount']);
            $running += $signed;
            $items[$index]['signed_amount'] = $signed;
            $items[$index]['running_balance'] = $running;
        }

        $today = date('Y-m-d');
        $pendingBeforeNextIncome = 0.0;
        $nextIncomeDate = null;
        foreach ($items as $item) {
            if ($item['date'] >= $today && $item['direction'] === 'income' && !$item['is_realized']) {
                $nextIncomeDate = $item['date'];
                break;
            }
        }

        foreach ($items as $item) {
            if ($item['direction'] !== 'expense' || $item['is_realized']) {
                continue;
            }
            if ($item['date'] >= $today && ($nextIncomeDate === null || $item['date'] < $nextIncomeDate)) {
                $pendingBeforeNextIncome += (float) $item['amount'];
            }
        }

        $received = array_sum(array_map(static fn (array $item): float => $item['direction'] === 'income' && $item['is_realized'] ? (float) $item['actual_amount'] : 0.0, $items));
        $paid = array_sum(array_map(static fn (array $item): float => $item['direction'] === 'expense' && $item['is_realized'] ? (float) $item['actual_amount'] : 0.0, $items));
        $plannedIncome = array_sum(array_map(static fn (array $item): float => $item['direction'] === 'income' ? (float) $item['amount'] : 0.0, $items));
        $plannedExpense = array_sum(array_map(static fn (array $item): float => $item['direction'] === 'expense' ? (float) $item['amount'] : 0.0, $items));

        return [
            'items' => $items,
            'summary' => [
                'planned_income' => $plannedIncome,
                'planned_expense' => $plannedExpense,
                'received' => $received,
                'paid' => $paid,
                'real_balance' => $received - $paid,
                'projected_balance' => $plannedIncome - $plannedExpense,
                'reserved_until_next_income' => $pendingBeforeNextIncome,
                'available_until_next_income' => ($received - $paid) - $pendingBeforeNextIncome,
                'next_income_date' => $nextIncomeDate,
                'start' => $start,
                'end' => $end,
            ],
        ];
    }

    public function updateRoadmapStatus(array $data): void
    {
        $itemType = (string) ($data['item_type'] ?? '');
        $itemId = (int) ($data['item_id'] ?? 0);
        $month = (string) ($data['month'] ?? date('Y-m'));
        $status = (string) ($data['status'] ?? 'pending');
        if ($itemId <= 0 || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            return;
        }

        if ($itemType === 'transaction') {
            $transaction = $this->findTransaction($itemId);
            if (!$transaction) {
                return;
            }
            $newStatus = $status === 'paid' ? 'paid' : 'pending';
            $stmt = Database::connection()->prepare('UPDATE transactions SET status = ?, paid_at = ? WHERE id = ? AND user_id = ?');
            $stmt->execute([$newStatus, $newStatus === 'paid' ? date('Y-m-d') : null, $itemId, $this->userId]);
            return;
        }

        if ($itemType === 'income_source') {
            $income = $this->find('income_sources', $itemId);
            if (!$income) {
                return;
            }
            $newStatus = $status === 'received' ? 'received' : 'pending';
            $stmt = Database::connection()->prepare('UPDATE income_sources SET status = ?, received_date = ? WHERE id = ? AND user_id = ?');
            $stmt->execute([
                $newStatus,
                $newStatus === 'received' ? date('Y-m-d') : ($income['received_date'] ?: $this->dateInMonth(null, $month)),
                $itemId,
                $this->userId,
            ]);
            return;
        }

        if (!in_array($itemType, ['monthly_bill', 'card_invoice'], true)) {
            return;
        }

        $normalizedStatus = $status === 'paid' ? 'paid' : 'pending';
        $actualDate = $normalizedStatus === 'paid' ? date('Y-m-d') : null;
        $stmt = Database::connection()->prepare(
            'INSERT INTO monthly_item_statuses (user_id, item_type, item_id, reference_month, status, actual_date)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE status = VALUES(status), actual_date = VALUES(actual_date), updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([$this->userId, $itemType, $itemId, $month, $normalizedStatus, $actualDate]);
    }

    public function find(string $table, int $id): ?array
    {
        $allowed = ['categories', 'accounts', 'monthly_bills', 'goals', 'credit_cards', 'card_purchases', 'commitments', 'income_sources'];
        if (!in_array($table, $allowed, true)) {
            return null;
        }
        $stmt = Database::connection()->prepare("SELECT * FROM {$table} WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $this->userId]);
        return $stmt->fetch() ?: null;
    }

    public function delete(string $table, int $id): void
    {
        $allowed = ['transactions', 'categories', 'accounts', 'monthly_bills', 'goals', 'credit_cards', 'card_purchases', 'commitments', 'income_sources'];
        if (!in_array($table, $allowed, true)) {
            return;
        }
        $stmt = Database::connection()->prepare("DELETE FROM {$table} WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $this->userId]);

        $cleanup = Database::connection()->prepare('DELETE FROM monthly_sort_orders WHERE user_id = ? AND table_name = ? AND item_id = ?');
        $cleanup->execute([$this->userId, $table, $id]);
    }

    public function saveCategory(array $data): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO categories (user_id, name, type, color, icon) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$this->userId, trim($data['name']), $data['type'], $data['color'] ?: '#4f46e5', $data['icon'] ?: 'tag']);
    }

    public function updateCategory(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare('UPDATE categories SET name=?, type=?, color=?, icon=? WHERE id=? AND user_id=?');
        $stmt->execute([trim($data['name']), $data['type'], $data['color'] ?: '#4f46e5', $data['icon'] ?: 'tag', $id, $this->userId]);
    }

    public function saveAccount(array $data): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO accounts (user_id, name, type, balance) VALUES (?, ?, ?, ?)');
        $stmt->execute([$this->userId, trim($data['name']), $data['type'], money_value($data['balance'] ?? 0)]);
    }

    public function updateAccount(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare('UPDATE accounts SET name=?, type=?, balance=? WHERE id=? AND user_id=?');
        $stmt->execute([trim($data['name']), $data['type'], money_value($data['balance'] ?? 0), $id, $this->userId]);
    }

    public function saveBill(array $data): void
    {
        $month = $data['month'] ?? date('Y-m');
        $categoryId = $data['category_id'] ?: null;
        $title = trim($data['title']);
        $amount = money_value($data['amount']);
        $dueDay = $this->dayFromDateOrValue($data['due_date'] ?? null, $data['due_day'] ?? 10);
        $paymentMonthOffset = $this->paymentMonthOffsetFromDate($month, $data['due_date'] ?? null);
        $isRecurring = isset($data['auto_create']) ? 1 : 0;

        $stmt = Database::connection()->prepare(
            'INSERT INTO monthly_bills (user_id, reference_month, category_id, title, amount, due_day, payment_month_offset, active, auto_create) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $this->userId,
            $month,
            $categoryId,
            $title,
            $amount,
            $dueDay,
            $paymentMonthOffset,
            1,
            $isRecurring,
        ]);

        if ($isRecurring === 1) {
            $this->createRecurringBillsUntilYearEnd($month, $categoryId, $title, $amount, $dueDay, $paymentMonthOffset);
            $this->syncRecurringBillsFromMonth($month);
        }
    }

    public function updateBill(int $id, array $data): void
    {
        $month = $data['month'] ?? date('Y-m');
        $categoryId = $data['category_id'] ?: null;
        $title = trim($data['title']);
        $amount = money_value($data['amount']);
        $dueDay = $this->dayFromDateOrValue($data['due_date'] ?? null, $data['due_day'] ?? 10);
        $paymentMonthOffset = $this->paymentMonthOffsetFromDate($month, $data['due_date'] ?? null);
        $isRecurring = isset($data['auto_create']) ? 1 : 0;

        $stmt = Database::connection()->prepare(
            'UPDATE monthly_bills SET reference_month=?, category_id=?, title=?, amount=?, due_day=?, payment_month_offset=?, active=?, auto_create=? WHERE id=? AND user_id=?'
        );
        $stmt->execute([
            $month,
            $categoryId,
            $title,
            $amount,
            $dueDay,
            $paymentMonthOffset,
            1,
            $isRecurring,
            $id,
            $this->userId,
        ]);

        if ($isRecurring === 1) {
            $this->createRecurringBillsUntilYearEnd($month, $categoryId, $title, $amount, $dueDay, $paymentMonthOffset, $id);
            $this->syncRecurringBillsFromMonth($month);
        }
    }

    public function syncRecurringBillsFromMonth(string $sourceMonth): void
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $sourceMonth)) {
            return;
        }

        $sourceDate = \DateTime::createFromFormat('Y-m', $sourceMonth);
        if (!$sourceDate) {
            return;
        }

        $stmt = Database::connection()->prepare(
            'SELECT *
             FROM monthly_bills
             WHERE user_id = ?
               AND reference_month = ?
               AND auto_create = 1
             ORDER BY created_at ASC, id ASC'
        );
        $stmt->execute([$this->userId, $sourceMonth]);
        $sourceBills = $stmt->fetchAll();
        if (!$sourceBills) {
            return;
        }

        $year = (int) $sourceDate->format('Y');
        $insert = Database::connection()->prepare(
            'INSERT INTO monthly_bills (user_id, reference_month, category_id, title, amount, due_day, payment_month_offset, active, auto_create)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1)'
        );
        $update = Database::connection()->prepare(
            'UPDATE monthly_bills
             SET category_id = ?, title = ?, amount = ?, due_day = ?, payment_month_offset = ?, active = 1, auto_create = 1
             WHERE id = ? AND user_id = ?'
        );
        $delete = Database::connection()->prepare('DELETE FROM monthly_bills WHERE id = ? AND user_id = ?');
        $cleanupSort = Database::connection()->prepare("DELETE FROM monthly_sort_orders WHERE user_id = ? AND table_name = 'monthly_bills' AND item_id = ?");

        for ($monthNumber = (int) $sourceDate->format('n') + 1; $monthNumber <= 12; $monthNumber++) {
            $referenceMonth = sprintf('%04d-%02d', $year, $monthNumber);
            foreach ($sourceBills as $source) {
                $find = Database::connection()->prepare(
                    "SELECT *
                     FROM monthly_bills
                     WHERE user_id = ?
                       AND reference_month = ?
                       AND title = ?
                     ORDER BY
                       CASE WHEN amount = ? AND due_day = ? AND payment_month_offset = ? THEN 0 ELSE 1 END,
                       id DESC"
                );
                $find->execute([$this->userId, $referenceMonth, $source['title'], $source['amount'], $source['due_day'], $source['payment_month_offset'] ?? 0]);
                $matches = $find->fetchAll();

                if (!$matches) {
                    $insert->execute([
                        $this->userId,
                        $referenceMonth,
                        $source['category_id'],
                        $source['title'],
                        $source['amount'],
                        $source['due_day'],
                        $source['payment_month_offset'] ?? 0,
                    ]);
                    continue;
                }

                $keep = array_shift($matches);
                $update->execute([
                    $source['category_id'],
                    $source['title'],
                    $source['amount'],
                    $source['due_day'],
                    $source['payment_month_offset'] ?? 0,
                    $keep['id'],
                    $this->userId,
                ]);

                foreach ($matches as $duplicate) {
                    $cleanupSort->execute([$this->userId, $duplicate['id']]);
                    $delete->execute([$duplicate['id'], $this->userId]);
                }
            }
        }
    }

    public function saveGoal(array $data): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO goals (user_id, name, target_amount, current_amount, due_date, color) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $this->userId,
            trim($data['name']),
            money_value($data['target_amount']),
            money_value($data['current_amount'] ?? 0),
            $data['due_date'] ?: null,
            $data['color'] ?: '#2563eb',
        ]);
    }

    public function updateGoal(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE goals SET name=?, target_amount=?, current_amount=?, due_date=?, color=? WHERE id=? AND user_id=?'
        );
        $stmt->execute([
            trim($data['name']),
            money_value($data['target_amount']),
            money_value($data['current_amount'] ?? 0),
            $data['due_date'] ?: null,
            $data['color'] ?: '#2563eb',
            $id,
            $this->userId,
        ]);
    }

    public function updateSettings(array $data): void
    {
        $avatarPath = $this->user()['avatar_path'] ?? null;
        if (!empty($_FILES['avatar']['tmp_name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
            $extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $dir = __DIR__ . '/../../public/uploads';
                if (!is_dir($dir)) {
                    mkdir($dir, 0775, true);
                }
                $filename = 'avatar-' . $this->userId . '-' . time() . '.' . $extension;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . '/' . $filename)) {
                    $avatarPath = 'uploads/' . $filename;
                }
            }
        }

        $stmt = Database::connection()->prepare('UPDATE users SET name=?, nickname=NULL, age=?, monthly_income=?, currency=?, theme_color=?, avatar_path=? WHERE id=?');
        $stmt->execute([
            trim($data['name']),
            $data['age'] === '' ? null : (int) $data['age'],
            money_value($data['monthly_income']),
            $data['currency'] ?: 'BRL',
            $data['theme_color'] ?: '#066ab5',
            $avatarPath,
            $this->userId,
        ]);
        $this->cachedUser = null;
    }

    public function reorderItems(string $table, array $ids, string $month): void
    {
        $allowed = ['card_purchases', 'monthly_bills', 'commitments', 'income_sources', 'accounts', 'categories'];
        if (!in_array($table, $allowed, true) || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            return;
        }

        $position = 1;
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id <= 0) {
                continue;
            }

            $this->applySortOrder($table, $id, $position, $month);
            $position++;
        }
    }

    private function normalizeAvatarPath(array $user): array
    {
        $avatarPath = $user['avatar_path'] ?? null;
        if ($avatarPath && $this->avatarFileExists($avatarPath)) {
            return $user;
        }

        $fallback = $this->latestAvailableAvatarPath();
        if ($fallback === null) {
            if ($avatarPath !== null && $avatarPath !== '') {
                $this->persistAvatarPath(null);
                $user['avatar_path'] = null;
            }
            return $user;
        }

        if ($avatarPath !== $fallback) {
            $this->persistAvatarPath($fallback);
        }

        $user['avatar_path'] = $fallback;
        return $user;
    }

    private function avatarFileExists(string $avatarPath): bool
    {
        $fullPath = __DIR__ . '/../../public/' . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $avatarPath), DIRECTORY_SEPARATOR);
        return is_file($fullPath);
    }

    private function latestAvailableAvatarPath(): ?string
    {
        $pattern = __DIR__ . '/../../public/uploads/avatar-' . $this->userId . '-*';
        $matches = glob($pattern) ?: [];
        if (!$matches) {
            return null;
        }

        usort($matches, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));
        return 'uploads/' . basename($matches[0]);
    }

    private function persistAvatarPath(?string $avatarPath): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET avatar_path = ? WHERE id = ?');
        $stmt->execute([$avatarPath, $this->userId]);
    }

    private function sumIncomeSources(string $month): float
    {
        $stmt = Database::connection()->prepare('SELECT COALESCE(SUM(amount), 0) FROM income_sources WHERE user_id = ? AND reference_month = ?');
        $stmt->execute([$this->userId, $month]);
        return (float) $stmt->fetchColumn();
    }

    private function roadmapItem(
        string $itemType,
        int $itemId,
        string $title,
        string $category,
        string $direction,
        float $amount,
        string $date,
        array $statusMap
    ): array {
        $status = $statusMap[$itemType . ':' . $itemId] ?? [];
        $isIncome = $direction === 'income';
        $resolvedStatus = (string) ($status['status'] ?? ($isIncome ? 'pending' : 'pending'));
        $isRealized = in_array($resolvedStatus, ['received', 'paid'], true);
        $actualAmount = isset($status['actual_amount']) && $status['actual_amount'] !== null
            ? (float) $status['actual_amount']
            : $amount;

        return [
            'item_type' => $itemType,
            'item_id' => $itemId,
            'title' => $title,
            'category' => $category,
            'direction' => $direction,
            'amount' => $amount,
            'actual_amount' => $actualAmount,
            'date' => ($status['actual_date'] ?? null) ?: $date,
            'planned_date' => $date,
            'status' => $resolvedStatus,
            'is_realized' => $isRealized,
            'source_url' => $this->roadmapSourceUrl($itemType, $itemId),
        ];
    }

    private function monthlyStatusMap(string $month): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM monthly_item_statuses WHERE user_id = ? AND reference_month = ?');
        $stmt->execute([$this->userId, $month]);
        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[$row['item_type'] . ':' . $row['item_id']] = $row;
        }
        return $map;
    }

    private function roadmapSourceUrl(string $itemType, int $itemId): string
    {
        return match ($itemType) {
            'income_source' => '/income?edit=' . $itemId,
            'monthly_bill' => '/bills?edit=' . $itemId,
            'card_invoice' => '/cards?selected_card=' . $itemId . '&edit_card=' . $itemId,
            default => '/',
        };
    }

    private function incomeTypeLabel(string $type): string
    {
        return match ($type) {
            'first_half' => 'Quinzena',
            'month_end' => 'Final de mes',
            default => 'Outras rendas',
        };
    }

    private function dayDate(string $month, int $day): string
    {
        $last = (int) date('t', strtotime($month . '-01'));
        return sprintf('%s-%02d', $month, max(1, min($last, $day)));
    }

    private function paymentDateForMonth(string $month, int $day, int $monthOffset = 0): string
    {
        return $this->dayDate($this->monthOffset($month, $monthOffset), $day);
    }

    private function dateInMonth(?string $date, string $month): string
    {
        if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        return $month . '-01';
    }

    private function dayFromDateOrValue(mixed $date, mixed $fallback = null): int
    {
        $date = trim((string) $date);
        if (preg_match('/^\d{4}-\d{2}-(\d{2})$/', $date, $matches)) {
            return max(1, min(31, (int) $matches[1]));
        }

        return max(1, min(31, (int) $fallback));
    }

    private function paymentMonthOffsetFromDate(string $referenceMonth, mixed $date): int
    {
        $date = trim((string) $date);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return 0;
        }

        $selectedMonth = substr($date, 0, 7);
        if ($selectedMonth === $referenceMonth) {
            return 0;
        }

        if ($selectedMonth === $this->monthOffset($referenceMonth, 1)) {
            return 1;
        }

        return 0;
    }

    private function sumBills(string $month): float
    {
        $stmt = Database::connection()->prepare('SELECT COALESCE(SUM(amount), 0) FROM monthly_bills WHERE user_id = ? AND reference_month = ?');
        $stmt->execute([$this->userId, $month]);
        return (float) $stmt->fetchColumn();
    }

    private function sumCommitmentsForMonth(string $month): float
    {
        return array_sum(array_map(fn ($item) => (float) $item['amount'], $this->commitmentsForMonth($month)));
    }

    private function sumCommitmentsForYear(int $year, string $month): float
    {
        return array_sum(array_map(fn ($item) => (float) $item['amount'], $this->commitmentsForYear($year, $month)));
    }

    private function sumTransactions(string $type, string $start, string $end, ?string $status): float
    {
        $sql = 'SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = ? AND type = ? AND due_date BETWEEN ? AND ?';
        $params = [$this->userId, $type, $start, $end];
        if ($status) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }

    private function costBreakdown(string $month, float $transactionsExpenses, float $commitmentsMonth): array
    {
        return [
            ['name' => 'Contas', 'color' => '#6d5df5', 'total' => $this->sumBills($month)],
            ['name' => 'Cartões de Crédito', 'color' => '#191929', 'total' => $this->sumCardInvoices($month)],
            ['name' => 'Lançamentos', 'color' => '#8f91a0', 'total' => $transactionsExpenses],
        ];
    }

    private function latestMovements(string $month): array
    {
        $items = [];

        foreach ($this->incomeSources($month) as $income) {
            $items[] = [
                'title' => $income['title'],
                'category' => 'Renda extra',
                'amount' => $income['amount'],
                'type' => 'income',
                'due_date' => $month . '-01',
            ];
        }

        foreach ($this->monthlyBills($month) as $bill) {
            $items[] = [
                'title' => $bill['title'],
                'category' => 'Conta',
                'amount' => $bill['amount'],
                'type' => 'expense',
                'due_date' => sprintf('%s-%02d', $month, max(1, min(31, (int) $bill['due_day']))),
            ];
        }

        foreach ($this->cardPurchases($month) as $purchase) {
            $items[] = [
                'title' => $purchase['title'],
                'category' => $purchase['card_name'] ?? 'Cartão',
                'amount' => $purchase['amount'],
                'type' => 'expense',
                'due_date' => $purchase['purchase_date'] ?: $month . '-01',
            ];
        }

        foreach ($this->recentCommitments($month) as $commitment) {
            $items[] = [
                'title' => $commitment['title'],
                'category' => 'Compromisso',
                'amount' => $commitment['amount'],
                'type' => 'expense',
                'due_date' => substr((string) ($commitment['created_at'] ?? ''), 0, 10) ?: ($month . '-01'),
                'sort_date' => (string) ($commitment['created_at'] ?? ($month . '-01 00:00:00')),
            ];
        }

        usort($items, static function (array $a, array $b): int {
            return strcmp((string) ($b['sort_date'] ?? $b['due_date'] ?? ''), (string) ($a['sort_date'] ?? $a['due_date'] ?? ''));
        });

        return array_slice($items, 0, 8);
    }

    private function recentCommitments(string $month): array
    {
        $start = $month . '-01 00:00:00';
        $end = date('Y-m-t 23:59:59', strtotime($month . '-01'));
        $stmt = Database::connection()->prepare(
            'SELECT *
             FROM commitments
             WHERE user_id = ? AND status = ? AND created_at BETWEEN ? AND ?
             ORDER BY created_at DESC, id DESC'
        );
        $stmt->execute([$this->userId, 'active', $start, $end]);
        return $stmt->fetchAll();
    }

    private function ensureCardInvoiceTable(): void
    {
        Database::connection()->exec('
            CREATE TABLE IF NOT EXISTS card_invoice_totals (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                credit_card_id BIGINT UNSIGNED NOT NULL,
                reference_month VARCHAR(7) NOT NULL,
                amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_invoice (user_id, credit_card_id, reference_month),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (credit_card_id) REFERENCES credit_cards(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    private function ensureCardPurchaseInstallmentsColumns(): void
    {
        $columns = [
            'installment_group' => 'ALTER TABLE card_purchases ADD COLUMN installment_group VARCHAR(40) NULL AFTER purchase_date',
            'installment_number' => 'ALTER TABLE card_purchases ADD COLUMN installment_number SMALLINT UNSIGNED NOT NULL DEFAULT 1 AFTER installment_group',
            'installment_total' => 'ALTER TABLE card_purchases ADD COLUMN installment_total SMALLINT UNSIGNED NOT NULL DEFAULT 1 AFTER installment_number',
            'installment_auto' => 'ALTER TABLE card_purchases ADD COLUMN installment_auto TINYINT(1) NOT NULL DEFAULT 0 AFTER installment_total',
        ];

        foreach ($columns as $sql) {
            try {
                Database::connection()->exec($sql);
            } catch (\Throwable $exception) {
                // Column already exists.
            }
        }

        try {
            Database::connection()->exec('CREATE INDEX idx_card_purchases_installments ON card_purchases (user_id, installment_group, installment_number)');
        } catch (\Throwable $exception) {
            // Index already exists.
        }
    }

    private function ensureIncomePlanningColumns(): void
    {
        $columns = [
            'income_type' => "ALTER TABLE income_sources ADD COLUMN income_type VARCHAR(40) NOT NULL DEFAULT 'other' AFTER amount",
            'received_date' => 'ALTER TABLE income_sources ADD COLUMN received_date DATE NULL AFTER income_type',
            'status' => "ALTER TABLE income_sources ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER received_date",
        ];

        foreach ($columns as $sql) {
            try {
                Database::connection()->exec($sql);
            } catch (\Throwable $exception) {
                // Column already exists.
            }
        }

        Database::connection()->exec("UPDATE income_sources SET received_date = CONCAT(reference_month, '-01') WHERE received_date IS NULL");
    }

    private function ensureMonthlyItemStatusesTable(): void
    {
        Database::connection()->exec('
            CREATE TABLE IF NOT EXISTS monthly_item_statuses (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                item_type VARCHAR(40) NOT NULL,
                item_id BIGINT UNSIGNED NOT NULL,
                reference_month CHAR(7) NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT "pending",
                actual_date DATE NULL,
                actual_amount DECIMAL(12,2) NULL,
                notes TEXT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_monthly_item_status (user_id, item_type, item_id, reference_month),
                INDEX idx_monthly_item_statuses_month (user_id, reference_month, status),
                CONSTRAINT fk_monthly_item_statuses_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    private function ensureSortOrderColumns(): void
    {
        foreach (['income_sources', 'credit_cards', 'card_purchases', 'commitments', 'categories', 'accounts', 'monthly_bills'] as $table) {
            try {
                Database::connection()->exec("ALTER TABLE {$table} ADD COLUMN sort_order INT UNSIGNED NOT NULL DEFAULT 0 AFTER user_id");
            } catch (\Throwable $exception) {
                // Column already exists.
            }

            try {
                Database::connection()->exec("CREATE INDEX idx_{$table}_sort_order ON {$table} (user_id, sort_order)");
            } catch (\Throwable $exception) {
                // Index already exists.
            }
        }
    }

    private function ensureMonthlySortOrdersTable(): void
    {
        Database::connection()->exec('
            CREATE TABLE IF NOT EXISTS monthly_sort_orders (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                table_name VARCHAR(60) NOT NULL,
                item_id BIGINT UNSIGNED NOT NULL,
                reference_month CHAR(7) NOT NULL,
                sort_order INT UNSIGNED NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_monthly_sort_order (user_id, table_name, item_id, reference_month),
                INDEX idx_monthly_sort_lookup (user_id, table_name, reference_month, sort_order),
                CONSTRAINT fk_monthly_sort_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    private function applySortOrder(string $table, int $id, int $sortOrder, string $month): void
    {
        $row = $this->find($table, $id);
        if (!$row) {
            return;
        }

        $stmt = Database::connection()->prepare(
            'INSERT INTO monthly_sort_orders (user_id, table_name, item_id, reference_month, sort_order)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order), updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([$this->userId, $table, $id, $month, $sortOrder]);
    }

    private function withCardPurchaseDisplayTitle(array $purchase): array
    {
        $number = max(1, (int) ($purchase['installment_number'] ?? 1));
        $total = max(1, (int) ($purchase['installment_total'] ?? 1));
        $title = trim((string) ($purchase['title'] ?? ''));

        $purchase['display_title'] = $title;
        if ($total > 1 && !preg_match('/\b\d+\s*\/\s*\d+\b$/', $title)) {
            $purchase['display_title'] = trim($title . ' ' . $number . '/' . $total);
        }

        return $purchase;
    }

    private function normalizeCardPurchaseTitle(string $title, int $installmentTotal): string
    {
        $title = trim($title);
        if ($installmentTotal > 1) {
            $title = trim((string) preg_replace('/\s+\d+\s*\/\s*\d+\s*$/', '', $title));
        }

        return $title;
    }

    private function monthOffset(string $month, int $offset): string
    {
        $date = \DateTime::createFromFormat('Y-m-d', $month . '-01');
        if (!$date) {
            return $month;
        }

        $date->modify(sprintf('%+d months', $offset));
        return $date->format('Y-m');
    }

    private function dateOffset(?string $date, int $offset): ?string
    {
        if (!$date) {
            return null;
        }

        $reference = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$reference) {
            return null;
        }

        $reference->modify(sprintf('%+d months', $offset));
        return $reference->format('Y-m-d');
    }

    private function ensureMonthlyBillsMonthColumn(): void
    {
        try {
            Database::connection()->exec('ALTER TABLE monthly_bills ADD COLUMN reference_month CHAR(7) NULL AFTER user_id');
        } catch (\Throwable $exception) {
            // Column already exists.
        }

        Database::connection()->exec("UPDATE monthly_bills SET reference_month = DATE_FORMAT(CURRENT_DATE, '%Y-%m') WHERE reference_month IS NULL");
    }

    private function ensureMonthlyBillsPaymentOffsetColumn(): void
    {
        try {
            Database::connection()->exec('ALTER TABLE monthly_bills ADD COLUMN payment_month_offset TINYINT NOT NULL DEFAULT 0 AFTER due_day');
        } catch (\Throwable $exception) {
            // Column already exists.
        }
    }

    private function ensureMonthlyBillsAlwaysActive(): void
    {
        Database::connection()->exec('UPDATE monthly_bills SET active = 1 WHERE active <> 1 OR active IS NULL');
    }

    private function createRecurringBillsUntilYearEnd(
        string $startMonth,
        mixed $categoryId,
        string $title,
        float $amount,
        int $dueDay,
        int $paymentMonthOffset,
        ?int $ignoreId = null
    ): void
    {
        $start = \DateTime::createFromFormat('Y-m', $startMonth);
        if (!$start) {
            return;
        }

        $year = (int) $start->format('Y');
        $insertStmt = Database::connection()->prepare(
            'INSERT INTO monthly_bills (user_id, reference_month, category_id, title, amount, due_day, payment_month_offset, active, auto_create)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1)'
        );
        $existsSql = 'SELECT id FROM monthly_bills WHERE user_id = ? AND reference_month = ? AND title = ? AND due_day = ? AND payment_month_offset = ? AND amount = ? AND ';
        $existsSql .= $categoryId === null ? 'category_id IS NULL' : 'category_id = ?';

        for ($monthNumber = (int) $start->format('n') + 1; $monthNumber <= 12; $monthNumber++) {
            $referenceMonth = sprintf('%04d-%02d', $year, $monthNumber);
            $params = [$this->userId, $referenceMonth, $title, $dueDay, $paymentMonthOffset, $amount];
            if ($categoryId !== null) {
                $params[] = $categoryId;
            }

            $existsStmt = Database::connection()->prepare($existsSql);
            $existsStmt->execute($params);
            $existingId = (int) ($existsStmt->fetchColumn() ?: 0);
            if ($existingId !== 0 && ($ignoreId === null || $existingId !== $ignoreId)) {
                continue;
            }

            if ($existingId === 0) {
                $insertStmt->execute([
                    $this->userId,
                    $referenceMonth,
                    $categoryId,
                    $title,
                    $amount,
                    $dueDay,
                    $paymentMonthOffset,
                ]);
            }
        }
    }

    private function sumCardInvoices(string $month): float
    {
        $total = 0.0;
        foreach ($this->creditCardsWithTotals($month) as $card) {
            $total += (float) $card['total'];
        }
        return $total;
    }
}
