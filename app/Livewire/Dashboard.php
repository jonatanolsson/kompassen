<?php

namespace App\Livewire;

use App\Livewire\Actions\Logout;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        redirect('/');
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
