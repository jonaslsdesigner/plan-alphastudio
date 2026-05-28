<?php

use App\Core\Auth;
use App\Core\View;
use App\Models\FinanceRepository;

session_start();

require __DIR__ . '/../app/Core/helpers.php';
require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/Auth.php';
require __DIR__ . '/../app/Core/View.php';
require __DIR__ . '/../app/Models/FinanceRepository.php';

$config = require __DIR__ . '/../app/config.php';
date_default_timezone_set($config['app']['timezone']);
verify_csrf();

$path = current_path();
$method = $_SERVER['REQUEST_METHOD'];
$requestedMonth = $_GET['month'] ?? null;
if (is_string($requestedMonth) && preg_match('/^\d{4}-\d{2}$/', $requestedMonth)) {
    $_SESSION['selected_month'] = $requestedMonth;
}
$month = $_SESSION['selected_month'] ?? date('Y-m');

if ($path === '/login' && $method === 'GET') {
    View::render('auth/login', [
        'title' => 'Entrar',
        'authPageClass' => 'auth-page-login-ref',
        'authShowCornerBrand' => true,
        'authLocaleLabel' => 'PT',
    ], 'guest');
    exit;
}

if ($path === '/login' && $method === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (Auth::attempt($email, $password)) {
        redirect('/');
    }

    View::render('auth/login', [
        'title' => 'Entrar',
        'error' => 'E-mail ou senha invalidos.',
        'old' => ['email' => $email],
        'authPageClass' => 'auth-page-login-ref',
        'authShowCornerBrand' => true,
        'authLocaleLabel' => 'PT',
    ], 'guest');
    exit;
}

if ($path === '/register' && $method === 'GET') {
    View::render('auth/register', [
        'title' => 'Criar conta',
        'authPageClass' => 'auth-page-register',
        'authHeroDescription' => 'Organize sua rotina financeira com clareza, praticidade e controle.',
    ], 'guest');
    exit;
}

if ($path === '/register' && $method === 'POST') {
    $name = trim($_POST['name'] ?? 'Usuario');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $name = $name === '' ? 'Usuario' : $name;

    if (strlen($password) < 6) {
        View::render('auth/register', [
            'title' => 'Criar conta',
            'error' => 'Use uma senha com pelo menos 6 caracteres.',
            'old' => ['name' => $name, 'email' => $email],
            'authPageClass' => 'auth-page-register',
            'authHeroDescription' => 'Organize sua rotina financeira com clareza, praticidade e controle.',
        ], 'guest');
        exit;
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        View::render('auth/register', [
            'title' => 'Criar conta',
            'error' => 'Informe um e-mail valido.',
            'old' => ['name' => $name, 'email' => $email],
            'authPageClass' => 'auth-page-register',
            'authHeroDescription' => 'Organize sua rotina financeira com clareza, praticidade e controle.',
        ], 'guest');
        exit;
    }

    if (Auth::emailExists($email)) {
        View::render('auth/register', [
            'title' => 'Criar conta',
            'error' => 'Este e-mail ja esta cadastrado. Tente entrar na sua conta.',
            'old' => ['name' => $name, 'email' => $email],
            'authPageClass' => 'auth-page-register',
            'authHeroDescription' => 'Organize sua rotina financeira com clareza, praticidade e controle.',
        ], 'guest');
        exit;
    }

    Auth::register($name, $email, $password);
    redirect('/');
}

if ($path === '/logout') {
    Auth::logout();
    redirect('/login');
}

Auth::requireLogin();
$user = Auth::user();
$repo = new FinanceRepository((int) $user['id']);

