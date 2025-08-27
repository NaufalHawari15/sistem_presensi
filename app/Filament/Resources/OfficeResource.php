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

    // Mengatur ikon dan nama di navigasi sidebar
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Pengelolaan Kantor';
    protected static ?int $navigationSort = 1; // Urutan di sidebar

    /**
     * Mendefinisikan form untuk membuat dan mengedit data.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Input untuk nama kantor
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Kantor'),

                // Input untuk alamat
                Forms\Components\Textarea::make('address')
                    ->required()
                    ->label('Alamat Lengkap'),

                // Grup untuk latitude dan longitude agar sejajar
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
                
                // Input untuk radius
                Forms\Components\TextInput::make('radius')
                    ->required()
                    ->numeric()
                    ->default(50)
                    ->suffix('meter') // Tambahan teks 'meter' di belakang input
                    ->label('Radius Toleransi'),
            ]);
    }

    /**
     * Mendefinisikan tabel untuk menampilkan data.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Kolom untuk menampilkan nama kantor
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kantor')
                    ->searchable() // Membuat kolom ini bisa dicari
                    ->sortable(), // Membuat kolom ini bisa diurutkan

                // Kolom untuk menampilkan alamat
                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->limit(50) // Batasi teks yang ditampilkan agar tidak terlalu panjang
                    ->searchable(),

                // Kolom untuk menampilkan radius
                Tables\Columns\TextColumn::make('radius')
                    ->label('Radius')
                    ->suffix(' m') // Tambahkan ' m' di belakang angka
                    ->sortable(),
            ])
            ->filters([
                // Tempat untuk menambahkan filter jika dibutuhkan nanti
            ])
            ->actions([
                Tables\Actions\EditAction::make(), // Tombol untuk mengedit
                Tables\Actions\DeleteAction::make(), // Tombol untuk menghapus
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Tempat untuk relasi jika ada
        ];
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
