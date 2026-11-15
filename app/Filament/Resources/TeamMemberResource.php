<?php

namespace App\Filament\Resources;

use App\Domain\Content\Models\TeamMember;
use App\Filament\Resources\TeamMemberResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeamMemberResource extends Resource
{
    use Translatable;

    protected static ?string $model = TeamMember::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Pengelola & Tim';

    protected static ?string $modelLabel = 'anggota tim';

    protected static ?string $pluralModelLabel = 'pengelola & tim';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Anggota')->columns(2)->schema([
                Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(255),
                Forms\Components\TextInput::make('role')->label('Jabatan')->required()->maxLength(255),
                Forms\Components\SpatieMediaLibraryFileUpload::make('photo')
                    ->label('Foto')
                    ->collection('default')
                    ->image()
                    ->maxSize(3072)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('bio')->label('Keterangan singkat')->rows(4)->columnSpanFull(),
                Forms\Components\TextInput::make('sort_order')->label('Urutan')->numeric()->default(0)->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('photo')->label('Foto')->collection('default')->limit(1)->circular(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('role')->label('Jabatan')->limit(40),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeamMembers::route('/'),
            'create' => Pages\CreateTeamMember::route('/create'),
            'edit' => Pages\EditTeamMember::route('/{record}/edit'),
        ];
    }
}
