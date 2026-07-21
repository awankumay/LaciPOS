<?php

namespace App\Providers;

use Native\Laravel\Facades\Window;
use Native\Laravel\Facades\Menu;
use Native\Laravel\Contracts\ProvidesPhpIni;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    public function boot(): void
    {
        Window::open()
            ->width(1280)
            ->height(800)
            ->minWidth(1024)
            ->minHeight(768)
            ->center()
            ->showDevTools(false)
            ->title('LaciPOS');

        Menu::new()
            ->appMenu()
            ->submenu('File', Menu::new()
                ->submenu('Keluar', 'quit')
            )
            ->submenu('Jendela', Menu::new()
                ->minimize()
                ->zoom()
                ->separator()
                ->close()
            )
            ->register();
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
