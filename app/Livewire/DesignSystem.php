<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DesignSystem extends Component
{
    public string $title = '';

    public function mount(): void
    {
        $this->title = __('Design System');
    }

    public function render(): View
    {
        return view('livewire.design-system');
    }
}
