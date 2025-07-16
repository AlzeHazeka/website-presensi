<?php

namespace App\Livewire;

use Laravel\Jetstream\Http\Livewire\NavigationMenu;

class AppNavigationMenu extends NavigationMenu
{
    public array $items = [
        'Dashboard' => 'dashboard',
        'Users' => 'user.list', // Pastikan rute ini sudah didefinisikan
        'Profile' => 'profile.show',
        'Settings' => 'settings',
        'Logout' => 'logout',
    ];

    public function render()
    {
        return view('livewire.app-navigation-menu');
    }
}
