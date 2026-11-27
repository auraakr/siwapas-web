<?php

namespace App\Filament\Pages;

use App\Domain\Catalog\Models\Facility;
use App\Domain\Content\Models\SiteSetting;
use App\Domain\Content\Services\SiteSettings;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Contact details, visit information, landing page photos and suggested event venues. Empty fields
 * fall back to the values in .env, which the hints show.
 *
 * @property Form $form
 */
class SiteSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Situs Publik';

    protected static ?string $navigationLabel = 'Pengaturan Situs';

    protected static ?string $title = 'Pengaturan Situs';

    protected static ?string $slug = 'pengaturan-situs';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.site-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(SiteSettings $settings): void
    {
        $stored = $settings->stored();
        $admission = $stored[SiteSettings::ADMISSION_KEY] ?? [];

        $this->form->fill([
            'contact' => collect(SiteSettings::CONTACT_FIELDS)
                ->mapWithKeys(fn (string $field): array => [$field => $stored["contact.{$field}"] ?? null])
                ->all(),
            'admission' => ['id' => $admission['id'] ?? null, 'en' => $admission['en'] ?? null],
            'event_venue_facility_ids' => array_map('strval', $settings->eventVenueFacilityIds()),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->model(app(SiteSettings::class)->imagesRecord())
            ->schema([
                Forms\Components\Section::make('Informasi kunjungan')
                    ->description('Tampil di beranda (strip di bawah gambar utama dan bagian Informasi Kunjungan).')
                    ->columns(2)
                    ->schema([
                        $this->contactInput('hours', 'Jam operasional')->placeholder('09.00–23.00 WIB'),
                        Forms\Components\Placeholder::make('admission_hint')
                            ->hiddenLabel()
                            ->content('Info HTM ditulis dalam dua bahasa. Kosongkan untuk memakai teks bawaan.'),
                        Forms\Components\Textarea::make('admission.id')
                            ->label('Info HTM (Indonesia)')
                            ->placeholder((string) config('siwapas.visit.admission.id'))
                            ->rows(2)
                            ->maxLength(300),
                        Forms\Components\Textarea::make('admission.en')
                            ->label('Info HTM (English)')
                            ->placeholder((string) config('siwapas.visit.admission.en'))
                            ->rows(2)
                            ->maxLength(300),
                    ]),
                Forms\Components\Section::make('Kontak')
                    ->columns(2)
                    ->schema([
                        $this->contactInput('whatsapp', 'Nomor WhatsApp pengelola')
                            ->tel()
                            ->regex('/^(\+62|62|0)8[0-9]{7,12}$/')
                            ->validationMessages(['regex' => 'Gunakan format 08xxxxxxxxxx.']),
                        $this->contactInput('phone', 'Telepon')->tel(),
                        $this->contactInput('email', 'Email')->email(),
                        $this->contactInput('maps_url', 'Link Google Maps')->url(),
                        Forms\Components\Textarea::make('contact.address')
                            ->label('Alamat')
                            ->placeholder($this->fallbackHint('address'))
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Media sosial')
                    ->columns(3)
                    ->schema([
                        $this->contactInput('instagram', 'Instagram')->url(),
                        $this->contactInput('facebook', 'Facebook')->url(),
                        $this->contactInput('youtube', 'YouTube')->url(),
                    ]),
                Forms\Components\Section::make('Gambar beranda')
                    ->description('JPG, PNG, atau WebP, maksimal 5 MB. Tanpa gambar, beranda memakai latar hijau bawaan.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('hero_image')
                            ->label('Gambar utama (hero)')
                            ->collection(SiteSetting::HERO_IMAGE)
                            ->image()
                            ->imageEditor()
                            ->maxSize(5120),
                        Forms\Components\SpatieMediaLibraryFileUpload::make('about_image')
                            ->label('Gambar bagian Tentang')
                            ->collection(SiteSetting::ABOUT_IMAGE)
                            ->image()
                            ->imageEditor()
                            ->maxSize(5120),
                    ]),
                Forms\Components\Section::make('Acara sendiri')
                    ->description('Fasilitas yang disarankan di bagian "Ingin Mengadakan Acara Sendiri?" pada beranda dan halaman Event.')
                    ->schema([
                        Forms\Components\CheckboxList::make('event_venue_facility_ids')
                            ->label('Fasilitas untuk acara')
                            ->options(fn (): array => Facility::query()
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->get()
                                ->mapWithKeys(fn (Facility $facility): array => [(string) $facility->id => $facility->name])
                                ->all())
                            ->helperText('Belum ada pilihan? Tambahkan fasilitasnya dulu di Layanan → Fasilitas Sewa.')
                            ->columns(3),
                    ]),
            ]);
    }

    public function save(SiteSettings $settings): void
    {
        $state = $this->form->getState();

        $settings->save([
            'contact' => (array) ($state['contact'] ?? []),
            'admission' => (array) ($state['admission'] ?? []),
            'event_venue_facility_ids' => (array) ($state['event_venue_facility_ids'] ?? []),
        ]);

        $this->form->model($settings->imagesRecord())->saveRelationships();

        Notification::make()->title('Pengaturan situs disimpan.')->success()->send();
    }

    private function contactInput(string $field, string $label): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make("contact.{$field}")
            ->label($label)
            ->placeholder($this->fallbackHint($field))
            ->maxLength(255);
    }

    /**
     * Shows what the site uses while the field is empty.
     */
    private function fallbackHint(string $field): ?string
    {
        $fallback = config("siwapas.contact.{$field}");

        return filled($fallback) ? "Kosong = {$fallback} (dari .env)" : null;
    }
}
