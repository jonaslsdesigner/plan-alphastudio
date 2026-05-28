<?php $pageTitle = 'Caixinhas'; ?>
<section class="grid gap-4 lg:grid-cols-[22rem_1fr]">
    <form method="post" action="<?= url($edit ? '/goals/update' : '/goals') ?>" class="app-panel space-y-3">
        <?= csrf_field() ?><input type="hidden" name="_back" value="/goals">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $edit['id'] ?>"><?php endif; ?>
        <h2 class="text-lg font-black"><?= $edit ? 'Editar caixinha' : 'Nova caixinha' ?></h2>
        <label class="app-label">Nome<input class="app-input" name="name" required placeholder="Reserva, investimento, viagem" value="<?= e($edit['name'] ?? '') ?>"></label>
        <label class="app-label">Meta<input class="app-input" name="target_amount" inputmode="decimal" required value="<?= e($edit['target_amount'] ?? '') ?>"></label>
        <label class="app-label">Guardado agora<input class="app-input" name="current_amount" inputmode="decimal" value="<?= e($edit['current_amount'] ?? '0') ?>"></label>
        <label class="app-label">Data alvo<input class="app-input" type="date" name="due_date" value="<?= e($edit['due_date'] ?? '') ?>"></label>
        <label class="app-label">Cor<input class="app-input h-12" type="color" name="color" value="<?= e($edit['color'] ?? '#2563eb') ?>"></label>
        <button class="app-button w-full"><?= $edit ? 'Salvar' : 'Adicionar' ?></button>
    </form>

    <section class="grid gap-3 sm:grid-cols-2">
        <?php foreach ($goals as $goal): ?>
            <?php $progress = $goal['target_amount'] > 0 ? min(100, ($goal['current_amount'] / $goal['target_amount']) * 100) : 0; ?>
            <article class="app-panel">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-black"><?= e($goal['name']) ?></h2>
                        <p class="text-xs font-bold text-slate-400"><?= money_br($goal['current_amount']) ?> de <?= money_br($goal['target_amount']) ?></p>
                    </div>
                    <div class="flex gap-2"><a class="icon-button" href="<?= url('/goals') ?>?edit=<?= (int) $goal['id'] ?>"><?= edit_icon() ?></a><form method="post" action="<?= url('/delete') ?>"><?= csrf_field() ?><input type="hidden" name="_back" value="/goals"><input type="hidden" name="table" value="goals"><input type="hidden" name="id" value="<?= (int) $goal['id'] ?>"><button class="icon-button danger">x</button></form></div>
                </div>
                <div class="mt-5 h-3 rounded-full bg-slate-100"><div class="h-3 rounded-full" style="width: <?= $progress ?>%; background: <?= e($goal['color']) ?>"></div></div>
                <p class="mt-2 text-right text-xs font-black text-slate-500"><?= number_format($progress, 0) ?>%</p>
            </article>
        <?php endforeach; ?>
        <?php if (!$goals): ?><p class="app-panel text-sm font-bold text-slate-400">Nenhuma caixinha criada.</p><?php endif; ?>
    </section>
</section>
