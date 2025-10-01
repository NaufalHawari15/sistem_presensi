<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeResource\Pages;
use App\Models\Office;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OfficeResource extends Resource
{
    protected static ?string $model = Office::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Pengelolaan Kantor';
    protected static ?int $navigationSort = 2; // Urutan di sidebar

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Kantor'),

                Forms\Components\Textarea::make('address')
                    ->required()
                    ->label('Alamat Lengkap')
                    ->columnSpanFull(),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->required()
                            ->numeric()
                            ->label('Garis Lintang (Latitude)'),
                        Forms\Components\TextInput::make('longitude')
                            ->required()
                            ->numeric()
                            ->label('Garis Bujur (Longitude)'),
                    ]),
                
                Forms\Components\TextInput::make('radius')
                    ->required()
                    ->numeric()
                    ->default(50)
                    ->suffix('meter')
                    ->label('Radius Toleransi'),

                // --- PENAMBAHAN FITUR DIMULAI ---
                Forms\Components\Select::make('work_schedule_id')
                    ->label('Pilih Jam Kerja')
                    ->relationship('workSchedule', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->placeholder('Pilih jadwal kerja untuk kantor ini'),
                // --- PENAMBAHAN FITUR SELESAI ---
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kantor')
                    ->searchable()
                    ->sortable(),
                
                // --- PENAMBAHAN KOLOM BARU ---
                Tables\Columns\TextColumn::make('workSchedule.name')
                    ->label('Jam Kerja')
                    ->badge()
                    ->sortable()
                    ->placeholder('Belum diatur'),

                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('radius')
                    ->label('Radius')
                    ->suffix(' m')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOffices::route('/'),
            'create' => Pages\CreateOffice::route('/create'),
            'edit' => Pages\EditOffice::route('/{record}/edit'),
        ];
    }
}
