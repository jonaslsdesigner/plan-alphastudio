<?php $pageTitle = 'Categorias'; ?>
<section class="grid gap-4 lg:grid-cols-[22rem_1fr]">
    <form method="post" action="<?= url($edit ? '/categories/update' : '/categories') ?>" class="app-panel space-y-3">
        <?= csrf_field() ?><input type="hidden" name="_back" value="/categories">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $edit['id'] ?>"><?php endif; ?>
        <h2 class="text-lg font-black"><?= $edit ? 'Editar categoria' : 'Nova categoria' ?></h2>
        <label class="app-label">Nome<input class="app-input" name="name" required value="<?= e($edit['name'] ?? '') ?>"></label>
        <label class="app-label">Tipo<select class="app-input" name="type"><option value="expense" <?= ($edit['type'] ?? '') === 'expense' ? 'selected' : '' ?>>Gasto</option><option value="income" <?= ($edit['type'] ?? '') === 'income' ? 'selected' : '' ?>>Ganho</option></select></label>
        <label class="app-label">Cor<input class="app-input h-12" type="color" name="color" value="<?= e($edit['color'] ?? '#4f46e5') ?>"></label>
        <label class="app-label">Ícone<input class="app-input" name="icon" value="<?= e($edit['icon'] ?? 'tag') ?>"></label>
        <button class="app-button w-full"><?= $edit ? 'Salvar' : 'Adicionar' ?></button>
    </form>
    <div class="app-panel grid gap-2 sm:grid-cols-2">
        <?php foreach ($categories as $category): ?>
            <article class="flex items-center justify-between rounded-2xl bg-slate-50 p-3">
                <div class="flex items-center gap-3"><span class="h-4 w-4 rounded-full" style="background: <?= e($category['color']) ?>"></span><div><p class="text-sm font-black"><?= e($category['name']) ?></p><p class="text-[11px] font-bold text-slate-400"><?= e($category['type']) ?></p></div></div>
                <div class="flex gap-2"><a class="icon-button" href="<?= url('/categories') ?>?edit=<?= (int) $category['id'] ?>"><?= edit_icon() ?></a><form method="post" action="<?= url('/delete') ?>"><?= csrf_field() ?><input type="hidden" name="_back" value="/categories"><input type="hidden" name="table" value="categories"><input type="hidden" name="id" value="<?= (int) $category['id'] ?>"><button class="icon-button danger">x</button></form></div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

