<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveResource\Pages;
use App\Models\Leave;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Pengajuan Izin';
    protected static ?string $navigationGroup = 'Manajemen Karyawan';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengajuan')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Nama Karyawan')
                            ->options(User::pluck('name', 'id'))
                            ->disabled(), // hanya view, tidak bisa ubah

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->disabled(),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->disabled(),

                        Forms\Components\Select::make('leave_type')
                            ->label('Jenis Pengajuan')
                            ->disabled(),

                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan Pengajuan')
                            ->rows(3)
                            ->disabled(),

                        Forms\Components\FileUpload::make('attachment')
                            ->label('Lampiran')
                            ->disk('public')
                            ->directory('leave-attachments')
                            ->downloadable()
                            ->openable()
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('Status Pengajuan')
                            ->options([
                                'Pending' => 'Pending',
                                'Approved' => 'Disetujui',
                                'Rejected' => 'Ditolak',
                            ])
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Nama Pengaju')->searchable()->sortable(),
                TextColumn::make('leave_type')->label('Jenis')->badge(),
                TextColumn::make('start_date')->label('Mulai')->date('d M Y'),
                TextColumn::make('end_date')->label('Selesai')->date('d M Y'),
                IconColumn::make('attachment')->label('Lampiran')
                    ->icon('heroicon-o-paper-clip')
                    ->url(fn ($record) => $record->attachment_url, true)
                    ->visible(fn ($record) => !empty($record->attachment)),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'Pending',
                        'success' => 'Approved',
                        'danger' => 'Rejected',
                    ]),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Nama Pangaju')
                    ->options(User::pluck('name', 'id')->toArray())
                    ->searchable(),
                    
                Tables\Filters\SelectFilter::make('leave_type')
                    ->label('Jenis Pengajuan')
                    ->options([
                        'Sakit' => 'Sakit',
                        'Izin' => 'Izin',
                        'Lainnya' => 'Cuti/Lainnya',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Menunggu',
                        'Approved' => 'Disetujui',
                        'Rejected' => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->label('Ubah Status'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaves::route('/'),
            'view' => Pages\ViewLeave::route('/{record}'),
            'edit' => Pages\EditLeave::route('/{record}/edit'),
            // 'create' sengaja dihapus supaya admin tidak bisa tambah manual
        ];
    }
}