if ($method === 'POST') {
    match ($path) {
        '/salary' => $repo->updateSalary($_POST),
        '/income' => $repo->saveIncomeSource($_POST),
        '/transactions' => $repo->saveTransaction($_POST),
        '/categories' => $repo->saveCategory($_POST),
        '/accounts' => $repo->saveAccount($_POST),
        '/bills' => $repo->saveBill($_POST),
        '/cards' => $repo->saveCreditCard($_POST),
        '/card-purchases' => $repo->saveCardPurchase($_POST),
        '/commitments' => $repo->saveCommitment($_POST),
        '/goals' => $repo->saveGoal($_POST),
        '/settings' => $repo->updateSettings($_POST),
        '/roadmap/status' => $repo->updateRoadmapStatus($_POST),
        default => null,
    };

    if ($path === '/transactions/update') {
        $repo->updateTransaction((int) $_POST['id'], $_POST);
    }

    if ($path === '/categories/update') {
        $repo->updateCategory((int) $_POST['id'], $_POST);
    }

    if ($path === '/accounts/update') {
        $repo->updateAccount((int) $_POST['id'], $_POST);
    }

    if ($path === '/bills/update') {
        $repo->updateBill((int) $_POST['id'], $_POST);
    }

    if ($path === '/income/update') {
        $repo->updateIncomeSource((int) $_POST['id'], $_POST);
    }

    if ($path === '/cards/update') {
        $repo->updateCreditCard((int) $_POST['id'], $_POST);
    }

    if ($path === '/card-purchases/update') {
        $repo->updateCardPurchase((int) $_POST['id'], $_POST);
    }

    if ($path === '/commitments/update') {
        $repo->updateCommitment((int) $_POST['id'], $_POST);
    }

    if ($path === '/goals/update') {
        $repo->updateGoal((int) $_POST['id'], $_POST);
    }

    if ($path === '/reorder') {
        $repo->reorderItems((string) ($_POST['table'] ?? ''), $_POST['ids'] ?? [], (string) ($_POST['month'] ?? $month));
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            exit;
        }
    }

    if ($path === '/delete') {
        if (($_POST['table'] ?? '') === 'card_purchase_series') {
            $repo->deleteCardPurchaseSeries((int) ($_POST['id'] ?? 0));
        } elseif (($_POST['table'] ?? '') === 'monthly_bill_series') {
            $repo->deleteBillSeries((int) ($_POST['id'] ?? 0));
        } else {
            $repo->delete($_POST['table'] ?? '', (int) ($_POST['id'] ?? 0));
        }
    }

    redirect($_POST['_back'] ?? '/');
}

$cards = $path === '/cards' ? $repo->creditCardsWithTotals($month) : [];
$selectedCardId = isset($_GET['selected_card']) ? (int) $_GET['selected_card'] : 0;
if ($path === '/cards' && !$selectedCardId && $cards) {
    $selectedCardId = (int) $cards[0]['id'];
}
if ($path === '/cards' && $selectedCardId && !array_filter($cards, fn ($card) => (int) $card['id'] === $selectedCardId)) {
    $selectedCardId = $cards ? (int) $cards[0]['id'] : 0;
}

$shared = [
    'title' => 'Alpha Planilhas',
    'user' => $repo->user(),
    'month' => $month,
    'categories' => $repo->categories($month),
    'accounts' => $repo->accounts($month),
];

match ($path) {
    '/' => View::render('dashboard/index', $shared + ['dashboard' => $repo->dashboard($month)]),
    '/income' => View::render('income/index', $shared + [
        'incomeSources' => $repo->incomeSources($month),
        'edit' => isset($_GET['edit']) ? $repo->find('income_sources', (int) $_GET['edit']) : null,
    ]),
    '/roadmap' => View::render('roadmap/index', $shared + [
        'roadmap' => $repo->roadmap($month),
    ]),
    '/transactions' => View::render('transactions/index', $shared + [
        'transactions' => $repo->transactions($month),
        'edit' => isset($_GET['edit']) ? $repo->findTransaction((int) $_GET['edit']) : null,
    ]),
    '/categories' => View::render('categories/index', $shared + ['edit' => isset($_GET['edit']) ? $repo->find('categories', (int) $_GET['edit']) : null]),
    '/accounts' => View::render('accounts/index', $shared + [
        'edit' => isset($_GET['edit']) ? $repo->find('accounts', (int) $_GET['edit']) : null,
        'editCategory' => isset($_GET['edit_category']) ? $repo->find('categories', (int) $_GET['edit_category']) : null,
    ]),
    '/bills' => View::render('bills/index', $shared + [
        'bills' => $repo->monthlyBills($month),
        'edit' => isset($_GET['edit']) ? $repo->find('monthly_bills', (int) $_GET['edit']) : null,
        'editCategory' => isset($_GET['edit_category']) ? $repo->find('categories', (int) $_GET['edit_category']) : null,
    ]),
    '/cards' => View::render('cards/index', $shared + [
        'cards' => $cards,
        'selectedCardId' => $selectedCardId,
        'purchases' => $repo->cardPurchases($month, $selectedCardId ?: null),
        'editCard' => isset($_GET['edit_card']) ? $repo->find('credit_cards', (int) $_GET['edit_card']) : null,
        'editPurchase' => isset($_GET['edit_responsible']) ? $repo->find('card_purchases', (int) $_GET['edit_responsible']) : null,
    ]),
    '/commitments' => View::render('commitments/index', $shared + [
        'commitments' => $repo->commitments($month),
        'activeCommitments' => $repo->commitmentsForYear((int) substr($month, 0, 4), $month),
        'edit' => isset($_GET['edit']) ? $repo->find('commitments', (int) $_GET['edit']) : null,
    ]),
    '/goals' => View::render('goals/index', $shared + [
        'goals' => $repo->goals(),
        'edit' => isset($_GET['edit']) ? $repo->find('goals', (int) $_GET['edit']) : null,
    ]),
    '/settings' => View::render('settings/index', $shared),
    default => View::render('dashboard/index', $shared + ['dashboard' => $repo->dashboard($month)]),
};
