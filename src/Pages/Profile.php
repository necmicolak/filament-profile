<?php

namespace RyanChandler\FilamentProfile\Pages;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Hesap';

    protected static ?string $title = 'Hesap';

    protected static string $view = 'filament-profile::filament.pages.profile';

    public $name;

    public $email;

    public $current_password;

    public $new_password;

    public $new_password_confirmation;

    public function mount()
    {
        $this->form->fill([
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
        ]);
    }

    public function submit()
    {
        $this->form->getState();

        $state = array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->new_password ? Hash::make($this->new_password) : null,
        ]);

        $user = auth()->user();

        $user->update($state);

        if ($this->new_password) {
            $this->updateSessionPassword($user);
        }

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->notify('success', 'Profiliniz güncellendi.');
    }

    protected function updateSessionPassword($user)
    {
        request()->session()->put([
            'password_hash_' . auth()->getDefaultDriver() => $user->getAuthPassword(),
        ]);
    }

    public function getCancelButtonUrlProperty()
    {
        return static::getUrl();
    }

    protected function getBreadcrumbs(): array
    {
        return [
            url()->current() => 'Profil',
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Genel')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->label('Ad Soyad')
                        ->required(),
                    TextInput::make('email')
                        ->label('Email')
                        ->required(),
                ]),
            Section::make('Şifre Değiştir')
                ->columns(2)
                ->schema([
                    TextInput::make('current_password')
                        ->label('Şuanki Şifre')
                        ->password()
                        ->rules(['required_with:new_password'])
                        ->currentPassword()
                        ->autocomplete('off')
                        ->columnSpan(1),
                    Grid::make()
                        ->schema([
                            TextInput::make('new_password')
                                ->label('Yeni Şifre')
                                ->password()
                                ->rules(['confirmed'])
                                ->autocomplete('new-password'),
                            TextInput::make('new_password_confirmation')
                                ->label('Yeni Şifre Onayla')
                                ->password()
                                ->rules([
                                    'required_with:new_password',
                                ])
                                ->autocomplete('new-password'),
                        ]),
                ]),
        ];
    }
}
