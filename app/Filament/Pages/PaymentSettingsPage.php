<?php

namespace App\Filament\Pages;

use App\Domain\Payment\Services\PaymentSettings;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Settings the managers can change themselves. The down payment percentage is copied onto each new
 * reservation, so changing it never affects orders that already exist.
 *
 * @property Form $form
 */
class PaymentSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pengaturan Pembayaran';

    protected static ?string $title = 'Pengaturan Pembayaran';

    protected static ?string $slug = 'pengaturan-pembayaran';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.payment-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(PaymentSettings $settings): void
    {
        $this->form->fill(['dp_percentage' => $settings->downPaymentPercentage()]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\TextInput::make('dp_percentage')
                    ->label('DP reservasi fasilitas (%)')
                    ->helperText('Persentase harga lunas yang dibayar di muka. Berlaku untuk reservasi baru; reservasi yang sudah ada tidak berubah. Tiket acara selalu dibayar penuh.')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(100)
                    ->suffix('%')
                    ->required(),
            ]);
    }

    public function save(PaymentSettings $settings): void
    {
        $settings->setDownPaymentPercentage((int) $this->form->getState()['dp_percentage']);

        Notification::make()->title('Pengaturan pembayaran disimpan.')->success()->send();
    }
}
