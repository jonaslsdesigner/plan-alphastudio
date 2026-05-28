<?php
$pageTitle = 'Contas';
$editAccount = $edit ?? null;
?>
<section class="grid gap-4 xl:grid-cols-[22rem_1fr]">
    <div class="space-y-4">
        <form method="post" action="<?= url($editAccount ? '/accounts/update' : '/accounts') ?>" class="app-panel space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="_back" value="/accounts">
            <?php if ($editAccount): ?><input type="hidden" name="id" value="<?= (int) $editAccount['id'] ?>"><?php endif; ?>
            <h2 class="text-lg font-black"><?= $editAccount ? 'Editar conta' : 'Nova conta' ?></h2>
            <label class="app-label">Nome
                <input class="app-input" name="name" required value="<?= e($editAccount['name'] ?? '') ?>">
            </label>
            <label class="app-label">Tipo
                <select class="app-input" name="type">
                    <option value="checking" <?= ($editAccount['type'] ?? '') === 'checking' ? 'selected' : '' ?>>Conta corrente</option>
                    <option value="cash" <?= ($editAccount['type'] ?? '') === 'cash' ? 'selected' : '' ?>>Dinheiro</option>
                    <option value="credit" <?= ($editAccount['type'] ?? '') === 'credit' ? 'selected' : '' ?>>Cartão</option>
                    <option value="saving" <?= ($editAccount['type'] ?? '') === 'saving' ? 'selected' : '' ?>>Caixinha</option>
                </select>
            </label>
            <label class="app-label">Saldo inicial
                <input class="app-input" name="balance" inputmode="decimal" value="<?= e($editAccount['balance'] ?? '0') ?>">
            </label>
            <button class="app-button w-full"><?= $editAccount ? 'Salvar conta' : 'Adicionar conta' ?></button>
        </form>

        <form method="post" action="<?= url($editCategory ? '/categories/update' : '/categories') ?>" class="app-panel space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="_back" value="/accounts">
            <?php if ($editCategory): ?><input type="hidden" name="id" value="<?= (int) $editCategory['id'] ?>"><?php endif; ?>
            <h2 class="text-lg font-black"><?= $editCategory ? 'Editar categoria' : 'Nova categoria' ?></h2>
            <label class="app-label">Nome
                <input class="app-input" name="name" required value="<?= e($editCategory['name'] ?? '') ?>">
            </label>
            <label class="app-label">Tipo
                <select class="app-input" name="type">
                    <option value="expense" <?= ($editCategory['type'] ?? '') === 'expense' ? 'selected' : '' ?>>Gasto</option>
                    <option value="income" <?= ($editCategory['type'] ?? '') === 'income' ? 'selected' : '' ?>>Ganho</option>
                </select>
            </label>
            <label class="app-label">Cor
                <input class="app-input h-12" type="color" name="color" value="<?= e($editCategory['color'] ?? '#9b57e3') ?>">
            </label>
            <label class="app-label">Ícone
                <input class="app-input" name="icon" value="<?= e($editCategory['icon'] ?? 'tag') ?>">
            </label>
            <button class="app-button w-full"><?= $editCategory ? 'Salvar categoria' : 'Adicionar categoria' ?></button>
            <?php if ($editCategory): ?><a class="block text-center text-xs font-bold text-[#77798a]" href="<?= url('/accounts') ?>">Cancelar edição</a><?php endif; ?>
        </form>
    </div>

    <div class="space-y-4">
        <section class="app-panel">
            <h2 class="mb-4 text-lg font-black">Contas cadastradas</h2>
            <div class="grid gap-2 sm:grid-cols-2" data-sortable-list data-sortable-table="accounts" data-reorder-url="<?= url('/reorder') ?>" data-csrf="<?= e(csrf_token()) ?>">
                <?php foreach ($accounts as $account): ?>
                    <article class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 p-3" data-sortable-id="<?= (int) $account['id'] ?>">
                        <button class="sort-handle" type="button" data-sort-handle aria-label="Mover conta cadastrada"></button>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-black"><?= e($account['name']) ?></p>
                            <p class="text-[11px] font-bold text-slate-400"><?= e($account['type']) ?> - <?= money_br($account['balance']) ?></p>
                        </div>
                        <div class="flex gap-2">
                            <a class="icon-button" href="<?= url('/accounts') ?>?edit=<?= (int) $account['id'] ?>"><?= edit_icon() ?></a>
                            <form method="post" action="<?= url('/delete') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_back" value="/accounts">
                                <input type="hidden" name="table" value="accounts">
                                <input type="hidden" name="id" value="<?= (int) $account['id'] ?>">
                                <button class="icon-button danger">x</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if (!$accounts): ?><p class="text-sm font-bold text-[#77798a]">Nenhuma conta cadastrada.</p><?php endif; ?>
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
                            <a class="icon-button" href="<?= url('/accounts') ?>?edit_category=<?= (int) $category['id'] ?>"><?= edit_icon() ?></a>
                            <form method="post" action="<?= url('/delete') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_back" value="/accounts">
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
    </div>
</section>
