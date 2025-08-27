<?php
// File: app/Filament/Resources/UserResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    // Menggunakan model User untuk mengelola data
    protected static ?string $model = User::class;

    // Mengatur ikon navigasi di sidebar
    protected static ?string $navigationIcon = 'heroicon-o-users';

    // Mengatur label navigasi yang akan muncul di sidebar
    protected static ?string $navigationLabel = 'Pengelolaan Pengguna';

    // Mengatur urutan navigasi di sidebar
    protected static ?int $navigationSort = 1;

    /**
     * Memodifikasi query untuk menyembunyikan pengguna dengan role 'admin'.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', '!=', 'admin');
    }

    /**
     * Mendefinisikan form untuk membuat dan mengedit pengguna.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun')
                    ->description('Isi data untuk membuat akun pengguna baru.')
                    ->schema([
                        // Kolom untuk Nama Lengkap
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        
                        // Kolom untuk Email
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        // Kolom dropdown untuk memilih Role dengan label yang baru
                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options([
                                'employee' => 'Karyawan',
                                'intern' => 'Magang/Pkl',
                            ])
                            ->required()
                            ->default('employee'),

                        // Kolom untuk Password. Hanya wajib saat membuat pengguna baru
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->rule(Password::min(8)->mixedCase()->numbers())
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                        
                        // Kolom untuk Konfirmasi Password
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->same('password')
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(false),
                    ])
            ]);
    }

    /**
     * Mendefinisikan tabel untuk menampilkan daftar pengguna.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Kolom Nama Lengkap
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),
                
                // Kolom Email
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                
                // Kolom Role yang ditampilkan sebagai Badge
                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->colors([
                        'success' => 'employee',
                        'info' => 'intern',
                        'danger' => 'admin',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'employee' => 'Karyawan',
                        'intern' => 'Magang/Pkl',
                        'admin' => 'Admin',
                        'user' => 'User',
                        default => $state,
                    }),
            ])
            ->filters([
                // Filter untuk menyortir berdasarkan Role
                Tables\Filters\SelectFilter::make('role')
                    ->label('Filter Berdasarkan Role')
                    ->options([
                        'employee' => 'Karyawan',
                        'intern' => 'Magang/Pkl',
                    ]),
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

    /**
     * Mendefinisikan halaman-halaman yang terkait dengan resource ini.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
