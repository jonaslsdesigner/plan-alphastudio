<?php

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], string $layout = 'layout'): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require __DIR__ . '/../Views/' . $layout . '.php';
    }
}
