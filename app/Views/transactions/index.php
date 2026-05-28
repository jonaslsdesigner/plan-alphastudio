<?php
$pageTitle = 'Lançamentos';
$formAction = $edit ? '/transactions/update' : '/transactions';
$defaultDueDate = $month . '-' . str_pad((string) min((int) date('d'), (int) date('t', strtotime($month . '-01'))), 2, '0', STR_PAD_LEFT);
?>
<section class="grid gap-4 xl:grid-cols-[22rem_1fr]">
    <form method="post" action="<?= url($formAction) ?>" class="app-panel space-y-3">
        <?= csrf_field() ?>
        <input type="hidden" name="_back" value="/transactions?month=<?= e($month) ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $edit['id'] ?>"><?php endif; ?>
        <h2 class="text-lg font-black"><?= $edit ? 'Editar lançamento' : 'Novo lançamento' ?></h2>
        <label class="app-label">Título<input class="app-input" name="title" required value="<?= e($edit['title'] ?? '') ?>"></label>
        <div class="grid grid-cols-2 gap-2">
            <label class="app-label">Tipo
                <select class="app-input" name="type">
                    <option value="expense" <?= ($edit['type'] ?? '') === 'expense' ? 'selected' : '' ?>>Gasto</option>
                    <option value="income" <?= ($edit['type'] ?? '') === 'income' ? 'selected' : '' ?>>Ganho</option>
                </select>
            </label>
            <label class="app-label">Valor<input class="app-input" name="amount" required inputmode="decimal" value="<?= e($edit['amount'] ?? '') ?>"></label>
        </div>
        <label class="app-label">Categoria
            <select class="app-input" name="category_id">
                <option value="">Sem categoria</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= (int) ($edit['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="app-label">Conta
            <select class="app-input" name="account_id">
                <option value="">Sem conta</option>
                <?php foreach ($accounts as $account): ?>
                    <option value="<?= (int) $account['id'] ?>" <?= (int) ($edit['account_id'] ?? 0) === (int) $account['id'] ? 'selected' : '' ?>><?= e($account['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="grid grid-cols-2 gap-2">
            <label class="app-label">Vencimento<input class="app-input" type="date" name="due_date" required value="<?= e($edit['due_date'] ?? $defaultDueDate) ?>"></label>
            <label class="app-label">Status
                <select class="app-input" name="status">
                    <option value="pending" <?= ($edit['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendente</option>
                    <option value="paid" <?= ($edit['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Pago</option>
                </select>
            </label>
        </div>
        <input type="hidden" name="paid_at" value="<?= date('Y-m-d') ?>">
        <label class="app-label">Notas<textarea class="app-input min-h-20" name="notes"><?= e($edit['notes'] ?? '') ?></textarea></label>
        <button class="app-button w-full"><?= $edit ? 'Salvar alterações' : 'Adicionar' ?></button>
    </form>

    <section class="app-panel">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-black">Planilha do mês</h2>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black"><?= e(month_label($month)) ?></span>
        </div>
        <div class="space-y-2">
            <?php foreach ($transactions as $transaction): ?>
                <article class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 p-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-black"><?= e($transaction['title']) ?></p>
                        <p class="text-[11px] font-bold text-slate-400"><?= e($transaction['category'] ?? 'Sem categoria') ?> - <?= date('d/m/Y', strtotime($transaction['due_date'])) ?> - <?= e($transaction['status'] === 'paid' ? 'Pago' : 'Pendente') ?></p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <p class="text-sm font-black <?= $transaction['type'] === 'income' ? 'text-emerald-600' : 'text-slate-950' ?>"><?= money_br($transaction['amount']) ?></p>
                        <a class="icon-button" href="<?= url('/transactions') ?>?month=<?= e($month) ?>&edit=<?= (int) $transaction['id'] ?>"><?= edit_icon() ?></a>
                        <form method="post" action="<?= url('/delete') ?>">
                            <?= csrf_field() ?><input type="hidden" name="_back" value="/transactions?month=<?= e($month) ?>"><input type="hidden" name="table" value="transactions"><input type="hidden" name="id" value="<?= (int) $transaction['id'] ?>">
                            <button class="icon-button danger" title="Excluir">x</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (!$transactions): ?><p class="rounded-2xl bg-slate-50 p-4 text-sm font-bold text-slate-400">Sem lançamentos por enquanto.</p><?php endif; ?>
        </div>
    </section>
</section>

