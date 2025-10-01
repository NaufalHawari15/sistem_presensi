<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Mail\AccountActivatedMail;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Pengelolaan Pengguna';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereRaw('LOWER(role) != ?', ['admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Pengguna')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()->required()->unique(ignoreRecord: true)->maxLength(255),
                        
                        Forms\Components\Select::make('department_id')->label('Departemen')
                            ->relationship('department', 'name')
                            ->searchable()->preload()->required(),
                        Forms\Components\Select::make('position_id')->label('Jabatan')
                            ->relationship('position', 'name')
                            ->searchable()->preload()->required(),
                        Forms\Components\Select::make('office_id')->label('Kantor Penempatan')
                            ->relationship('office', 'name')
                            ->searchable()->preload()->required(),

                        Forms\Components\Select::make('role')->label('Role')
                            ->options([
                                'employee' => 'Karyawan',
                                'intern' => 'Magang/Pkl',
                            ])
                            ->required(),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Akun Aktif')
                            ->helperText('Matikan toggle ini untuk menonaktifkan akun pengguna.')
                            ->onColor('success')
                            ->offColor('danger')
                            ->visible(fn (User $record = null): bool => $record && $record->is_active),

                        Forms\Components\TextInput::make('password')->label('Password Baru (Opsional)')
                            ->password()
                            ->rule(Password::min(8))->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Lengkap')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('department.name')->label('Departemen')->placeholder('N/A')->sortable(),
                Tables\Columns\TextColumn::make('position.name')->label('Jabatan')->placeholder('N/A')->sortable(),
                Tables\Columns\TextColumn::make('office.name')->label('Kantor')->placeholder('Belum Ditetapkan')->sortable(),
                Tables\Columns\TextColumn::make('role')->label('Role')
                    ->badge()
                    ->placeholder('N/A')
                    ->color(fn (User $record): string => match((int) $record->is_active) {
                        1 => $record->role === 'employee' ? 'success' : 'info',
                        0 => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(function ($state, User $record): string {
                        $roleText = match ($state) {
                            'employee' => 'Karyawan',
                            'intern' => 'Magang',
                            default => 'Tidak Diketahui',
                        };
                        return $record->is_active ? $roleText : $roleText . ' (Menunggu Persetujuan)';
                    }),
                Tables\Columns\IconColumn::make('is_active')->label('Status Akun')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Akun')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // === FUNGSI AKTIFKAN & NON-AKTIFKAN DIGABUNGKAN ===
                Action::make('toggleStatus')
                    ->label(fn (User $record): string => $record->is_active ? 'Non-aktifkan' : 'Aktifkan')
                    ->icon(fn (User $record): string => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (User $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        // Jika akun aktif, maka non-aktifkan
                        if ($record->is_active) {
                            $record->is_active = false;
                            $record->save();

                            Notification::make()
                                ->success()
                                ->title('Berhasil Dinonaktifkan')
                                ->body('Akun pengguna telah berhasil dinonaktifkan.')
                                ->send();
                        } else {
                            // Jika akun tidak aktif, maka aktifkan dan kirim email
                            $record->is_active = true;
                            $record->save();

                            try {
                                Mail::to($record->email)->send(new AccountActivatedMail($record));
                                Notification::make()
                                    ->success()
                                    ->title('Aktivasi Berhasil')
                                    ->body('Akun pengguna telah diaktifkan dan notifikasi terkirim.')
                                    ->send();
                            } catch (\Exception $e) {
                                Log::error('Gagal kirim email aktivasi dari Filament: ' . $e->getMessage());
                                Notification::make()
                                    ->danger()
                                    ->title('Email Gagal Terkirim')
                                    ->body('Aktivasi berhasil, namun email notifikasi gagal dikirim.')
                                    ->send();
                            }
                        }
                    }),
                // === AKHIR FUNGSI GABUNGAN ===
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

