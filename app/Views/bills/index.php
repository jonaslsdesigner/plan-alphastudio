<?php
$pageTitle = 'Contas';
$mobileAction = $_GET['add'] ?? '';
$showBillFormMobile = $edit || $mobileAction === 'bill';
$showCategoryFormMobile = $editCategory || $mobileAction === 'category';
$showListMobile = !$showBillFormMobile && !$showCategoryFormMobile;
$billDueMonth = $edit ? date('Y-m', strtotime($month . '-01 +' . (int) ($edit['payment_month_offset'] ?? 0) . ' months')) : $month;
$billDueDate = $billDueMonth . '-' . str_pad((string) (int) ($edit['due_day'] ?? 10), 2, '0', STR_PAD_LEFT);
$billDateLabel = static function (array $bill) use ($month): string {
    $date = date('Y-m', strtotime($month . '-01 +' . (int) ($bill['payment_month_offset'] ?? 0) . ' months'))
        . '-' . str_pad((string) (int) ($bill['due_day'] ?? 1), 2, '0', STR_PAD_LEFT);
    return date('d/m', strtotime($date));
};
?>
<section class="grid gap-4 xl:grid-cols-[22rem_1fr]">
    <div class="app-mobile-form-stack <?= !$showListMobile ? 'is-mobile-active' : '' ?> space-y-4">
        <form method="post" action="<?= url($edit ? '/bills/update' : '/bills') ?>" class="app-panel app-mobile-form <?= $showBillFormMobile ? 'is-mobile-active' : '' ?> space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="month" value="<?= e($month) ?>">
            <input type="hidden" name="_back" value="/bills?month=<?= e($month) ?>">
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $edit['id'] ?>"><?php endif; ?>
            <h2 class="text-lg font-black"><?= $edit ? 'Editar conta' : 'Nova conta/custo' ?></h2>
            <label class="app-label">Titulo
                <input class="app-input" name="title" required placeholder="Aluguel, energia, internet" value="<?= e($edit['title'] ?? '') ?>">
            </label>
            <label class="app-label">Categoria
                <select class="app-input" name="category_id">
                    <option value="">Sem categoria</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= (int) ($edit['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="grid grid-cols-2 gap-2">
                <label class="app-label">Valor
                    <input class="app-input" name="amount" inputmode="decimal" required value="<?= e($edit['amount'] ?? '') ?>">
                </label>
                <label class="app-label">Vencimento
                    <input class="app-input" type="date" name="due_date" value="<?= e($billDueDate) ?>" required>
                </label>
            </div>
            <label class="flex items-center gap-2 text-sm font-bold"><input type="checkbox" name="auto_create" <?= (int) ($edit['auto_create'] ?? 1) === 1 ? 'checked' : '' ?>> Conta recorrente</label>
            <p class="text-xs font-semibold text-[#77798a]">Toda conta/custo adicionada aqui entra automaticamente no dashboard do mês.</p>
            <button class="app-button w-full"><?= $edit ? 'Salvar conta' : 'Adicionar conta' ?></button>
            <a class="app-mobile-back block text-center text-xs font-bold text-[#77798a]" href="<?= url('/bills') ?>?month=<?= e($month) ?>">Voltar para contas</a>
        </form>

        <form method="post" action="<?= url($editCategory ? '/categories/update' : '/categories') ?>" class="app-panel app-mobile-form <?= $showCategoryFormMobile ? 'is-mobile-active' : '' ?> space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="_back" value="/bills?month=<?= e($month) ?>">
            <?php if ($editCategory): ?><input type="hidden" name="id" value="<?= (int) $editCategory['id'] ?>"><?php endif; ?>
            <h2 class="text-lg font-black"><?= $editCategory ? 'Editar categoria' : 'Nova categoria' ?></h2>
            <label class="app-label">Nome
                <input class="app-input" name="name" required placeholder="Moradia, transporte, alimentacao" value="<?= e($editCategory['name'] ?? '') ?>">
            </label>
            <label class="app-label">Tipo
                <select class="app-input" name="type">
                    <option value="expense" <?= ($editCategory['type'] ?? 'expense') === 'expense' ? 'selected' : '' ?>>Gasto</option>
                    <option value="income" <?= ($editCategory['type'] ?? '') === 'income' ? 'selected' : '' ?>>Ganho</option>
                </select>
            </label>
            <label class="app-label">Cor
                <input class="app-input h-12" type="color" name="color" value="<?= e($editCategory['color'] ?? '#9b57e3') ?>">
            </label>
            <label class="app-label">Icone
                <input class="app-input" name="icon" value="<?= e($editCategory['icon'] ?? 'tag') ?>">
            </label>
            <button class="app-button w-full"><?= $editCategory ? 'Salvar categoria' : 'Adicionar categoria' ?></button>
            <?php if ($editCategory): ?><a class="block text-center text-xs font-bold text-[#77798a]" href="<?= url('/bills') ?>?month=<?= e($month) ?>">Cancelar edicao</a><?php endif; ?>
            <?php if (!$editCategory): ?><a class="app-mobile-back block text-center text-xs font-bold text-[#77798a]" href="<?= url('/bills') ?>?month=<?= e($month) ?>">Voltar para contas</a><?php endif; ?>
        </form>
    </div>

    <div class="app-mobile-list <?= $showListMobile ? 'is-mobile-active' : '' ?> space-y-4">
        <section class="app-panel">
            <h2 class="mb-4 text-lg font-black">Custos organizados</h2>
            <div class="overflow-hidden rounded-2xl border border-slate-100">
                <table class="w-full text-left text-sm">
                    <thead class="bg-indigo-600 text-white">
                        <tr><th class="p-3"></th><th class="p-3">Conta</th><th class="p-3">Dia</th><th class="p-3 text-right">Valor</th><th class="p-3"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white" data-sortable-list data-sortable-table="monthly_bills" data-reorder-url="<?= url('/reorder') ?>" data-csrf="<?= e(csrf_token()) ?>">
                    <?php foreach ($bills as $bill): ?>
                        <tr class="app-bill-item" data-sortable-id="<?= (int) $bill['id'] ?>">
                            <td class="app-bill-item-handle p-3"><button class="sort-handle" type="button" data-sort-handle aria-label="Mover conta"></button></td>
                            <td class="app-bill-item-copy p-3 font-black">
                                <?= e($bill['title']) ?>
                                <p class="text-[11px] font-bold text-slate-400">
                                    <?= e($bill['category'] ?? 'Sem categoria') ?>
                                    <span class="app-bill-item-day-inline"> - <?= e($billDateLabel($bill)) ?></span>
                                </p>
                            </td>
                            <td class="app-bill-item-day p-3 font-bold"><?= e($billDateLabel($bill)) ?></td>
                            <td class="app-bill-item-side p-3 text-right" colspan="2">
                                <div class="app-bill-item-actions-row flex justify-end gap-2">
                                    <p class="app-bill-item-amount font-black"><?= money_br($bill['amount']) ?></p>
                                    <a class="icon-button" href="<?= url('/bills') ?>?month=<?= e($month) ?>&edit=<?= (int) $bill['id'] ?>"><?= edit_icon() ?></a>
                                    <form method="post" action="<?= url('/delete') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_back" value="/bills?month=<?= e($month) ?>">
                                        <input type="hidden" name="table" value="monthly_bills">
                                        <input type="hidden" name="id" value="<?= (int) $bill['id'] ?>">
                                        <button class="icon-button danger">x</button>
                                    </form>
                                    <?php if ((int) ($bill['auto_create'] ?? 0) === 1): ?>
                                        <form method="post" action="<?= url('/delete') ?>" data-confirm="Excluir toda esta recorrencia, incluindo meses anteriores e futuros?">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_back" value="/bills?month=<?= e($month) ?>">
                                            <input type="hidden" name="table" value="monthly_bill_series">
                                            <input type="hidden" name="id" value="<?= (int) $bill['id'] ?>">
                                            <button class="icon-button danger series-delete-button" title="Excluir recorrencia inteira">TODOS</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="app-panel">
            <h2 class="mb-4 text-lg font-black">Categorias</h2>
            <div class="grid gap-2 sm:grid-cols-2" data-sortable-list data-sortable-table="categories" data-reorder-url="<?= url('/reorder') ?>" data-csrf="<?= e(csrf_token()) ?>">
                <?php foreach ($categories as $category): ?>
                    <article class="app-category-item flex items-center justify-between gap-3 rounded-2xl bg-slate-50 p-3" data-sortable-id="<?= (int) $category['id'] ?>">
                        <button class="sort-handle" type="button" data-sort-handle aria-label="Mover categoria"></button>
                        <div class="app-category-item-content flex items-center gap-3">
                            <span class="h-4 w-4 rounded-full" style="background: <?= e($category['color']) ?>"></span>
                            <div class="app-category-item-text">
                                <p class="text-sm font-black"><?= e($category['name']) ?></p>
                                <p class="text-[11px] font-bold text-slate-400"><?= e($category['type'] === 'income' ? 'Ganho' : 'Gasto') ?></p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a class="icon-button" href="<?= url('/bills') ?>?month=<?= e($month) ?>&edit_category=<?= (int) $category['id'] ?>"><?= edit_icon() ?></a>
                            <form method="post" action="<?= url('/delete') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_back" value="/bills?month=<?= e($month) ?>">
                                <input type="hidden" name="table" value="categories">
                                <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                                <button class="icon-button danger">x</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if (!$categories): ?><p class="text-sm font-bold text-[#77798a]">Nenhuma categoria cadastrada.</p><?php endif; ?>
            </div>
        </section>

        <div class="app-mobile-actions">
            <a class="app-button w-full text-center" href="<?= url('/bills') ?>?month=<?= e($month) ?>&add=bill">Adicionar conta</a>
            <a class="app-button w-full text-center" href="<?= url('/bills') ?>?month=<?= e($month) ?>&add=category">Adicionar categoria</a>
        </div>
    </div>
</section>
