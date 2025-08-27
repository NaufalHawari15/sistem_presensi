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
use Filament\Forms\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Izin / Sakit / Lainnya';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Nama Karyawan / Siswa Magang')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->native(false)
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->native(false)
                    ->required(),

                Forms\Components\Select::make('leave_type')
                    ->label('Jenis Pengajuan')
                    ->options([
                        'Sakit' => 'Sakit',
                        'Izin' => 'Izin',
                        'Lainnya' => 'Lainnya',
                    ])
                    ->required()
                    ->live(),

                Forms\Components\Textarea::make('reason')
                    ->label('Alasan')
                    ->required()
                    ->visible(fn (Get $get): bool => in_array($get('leave_type'), ['Sakit', 'Izin'])),

                Forms\Components\TextInput::make('other_reason')
                    ->label('Sebutkan Alasan Lainnya')
                    ->required()
                    ->visible(fn (Get $get): bool => $get('leave_type') === 'Lainnya'),

                Forms\Components\FileUpload::make('attachment')
                   ->label('Lampiran (Opsional)')
                    ->disk('public')
                    ->directory('leave-attachments')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                    ->maxSize(2048)
           
                    ->downloadable()
                    ->openable()    
                    ->previewable(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Nama Pengaju')->searchable()->sortable(),
                TextColumn::make('leave_type')->label('Jenis Pengajuan'),
                TextColumn::make('start_date')->label('Tanggal Mulai')->date('d M Y')->sortable(),
                BadgeColumn::make('status')->label('Status')->colors([
                    'warning' => 'Pending',
                    'success' => 'Approved',
                    'danger' => 'Rejected',
                ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'Pending' => 'Menunggu',
                    'Approved' => 'Disetujui',
                    'Rejected' => 'Ditolak',
                ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('approve')
                        ->label('Setujui')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Leave $record) => $record->update(['status' => 'Approved']))
                        ->requiresConfirmation()
                        ->visible(fn (Leave $record) => $record->status === 'Pending'),
                    Tables\Actions\Action::make('reject')
                        ->label('Tolak')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn (Leave $record) => $record->update(['status' => 'Rejected']))
                        ->requiresConfirmation()
                        ->visible(fn (Leave $record) => $record->status === 'Pending'),
                ]),
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
            'index' => Pages\ListLeaves::route('/'),
            'create' => Pages\CreateLeave::route('/create'),
            'edit' => Pages\EditLeave::route('/{record}/edit'),
            'view' => Pages\ViewLeave::route('/{record}'),
        ];
    }
}
