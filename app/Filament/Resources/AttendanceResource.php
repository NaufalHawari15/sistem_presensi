<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Model;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';
    protected static ?string $navigationLabel = 'Monitoring Absensi';
    protected static ?int $navigationSort = 4;

    // Membuat resource ini tidak bisa dibuat atau diedit dari panel admin
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        // Form tidak terlalu relevan karena data masuk dari API
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Pengguna') // Label diubah menjadi lebih umum
                    ->searchable()
                    ->sortable(),
                TextColumn::make('check_in_time')
                    ->label('Waktu Check-in')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
                TextColumn::make('check_out_time')
                    ->label('Waktu Check-out')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable()
                    ->placeholder('Belum check-out'),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'On Time',
                        'danger' => 'Late',
                        'secondary' => 'Absent',
                    ]),
                ImageColumn::make('check_in_photo')
                    ->label('Foto Check-in')
                    ->disk('public')
                    ->width(80)
                    ->height(80)
                    ->circular(),
                TextColumn::make('office.name')
                    ->label('Lokasi Absen')
                    ->sortable(),
            ])
            ->filters([
                // Filter berdasarkan role pengguna
                Tables\Filters\SelectFilter::make('role')
                    ->label('Filter Role')
                    ->options([
                        'employee' => 'Karyawan',
                        'intern' => 'Siswa Magang',
                    ])
                    ->query(fn ($query, $data) => $query->whereHas('user', function ($q) use ($data) {
                        if (isset($data['value'])) {
                            $q->where('role', $data['value']);
                        }
                    })),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'view' => Pages\ViewAttendance::route('/{record}'),
        ];
    }
}
