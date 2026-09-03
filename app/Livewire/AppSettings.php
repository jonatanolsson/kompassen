<?php

namespace App\Livewire;

use App\Models\AppSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]

class AppSettings extends Component
{
    use WithFileUploads;

    public ?string $currentLogo = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $logo = null;

    public function mount(): void
    {
        $this->currentLogo = AppSetting::get('brand_logo');
    }

    public function save(): void
    {
        $this->validate([
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($this->logo) {
            if ($this->currentLogo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($this->currentLogo);
            }

            $path = $this->logo->store('brand', 'public');
            AppSetting::set('brand_logo', $path);
            $this->currentLogo = $path;
            $this->logo = null;
        }

        $this->dispatch('toast', message: 'Settings saved.', variant: 'success');
    }

    public function removeLogo(): void
    {
        if ($this->currentLogo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($this->currentLogo);
            AppSetting::set('brand_logo', null);
            $this->currentLogo = null;
        }

        $this->dispatch('toast', message: 'Logo removed.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.app-settings');
    }
}
