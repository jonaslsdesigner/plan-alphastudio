<?php
$pageTitle = 'Cartões de Crédito';
$selectedCard = null;
foreach ($cards as $card) {
    if ((int) $card['id'] === (int) $selectedCardId) {
        $selectedCard = $card;
        break;
    }
}
$backUrl = '/cards?month=' . urlencode($month) . ($selectedCardId ? '&selected_card=' . (int) $selectedCardId : '');
$formCardId = (int) ($editPurchase['credit_card_id'] ?? $selectedCardId);
$mobileAction = $_GET['add'] ?? '';
$showCardFormMobile = $editCard || $mobileAction === 'card';
$showPurchaseFormMobile = $editPurchase || $mobileAction === 'responsible';
$showListMobile = !$showCardFormMobile && !$showPurchaseFormMobile;
$editInstallmentNumber = max(1, (int) ($editPurchase['installment_number'] ?? 1));
$editInstallmentTotal = max($editInstallmentNumber, (int) ($editPurchase['installment_total'] ?? 1));
$editPurchaseTitle = (string) ($editPurchase['title'] ?? '');
if ($editPurchase && $editInstallmentTotal <= 1 && preg_match('/\b(\d+)\s*\/\s*(\d+)\b$/', (string) ($editPurchase['title'] ?? ''), $matches)) {
    $editInstallmentNumber = max(1, (int) $matches[1]);
    $editInstallmentTotal = max($editInstallmentNumber, (int) $matches[2]);
}
if ($editInstallmentTotal > 1) {
    $editPurchaseTitle = trim((string) preg_replace('/\s+\d+\s*\/\s*\d+\s*$/', '', $editPurchaseTitle));
}
$cardClosingDate = $editCard && !empty($editCard['closing_day']) ? $month . '-' . str_pad((string) (int) $editCard['closing_day'], 2, '0', STR_PAD_LEFT) : '';
$cardDueDate = $editCard && !empty($editCard['due_day']) ? $month . '-' . str_pad((string) (int) $editCard['due_day'], 2, '0', STR_PAD_LEFT) : '';
?>
<section class="grid gap-4 xl:grid-cols-[22rem_1fr]">
    <div class="app-mobile-form-stack <?= !$showListMobile ? 'is-mobile-active' : '' ?> space-y-4">
        <form method="post" action="<?= url($editCard ? '/cards/update' : '/cards') ?>" class="app-panel app-mobile-form <?= $showCardFormMobile ? 'is-mobile-active' : '' ?> space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="month" value="<?= e($month) ?>">
            <input type="hidden" name="_back" value="<?= e($backUrl) ?>">
            <?php if ($editCard): ?><input type="hidden" name="id" value="<?= (int) $editCard['id'] ?>"><?php endif; ?>
            <h2 class="text-lg font-black"><?= $editCard ? 'Editar cartão' : 'Novo cartão' ?></h2>
            <label class="app-label">Nome
                <input class="app-input" name="name" placeholder="Nubank, Inter, Caixa" value="<?= e($editCard['name'] ?? '') ?>" required>
            </label>
            <div class="grid grid-cols-2 gap-2">
                <label class="app-label">Fechamento
                    <input class="app-input" type="date" name="closing_date" value="<?= e($cardClosingDate) ?>">
                </label>
                <label class="app-label">Vencimento
                    <input class="app-input" type="date" name="due_date" value="<?= e($cardDueDate) ?>">
                </label>
            </div>
            <?php
            $editInvoiceAmount = '';
            if ($editCard) {
                $cardInArray = current(array_filter($cards, fn ($c) => (int) $c['id'] === (int) $editCard['id']));
                if ($cardInArray) {
                    $editInvoiceAmount = $cardInArray['invoice_total'] > 0 ? $cardInArray['invoice_total'] : '';
                }
            }
            ?>
            <label class="app-label">Fatura em <?= e(month_label($month)) ?>
                <input class="app-input" name="invoice_amount" inputmode="decimal" placeholder="Opcional. Valor final fechado" value="<?= e($editInvoiceAmount) ?>">
                <span class="mt-1 block text-[10px] font-semibold leading-tight text-[#77798a]">Preencha este campo se quiser fixar o valor real da fatura deste mês, invalidando a soma dos responsáveis.</span>
            </label>
            <label class="app-label">Cor
                <input class="app-input h-12" type="color" name="color" value="<?= e($editCard['color'] ?? '#9b57e3') ?>">
            </label>
            <button class="app-button w-full"><?= $editCard ? 'Salvar cartão' : 'Adicionar cartão' ?></button>
            <?php if ($editCard): ?><a class="block text-center text-xs font-bold text-[#77798a]" href="<?= url($backUrl) ?>">Cancelar edição</a><?php endif; ?>
            <?php if (!$editCard): ?><a class="app-mobile-back block text-center text-xs font-bold text-[#77798a]" href="<?= url($backUrl) ?>">Voltar para cartoes</a><?php endif; ?>
        </form>

        <form method="post" action="<?= url($editPurchase ? '/card-purchases/update' : '/card-purchases') ?>" class="app-panel app-mobile-form <?= $showPurchaseFormMobile ? 'is-mobile-active' : '' ?> space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="month" value="<?= e($month) ?>">
            <input type="hidden" name="_back" value="<?= e($backUrl) ?>">
            <?php if ($editPurchase): ?><input type="hidden" name="id" value="<?= (int) $editPurchase['id'] ?>"><?php endif; ?>
            <h2 class="text-lg font-black"><?= $editPurchase ? 'Editar responsável' : 'Responsável pela fatura' ?></h2>
            <label class="app-label">Cartão
                <select class="app-input" name="credit_card_id" required>
                    <?php foreach ($cards as $card): ?><option value="<?= (int) $card['id'] ?>" <?= $formCardId === (int) $card['id'] ? 'selected' : '' ?>><?= e($card['name']) ?></option><?php endforeach; ?>
                </select>
            </label>
            <label class="app-label">Responsável
                <input class="app-input" name="title" placeholder="Mãe, sogra, sogro, terceiro" value="<?= e($editPurchase['title'] ?? '') ?>" required>
            </label>
            <label class="app-label">Descrição
                <textarea class="app-input min-h-20" name="description" placeholder="Referência do que essa pessoa deve"><?= e($editPurchase['description'] ?? '') ?></textarea>
            </label>
            <div class="grid grid-cols-2 gap-2">
                <label class="app-label">Parcela atual
                    <input class="app-input" type="number" min="1" name="installment_number" value="<?= e((string) $editInstallmentNumber) ?>" required>
                </label>
                <label class="app-label">Total de parcelas
                    <input class="app-input" type="number" min="1" name="installment_total" value="<?= e((string) $editInstallmentTotal) ?>" required>
                </label>
            </div>
            <label class="flex items-center gap-2 text-sm font-bold">
                <input type="checkbox" name="generate_installments" checked>
                <?= $editPurchase ? 'Gerar parcelas faltantes' : 'Replicar parcelas futuras' ?>
            </label>
            <label class="flex items-center gap-2 text-sm font-bold">
                <input type="checkbox" name="generate_previous_installments" checked>
                Replicar parcelas anteriores
            </label>
            <div class="grid grid-cols-2 gap-2">
                <label class="app-label">Valor devido
                    <input class="app-input" name="amount" inputmode="decimal" value="<?= e($editPurchase['amount'] ?? '') ?>" required>
                </label>
                <label class="app-label">Data de referência
                    <input class="app-input" type="date" name="purchase_date" value="<?= e($editPurchase['purchase_date'] ?? '') ?>">
                </label>
            </div>
            <button class="app-button w-full" <?= !$cards ? 'disabled' : '' ?>><?= $editPurchase ? 'Salvar responsável' : 'Adicionar responsável' ?></button>
            <?php if ($editPurchase): ?><a class="block text-center text-xs font-bold text-[#77798a]" href="<?= url($backUrl) ?>">Cancelar edição</a><?php endif; ?>
            <?php if (!$editPurchase): ?><a class="app-mobile-back block text-center text-xs font-bold text-[#77798a]" href="<?= url($backUrl) ?>">Voltar para cartoes</a><?php endif; ?>
        </form>
    </div>

    <div class="app-mobile-list <?= $showListMobile ? 'is-mobile-active' : '' ?> space-y-4">
        <section class="grid gap-3 sm:grid-cols-2">
            <?php foreach ($cards as $card): ?>
                <?php
                $isSelected = (int) $selectedCardId === (int) $card['id'];
                $invoiceTotal = (float) ($card['invoice_total'] ?? 0);
                $responsibleTotal = (float) ($card['responsible_total'] ?? 0);
                $myTotal = (float) ($card['total'] ?? 0);
                $hasInvoiceTotal = $invoiceTotal > 0;
                ?>
                <article class="rounded-lg p-4 text-white shadow-sm" style="background: <?= e($card['color'] ?: '#9b57e3') ?>; <?= $isSelected ? 'box-shadow: 0 0 0 4px rgba(var(--app-accent-rgb), .45);' : '' ?>">
                    <p class="text-sm font-black"><?= e($card['name']) ?></p>
                    <p class="mt-5 text-[10px] font-black uppercase text-white/70"><?= $hasInvoiceTotal ? 'Minha responsabilidade' : 'Total cadastrado' ?></p>
                    <p class="text-2xl font-black"><?= money_br($myTotal) ?></p>
                    <?php if ($hasInvoiceTotal): ?>
                        <div class="mt-3 space-y-1 rounded-md bg-black/10 p-2 text-[11px] font-black">
                            <div class="flex justify-between gap-3"><span>Fatura total</span><span><?= money_br($invoiceTotal) ?></span></div>
                            <div class="flex justify-between gap-3 text-white/80"><span>- Responsáveis</span><span><?= money_br($responsibleTotal) ?></span></div>
                            <div class="flex justify-between gap-3 border-t border-white/20 pt-1"><span>= Minha parte</span><span><?= money_br($myTotal) ?></span></div>
                        </div>
                    <?php endif; ?>
                    <p class="mt-2 text-[11px] font-bold text-white/60">Fecha <?= e($card['closing_day'] ?: '-') ?> | Vence <?= e($card['due_day'] ?: '-') ?></p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a class="app-action-button" style="<?= $isSelected ? 'background: var(--app-accent); color: #fff;' : 'background: #fff; color: var(--app-blue);' ?>" href="<?= url('/cards') ?>?month=<?= e($month) ?>&selected_card=<?= (int) $card['id'] ?>"><?= $isSelected ? 'SELECIONADO' : 'SELECIONAR' ?></a>
                        <a class="app-action-button bg-white/15 text-white" href="<?= url('/cards') ?>?month=<?= e($month) ?>&selected_card=<?= (int) $card['id'] ?>&edit_card=<?= (int) $card['id'] ?>">Editar</a>
                        <form method="post" action="<?= url('/delete') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_back" value="/cards?month=<?= e($month) ?>">
                            <input type="hidden" name="table" value="credit_cards">
                            <input type="hidden" name="id" value="<?= (int) $card['id'] ?>">
                            <button class="app-action-button bg-white/15 text-white">Excluir</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (!$cards): ?><p class="app-panel text-sm font-bold text-[#77798a]">Cadastre seu primeiro cartão.</p><?php endif; ?>
        </section>

        <section class="app-panel">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-lg font-black">Responsáveis de <?= e(month_label($month)) ?></h2>
                <?php if ($selectedCard): ?><span class="app-pill text-white" style="background: <?= e($selectedCard['color'] ?: '#9b57e3') ?>"><?= e($selectedCard['name']) ?></span><?php endif; ?>
            </div>
            <?php if ($selectedCard): ?>
                <?php
                $selectedInvoiceTotal = (float) ($selectedCard['invoice_total'] ?? 0);
                $selectedResponsibleTotal = (float) ($selectedCard['responsible_total'] ?? 0);
                $selectedMyTotal = (float) ($selectedCard['total'] ?? 0);
                ?>
                <div class="mb-4 grid gap-2 rounded-[1.05rem] bg-[#f7f7f2] p-3 sm:grid-cols-3">
                    <div>
                        <p class="text-[10px] font-black uppercase text-[#77798a]">Fatura total</p>
                        <p class="text-lg font-black text-[#191929]"><?= money_br($selectedInvoiceTotal ?: $selectedMyTotal) ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-[#77798a]">- Responsáveis</p>
                        <p class="text-lg font-black text-[#d64f45]"><?= money_br($selectedResponsibleTotal) ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-[#77798a]">= Minha parte</p>
                        <p class="text-lg font-black text-[#12858f]"><?= money_br($selectedMyTotal) ?></p>
                    </div>
                </div>
            <?php endif; ?>
            <div class="space-y-2" data-sortable-list data-sortable-table="card_purchases" data-reorder-url="<?= url('/reorder') ?>" data-csrf="<?= e(csrf_token()) ?>">
                <?php foreach ($purchases as $purchase): ?>
                    <?php
                    $purchaseTitle = trim((string) ($purchase['display_title'] ?? $purchase['title'] ?? ''));
                    $installmentNumber = max(1, (int) ($purchase['installment_number'] ?? 1));
                    $installmentTotal = max(1, (int) ($purchase['installment_total'] ?? 1));
                    if ($installmentTotal > 1 && !preg_match('/\b\d+\s*\/\s*\d+\b$/', $purchaseTitle)) {
                        $purchaseTitle .= ' ' . $installmentNumber . '/' . $installmentTotal;
                    }
                    ?>
                    <article class="app-card-responsible-item app-sortable-card flex items-center justify-between gap-3 rounded-[1.05rem] bg-[#f7f7f2] p-3" data-sortable-id="<?= (int) $purchase['id'] ?>">
                        <button class="sort-handle" type="button" data-sort-handle aria-label="Mover responsavel"></button>
                        <div class="app-card-responsible-copy app-sortable-card-copy min-w-0 flex-1">
                            <p class="truncate text-sm font-black"><?= e($purchaseTitle) ?></p>
                            <p class="text-[11px] font-bold text-[#77798a]"><?= e($purchase['card_name'] ?? 'Sem cartão') ?><?= $purchase['purchase_date'] ? ' - ' . date('d/m', strtotime($purchase['purchase_date'])) : '' ?></p>
                            <?php if ($purchase['description']): ?><p class="mt-1 text-xs font-semibold text-[#77798a]"><?= e($purchase['description']) ?></p><?php endif; ?>
                        </div>
                        <div class="app-card-responsible-actions app-sortable-card-actions flex shrink-0 items-center gap-2">
                            <p class="app-card-responsible-amount app-sortable-card-amount text-sm font-black"><?= money_br($purchase['amount']) ?></p>
                            <a class="icon-button" href="<?= url('/cards') ?>?month=<?= e($month) ?>&selected_card=<?= (int) $selectedCardId ?>&edit_responsible=<?= (int) $purchase['id'] ?>"><?= edit_icon() ?></a>
                            <form method="post" action="<?= url('/delete') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_back" value="<?= e($backUrl) ?>">
                                <input type="hidden" name="table" value="card_purchases">
                                <input type="hidden" name="id" value="<?= (int) $purchase['id'] ?>">
                                <button class="icon-button danger">x</button>
                            </form>
                            <?php if (!empty($purchase['installment_group'])): ?>
                                <form method="post" action="<?= url('/delete') ?>" data-confirm="Excluir toda esta recorrencia, incluindo meses anteriores e futuros?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_back" value="<?= e($backUrl) ?>">
                                    <input type="hidden" name="table" value="card_purchase_series">
                                    <input type="hidden" name="id" value="<?= (int) $purchase['id'] ?>">
                                    <button class="icon-button danger series-delete-button" title="Excluir recorrencia inteira">TODOS</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if (!$purchases): ?><p class="rounded-[1.05rem] bg-[#f7f7f2] p-4 text-sm font-bold text-[#77798a]"><?= $selectedCard ? 'Sem responsáveis cadastrados para este cartão neste mês.' : 'Cadastre um cartão para organizar os responsáveis.' ?></p><?php endif; ?>
            </div>
        </section>

        <div class="app-mobile-actions">
            <a class="app-button w-full text-center" href="<?= url('/cards') ?>?month=<?= e($month) ?>&selected_card=<?= (int) $selectedCardId ?>&add=card">Adicionar cartao</a>
            <a class="app-button w-full text-center" href="<?= url('/cards') ?>?month=<?= e($month) ?>&selected_card=<?= (int) $selectedCardId ?>&add=responsible">Adicionar responsavel</a>
        </div>
    </div>
</section>
