<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\Widgets\AttendanceStats;
use App\Models\Attendance;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';
    protected static ?string $navigationLabel = 'Riwayat Absensi';
    protected static ?string $navigationGroup = 'Manajemen Karyawan';
    protected static ?int $navigationSort = 2;

    // Menonaktifkan tombol "Buat Baru" dan "Edit"
    // karena data absensi dibuat dari API dan bersifat historis (tidak untuk diubah).
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('Waktu Masuk')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out_time')
                    ->label('Waktu Pulang')
                    ->dateTime('d M Y, H:i:s')
                    ->placeholder('Belum clock out')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status Masuk')
                    ->colors([
                        'success' => 'On Time',
                        'danger' => 'Late',
                    ]),
            ])
            ->filters([
                // Filter canggih berdasarkan rentang tanggal
                Filter::make('check_in_time')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal')->native(false),
                        DatePicker::make('until')->label('Sampai Tanggal')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date): Builder => $query->whereDate('check_in_time', '>=', $date))
                            ->when($data['until'], fn (Builder $query, $date): Builder => $query->whereDate('check_in_time', '<=', $date));
                    }),
                // Filter untuk memilih karyawan spesifik
                SelectFilter::make('user_id')
                    ->label('Karyawan')
                    ->options(User::pluck('name', 'id')->toArray())
                    ->searchable(),
                // Filter cepat berdasarkan status
                SelectFilter::make('status')
                    ->options([
                        'On Time' => 'On Time',
                        'Late' => 'Late',
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->infolist(self::getInfolistSchema()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('check_in_time', 'desc');
    }
    
    // Schema ini mendefinisikan tampilan detail saat admin mengklik "View"
    public static function getInfolistSchema(): array
    {
        return [
            Components\Section::make('Informasi Absensi')
                ->schema([
                    Components\Grid::make(2)
                        ->schema([
                            Components\TextEntry::make('user.name')->label('Nama Karyawan'),
                            Components\TextEntry::make('office.name')->label('Lokasi Kantor'),
                            Components\TextEntry::make('check_in_time')->label('Waktu Masuk')->dateTime('d M Y, H:i:s'),
                            Components\TextEntry::make('check_out_time')->label('Waktu Pulang')->dateTime('d M Y, H:i:s')->placeholder('Belum clock out'),
                            Components\TextEntry::make('status')->label('Status Masuk')->badge()->colors(['success' => 'On Time', 'danger' => 'Late']),
                        ])
                ]),
            Components\Section::make('Bukti Foto')
                ->schema([
                    Components\Grid::make(2)
                        ->schema([
                            Components\ImageEntry::make('check_in_photo')->label('Foto Masuk')->disk('public')->height(200),
                            Components\ImageEntry::make('check_out_photo')->label('Foto Pulang')->disk('public')->height(200),
                        ])
                ])
        ];
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
        ];
    }

    // Fungsi ini memanggil widget statistik untuk ditampilkan di atas tabel
    public static function getWidgets(): array
    {
        return [
            AttendanceStats::class,
        ];
    }
}

