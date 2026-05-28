<?php $pageTitle = 'Dashboard'; ?>
<section class="dashboard-home grid gap-4 xl:grid-cols-[1fr_21rem]">
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <?php
            $cards = [
                ['Rendas do mês', $dashboard['income'], 'app-accent-card', 'Entradas dinâmicas', '/income'],
                ['Contas', $dashboard['billsTotal'], 'bg-white', 'Custos fixos', '/bills'],
                ['Cartões de Crédito', $dashboard['cardsTotal'], 'bg-white', 'Responsáveis', '/cards'],
                ['Sobra prevista', $dashboard['remaining'], $dashboard['remaining'] >= 0 ? 'bg-[#f2ffd0]' : 'bg-[#fff0ee]', 'Depois dos custos', null],
            ];
            ?>
            <?php foreach ($cards as [$label, $value, $class, $hint, $href]): ?>
                <?php if ($href): ?>
                    <a href="<?= url($href) ?>?month=<?= e($month) ?>" class="app-card-link" aria-label="<?= e($label) ?>">
                <?php endif; ?>
                <article class="app-card dashboard-metric-card <?= $class ?> p-4">
                    <p class="text-xs font-bold opacity-60"><?= e($label) ?></p>
                    <p class="mt-5 text-2xl font-black tracking-normal"><?= money_br($value) ?></p>
                    <p class="mt-1 text-[11px] font-bold opacity-50"><?= e($hint) ?></p>
                </article>
                <?php if ($href): ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <section class="app-panel">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black">Resumo do mês</h2>
                    <p class="text-xs font-semibold text-[#77798a]"><?= e(month_label($month)) ?></p>
                </div>
                <a class="app-mini-button" href="<?= url('/income') ?>?month=<?= e($month) ?>">Rendas</a>
            </div>
            <div class="space-y-3">
                <?php foreach (array_filter(array_slice($dashboard['byCategory'], 0, 7), fn ($row) => (float) $row['total'] > 0) as $row): ?>
                    <?php $percent = $dashboard['expenses'] > 0 ? min(100, ((float) $row['total'] / $dashboard['expenses']) * 100) : 0; ?>
                    <div>
                        <div class="mb-1 flex justify-between text-sm font-bold">
                            <span><?= e($row['name']) ?></span>
                            <span><?= money_br($row['total']) ?></span>
                        </div>
                        <div class="h-2 rounded-full bg-[#eeeeE8]">
                            <div class="h-2 rounded-full" style="width: <?= $percent ?>%; background: <?= e($row['color'] ?: '#4f46e5') ?>"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="app-panel">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-black">Movimentos recentes</h2>
                <a class="app-mini-button" href="<?= url('/cards') ?>?month=<?= e($month) ?>">Cartões</a>
            </div>
            <div class="space-y-2">
                <?php foreach ($dashboard['latest'] as $transaction): ?>
                    <div class="flex items-center justify-between rounded-[1.05rem] bg-[#f7f7f2] p-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black"><?= e($transaction['title']) ?></p>
                            <p class="text-[11px] font-bold text-[#77798a]"><?= e($transaction['category'] ?? 'Sem categoria') ?> - <?= date('d/m', strtotime($transaction['due_date'])) ?></p>
                        </div>
                        <p class="shrink-0 text-sm font-black <?= $transaction['type'] === 'income' ? 'text-emerald-600' : 'text-[#24589b]' ?>">
                            <?= money_br($transaction['amount']) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
                <?php if (!$dashboard['latest']): ?><p class="rounded-[1.05rem] bg-[#f7f7f2] p-4 text-sm font-bold text-[#77798a]">Sem rendas extras ou responsáveis no cartão neste mês.</p><?php endif; ?>
            </div>
        </section>
    </div>

    <aside class="space-y-4">
        <section class="app-panel">
            <h2 class="text-lg font-black">Contas do mês</h2>
            <div class="mt-3 space-y-2">
                <?php foreach ($dashboard['bills'] as $bill): ?>
                    <div class="flex items-center justify-between rounded-[1.05rem] border border-[#ecece4] bg-white p-3">
                        <div>
                            <p class="text-sm font-black"><?= e($bill['title']) ?></p>
                            <p class="text-[11px] font-bold text-[#77798a]">Dia <?= (int) $bill['due_day'] ?></p>
                        </div>
                        <p class="text-sm font-black"><?= money_br($bill['amount']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="app-panel app-themed-panel">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-black">Compromissos do ano</h2>
                <span class="app-themed-pill app-pill app-value-pill"><?= money_br($dashboard['commitmentsTotal']) ?></span>
            </div>
            <div class="mt-3 space-y-3">
                <?php foreach ($dashboard['commitments'] as $commitment): ?>
                    <div class="rounded-[1.05rem] bg-white/8 p-3">
                        <div class="flex justify-between gap-2 text-sm font-bold">
                            <span><?= e($commitment['title']) ?></span>
                            <span><?= money_br($commitment['amount']) ?></span>
                        </div>
                        <p class="mt-1 text-[11px] font-bold text-white/50">Ano <?= (int) $commitment['start_year'] ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (!$dashboard['commitments']): ?><p class="text-sm font-semibold text-white/60">Sem compromissos ativos neste ano.</p><?php endif; ?>
            </div>
        </section>
    </aside>
</section>

