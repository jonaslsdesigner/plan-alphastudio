<?php
$pageTitle = 'Roteiro do Mes';
$items = $roadmap['items'] ?? [];
$summary = $roadmap['summary'] ?? [];
$statusLabels = [
    'pending' => 'Pendente',
    'paid' => 'Pago',
    'received' => 'Recebido',
];
?>
<section class="space-y-4">
    <section class="app-panel roadmap-hero">
        <div class="roadmap-hero-copy">
            <p class="text-xs font-semibold uppercase text-[#77798a]"><?= e(month_label($month)) ?></p>
            <h2 class="text-2xl font-bold">Roteiro do Mes</h2>
            <p class="text-sm font-semibold text-[#77798a]">Recebimentos, contas, faturas, compromissos e gastos diarios em ordem de data.</p>
        </div>
        <div class="roadmap-summary-grid">
            <article>
                <span>Saldo real</span>
                <strong><?= money_br($summary['real_balance'] ?? 0) ?></strong>
            </article>
            <article>
                <span>Projetado</span>
                <strong><?= money_br($summary['projected_balance'] ?? 0) ?></strong>
            </article>
            <article>
                <span>Reservar ate proxima entrada</span>
                <strong><?= money_br($summary['reserved_until_next_income'] ?? 0) ?></strong>
            </article>
            <article>
                <span>Disponivel ate la</span>
                <strong><?= money_br($summary['available_until_next_income'] ?? 0) ?></strong>
            </article>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[18rem_1fr]">
        <aside class="app-panel roadmap-side">
            <h3 class="text-lg font-bold">Resumo</h3>
            <div class="space-y-2">
                <div class="roadmap-mini-row"><span>A receber</span><strong class="text-emerald-600"><?= money_br($summary['planned_income'] ?? 0) ?></strong></div>
                <div class="roadmap-mini-row"><span>A pagar</span><strong class="text-[#d64554]"><?= money_br($summary['planned_expense'] ?? 0) ?></strong></div>
                <div class="roadmap-mini-row"><span>Recebido</span><strong class="text-emerald-600"><?= money_br($summary['received'] ?? 0) ?></strong></div>
                <div class="roadmap-mini-row"><span>Pago</span><strong class="text-[#d64554]"><?= money_br($summary['paid'] ?? 0) ?></strong></div>
            </div>
            <?php if (!empty($summary['next_income_date'])): ?>
                <p class="mt-4 rounded-[.8rem] bg-[#f7f7f2] p-3 text-xs font-medium text-[#77798a]">Proxima entrada prevista em <?= date('d/m', strtotime($summary['next_income_date'])) ?>.</p>
            <?php endif; ?>
        </aside>

        <section class="app-panel">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-lg font-bold">Ordem do mes</h3>
                <a class="app-mini-button text-center" href="<?= url('/transactions') ?>?month=<?= e($month) ?>">Adicionar gasto diario</a>
            </div>

            <div class="roadmap-list">
                <?php foreach ($items as $item): ?>
                    <?php
                    $isIncome = $item['direction'] === 'income';
                    $isDone = !empty($item['is_realized']);
                    $doneStatus = $isIncome ? 'received' : 'paid';
                    $pendingStatus = 'pending';
                    $isMonthTurn = substr((string) $item['date'], 0, 7) !== $month;
                    ?>
                    <article class="roadmap-item <?= $isIncome ? 'is-income' : 'is-expense' ?> <?= $isDone ? 'is-done' : '' ?>">
                        <time datetime="<?= e($item['date']) ?>">
                            <strong><?= date('d', strtotime($item['date'])) ?></strong>
                            <span><?= date('m', strtotime($item['date'])) ?></span>
                        </time>
                        <div class="roadmap-item-copy">
                            <p><?= e($item['title']) ?></p>
                            <span><?= e($item['category']) ?><?= $isMonthTurn ? ' - Virada do mes' : '' ?> - <?= e($statusLabels[$item['status']] ?? 'Pendente') ?></span>
                        </div>
                        <div class="roadmap-item-side">
                            <strong class="<?= $isIncome ? 'text-emerald-600' : 'text-[#d64554]' ?>"><?= ($isIncome ? '+' : '-') . ' ' . money_br($item['actual_amount'] ?? $item['amount']) ?></strong>
                            <small><?= money_br($item['running_balance']) ?></small>
                        </div>
                        <div class="roadmap-item-actions">
                            <?php if ($item['item_type'] !== 'transaction'): ?>
                                <form method="post" action="<?= url('/roadmap/status') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_back" value="/roadmap?month=<?= e($month) ?>">
                                    <input type="hidden" name="month" value="<?= e($month) ?>">
                                    <input type="hidden" name="item_type" value="<?= e($item['item_type']) ?>">
                                    <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
                                    <input type="hidden" name="status" value="<?= e($isDone ? $pendingStatus : $doneStatus) ?>">
                                    <button class="roadmap-status-button" type="submit"><?= $isDone ? 'Reabrir' : ($isIncome ? 'Recebi' : 'Paguei') ?></button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="<?= url('/roadmap/status') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_back" value="/roadmap?month=<?= e($month) ?>">
                                    <input type="hidden" name="month" value="<?= e($month) ?>">
                                    <input type="hidden" name="item_type" value="transaction">
                                    <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
                                    <input type="hidden" name="status" value="<?= $isDone ? 'pending' : 'paid' ?>">
                                    <button class="roadmap-status-button" type="submit"><?= $isDone ? 'Reabrir' : 'Paguei' ?></button>
                                </form>
                            <?php endif; ?>
                            <a class="icon-button" href="<?= url($item['source_url']) . (str_contains($item['source_url'], '?') ? '&' : '?') . 'month=' . urlencode($month) ?>"><?= edit_icon() ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if (!$items): ?><p class="rounded-[1.05rem] bg-[#f7f7f2] p-4 text-sm font-medium text-[#77798a]">Sem itens no roteiro deste mes.</p><?php endif; ?>
            </div>
        </section>
    </section>
</section>
