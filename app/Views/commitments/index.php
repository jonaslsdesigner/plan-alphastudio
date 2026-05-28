<?php
$pageTitle = 'Compromissos';
$currentYear = (int) substr($month, 0, 4);
$mobileAction = $_GET['add'] ?? '';
$showFormMobile = $edit || $mobileAction === 'commitment';
$showListMobile = !$showFormMobile;
?>
<section class="grid gap-4 xl:grid-cols-[22rem_1fr]">
    <form method="post" action="<?= url($edit ? '/commitments/update' : '/commitments') ?>" class="app-panel app-mobile-form <?= $showFormMobile ? 'is-mobile-active' : '' ?> space-y-3">
        <?= csrf_field() ?>
        <input type="hidden" name="_back" value="/commitments?month=<?= e($month) ?>">
        <input type="hidden" name="start_month" value="1">
        <input type="hidden" name="duration_months" value="12">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $edit['id'] ?>"><?php endif; ?>
        <h2 class="text-lg font-black"><?= $edit ? 'Editar compromisso' : 'Novo compromisso' ?></h2>
        <label class="app-label">Título
            <input class="app-input" name="title" placeholder="Licenciamento, IPVA, acordo" value="<?= e($edit['title'] ?? '') ?>" required>
        </label>
        <label class="app-label">Descrição
            <textarea class="app-input min-h-24" name="description" placeholder="Anotações, vencimentos, parcelas ou documentos importantes"><?= e($edit['description'] ?? '') ?></textarea>
        </label>
        <label class="app-label">Valor previsto
            <input class="app-input" name="amount" inputmode="decimal" value="<?= e($edit['amount'] ?? '') ?>" required>
        </label>
        <label class="app-label">Ano
            <input class="app-input" type="number" name="start_year" value="<?= e($edit['start_year'] ?? $currentYear) ?>" required>
        </label>
        <label class="app-label">Status
            <select class="app-input" name="status">
                <option value="active" <?= ($edit['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Ativo</option>
                <option value="done" <?= ($edit['status'] ?? '') === 'done' ? 'selected' : '' ?>>Resolvido</option>
            </select>
        </label>
        <button class="app-button w-full"><?= $edit ? 'Salvar compromisso' : 'Adicionar compromisso' ?></button>
        <?php if ($edit): ?><a class="block text-center text-xs font-bold text-[#77798a]" href="<?= url('/commitments') ?>?month=<?= e($month) ?>">Cancelar edição</a><?php endif; ?>
        <?php if (!$edit): ?><a class="app-mobile-back block text-center text-xs font-bold text-[#77798a]" href="<?= url('/commitments') ?>?month=<?= e($month) ?>">Voltar para compromissos</a><?php endif; ?>
    </form>

    <div class="app-mobile-list <?= $showListMobile ? 'is-mobile-active' : '' ?> space-y-4">
        <section class="app-panel commitment-summary-panel">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-lg font-black">Pendências de <?= e((string) $currentYear) ?></h2>
                    <p class="text-sm font-semibold text-[#77798a]">Área informativa para acompanhar o que precisa resolver no ano.</p>
                </div>
                <span class="app-pill app-money-pill text-white" style="background: var(--app-accent)"><?= money_br(array_sum(array_column($activeCommitments, 'amount'))) ?></span>
            </div>
            <div class="space-y-2" data-sortable-list data-sortable-table="commitments" data-reorder-url="<?= url('/reorder') ?>" data-csrf="<?= e(csrf_token()) ?>">
                <?php foreach ($activeCommitments as $commitment): ?>
                    <article class="flex items-center justify-between gap-3 rounded-[1.05rem] bg-[#f7f7f2] p-3" data-sortable-id="<?= (int) $commitment['id'] ?>">
                        <button class="sort-handle" type="button" data-sort-handle aria-label="Mover compromisso"></button>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-black"><?= e($commitment['title']) ?></p>
                            <p class="text-[11px] font-bold text-[#77798a]">Ano <?= (int) $commitment['start_year'] ?></p>
                            <?php if ($commitment['description']): ?><p class="mt-1 text-xs font-semibold text-[#77798a]"><?= e($commitment['description']) ?></p><?php endif; ?>
                        </div>
                        <p class="shrink-0 whitespace-nowrap text-sm font-black"><?= money_br($commitment['amount']) ?></p>
                    </article>
                <?php endforeach; ?>
                <?php if (!$activeCommitments): ?><p class="text-sm font-bold text-[#77798a]">Nenhuma pendência ativa para este ano.</p><?php endif; ?>
            </div>
        </section>

        <section class="app-panel">
            <h2 class="mb-4 text-lg font-black">Todos os compromissos</h2>
            <div class="space-y-2" data-sortable-list data-sortable-table="commitments" data-reorder-url="<?= url('/reorder') ?>" data-csrf="<?= e(csrf_token()) ?>">
                <?php foreach ($commitments as $commitment): ?>
                    <article class="app-commitment-item app-sortable-card flex items-center justify-between gap-3 rounded-[1.05rem] bg-white p-3" data-sortable-id="<?= (int) $commitment['id'] ?>">
                        <button class="sort-handle" type="button" data-sort-handle aria-label="Mover compromisso"></button>
                        <div class="app-commitment-item-copy app-sortable-card-copy min-w-0 flex-1">
                            <p class="truncate text-sm font-black"><?= e($commitment['title']) ?></p>
                            <p class="text-[11px] font-bold text-[#77798a]">Ano <?= (int) $commitment['start_year'] ?> - <?= $commitment['status'] === 'done' ? 'Resolvido' : 'Ativo' ?></p>
                        </div>
                        <div class="app-commitment-item-actions app-sortable-card-actions flex shrink-0 items-center gap-2">
                            <p class="app-commitment-item-amount app-sortable-card-amount text-sm font-black"><?= money_br($commitment['amount']) ?></p>
                            <a class="icon-button" href="<?= url('/commitments') ?>?month=<?= e($month) ?>&edit=<?= (int) $commitment['id'] ?>"><?= edit_icon() ?></a>
                            <form method="post" action="<?= url('/delete') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_back" value="/commitments?month=<?= e($month) ?>">
                                <input type="hidden" name="table" value="commitments">
                                <input type="hidden" name="id" value="<?= (int) $commitment['id'] ?>">
                                <button class="icon-button danger">x</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <div class="app-mobile-actions">
            <a class="app-button w-full text-center" href="<?= url('/commitments') ?>?month=<?= e($month) ?>&add=commitment">Adicionar compromisso</a>
        </div>
    </div>
</section>

