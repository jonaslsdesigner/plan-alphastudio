<?php
$pageTitle = 'Rendas';
$mobileAction = $_GET['add'] ?? '';
$showIncomeFormMobile = $edit || $mobileAction === 'income';
$showListMobile = !$showIncomeFormMobile;
$defaultIncomeDate = $month . '-' . str_pad((string) min((int) date('d'), (int) date('t', strtotime($month . '-01'))), 2, '0', STR_PAD_LEFT);
$incomeTypeLabels = [
    'first_half' => 'Quinzena',
    'month_end' => 'Final de Mes',
    'other' => 'Outras Rendas',
];
?>
<section class="grid gap-4 xl:grid-cols-[22rem_1fr]">
    <div class="app-mobile-form-stack <?= !$showListMobile ? 'is-mobile-active' : '' ?> space-y-4">
        <form method="post" action="<?= url($edit ? '/income/update' : '/income') ?>" class="app-panel app-mobile-form <?= $showIncomeFormMobile ? 'is-mobile-active' : '' ?> space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="month" value="<?= e($month) ?>">
            <input type="hidden" name="_back" value="/income?month=<?= e($month) ?>">
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $edit['id'] ?>"><?php endif; ?>
            <h2 class="text-lg font-black"><?= $edit ? 'Editar renda' : 'Nova renda do mes' ?></h2>
            <label class="app-label">Tipo
                <select class="app-input" name="income_type">
                    <?php foreach ($incomeTypeLabels as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= ($edit['income_type'] ?? 'other') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="app-label">Referencia
                <input class="app-input" name="title" placeholder="Quinzena, final do mes, freelance" value="<?= e($edit['title'] ?? '') ?>" required>
            </label>
            <div class="grid grid-cols-2 gap-2">
                <label class="app-label">Valor
                    <input class="app-input" name="amount" inputmode="decimal" value="<?= e($edit['amount'] ?? '') ?>" required>
                </label>
                <label class="app-label">Data
                    <input class="app-input" type="date" name="received_date" value="<?= e($edit['received_date'] ?? $defaultIncomeDate) ?>" required>
                </label>
            </div>
            <label class="app-label">Status
                <select class="app-input" name="status">
                    <option value="pending" <?= ($edit['status'] ?? '') !== 'received' ? 'selected' : '' ?>>Previsto</option>
                    <option value="received" <?= ($edit['status'] ?? '') === 'received' ? 'selected' : '' ?>>Recebido</option>
                </select>
            </label>
            <button class="app-button w-full"><?= $edit ? 'Salvar renda' : 'Adicionar renda' ?></button>
            <?php if ($edit): ?><a class="block text-center text-xs font-bold text-[#77798a]" href="<?= url('/income') ?>?month=<?= e($month) ?>">Cancelar edicao</a><?php endif; ?>
            <?php if (!$edit): ?><a class="app-mobile-back block text-center text-xs font-bold text-[#77798a]" href="<?= url('/income') ?>?month=<?= e($month) ?>">Voltar para rendas</a><?php endif; ?>
        </form>
    </div>

    <section class="app-panel app-mobile-list <?= $showListMobile ? 'is-mobile-active' : '' ?>">
        <div class="app-income-summary mb-4">
            <div class="app-income-summary-copy">
                <h2 class="text-lg font-black">Rendas de <?= e(month_label($month)) ?></h2>
                <p class="text-sm font-semibold text-[#77798a]">Quinzena, final do mes e outras rendas variam mes a mes.</p>
            </div>
            <span class="app-pill app-value-pill app-income-summary-total text-white" style="background: var(--app-blue)"><?= money_br(array_sum(array_column($incomeSources, 'amount'))) ?></span>
        </div>
        <div class="space-y-2" data-sortable-list data-sortable-table="income_sources" data-reorder-url="<?= url('/reorder') ?>" data-csrf="<?= e(csrf_token()) ?>">
            <?php foreach ($incomeSources as $income): ?>
                <article class="app-income-item app-sortable-card flex items-center justify-between gap-3 rounded-[1.05rem] bg-white p-3" data-sortable-id="<?= (int) $income['id'] ?>">
                    <button class="sort-handle" type="button" data-sort-handle aria-label="Mover renda"></button>
                    <div class="app-income-item-copy app-sortable-card-copy min-w-0 flex-1">
                        <p class="truncate text-sm font-black"><?= e($income['title']) ?></p>
                        <p class="text-[11px] font-bold text-[#77798a]"><?= e($incomeTypeLabels[$income['income_type'] ?? 'other'] ?? 'Outras Rendas') ?> - <?= date('d/m', strtotime($income['received_date'] ?? $month . '-01')) ?> - <?= ($income['status'] ?? '') === 'received' ? 'Recebido' : 'Previsto' ?></p>
                    </div>
                    <div class="app-income-item-actions app-sortable-card-actions flex shrink-0 items-center gap-2">
                        <p class="app-income-item-amount app-sortable-card-amount text-sm font-black text-emerald-600"><?= money_br($income['amount']) ?></p>
                        <a class="icon-button" href="<?= url('/income') ?>?month=<?= e($month) ?>&edit=<?= (int) $income['id'] ?>"><?= edit_icon() ?></a>
                        <form method="post" action="<?= url('/delete') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_back" value="/income?month=<?= e($month) ?>">
                            <input type="hidden" name="table" value="income_sources">
                            <input type="hidden" name="id" value="<?= (int) $income['id'] ?>">
                            <button class="icon-button danger">x</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (!$incomeSources): ?><p class="rounded-[1.05rem] bg-[#f7f7f2] p-4 text-sm font-bold text-[#77798a]">Sem rendas cadastradas neste mes.</p><?php endif; ?>
        </div>
        <div class="app-mobile-actions mt-4">
            <a class="app-button w-full text-center" href="<?= url('/income') ?>?month=<?= e($month) ?>&add=income">Adicionar renda</a>
        </div>
    </section>
</section>
