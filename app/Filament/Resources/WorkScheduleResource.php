<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkScheduleResource\Pages;
use App\Models\WorkSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkScheduleResource extends Resource
{
    protected static ?string $model = WorkSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Pengelolaan Jam Kerja';
    protected static ?int $navigationSort = 3; // Atur urutan di sidebar

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Jadwal')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Jam Kantor Normal'),
                Forms\Components\TimePicker::make('start_time')
                    ->label('Jam Masuk')
                    ->required(),
                Forms\Components\TimePicker::make('end_time')
                    ->label('Jam Pulang')
                    ->required(),
                Forms\Components\TextInput::make('late_tolerance_minutes')
                    ->label('Toleransi Keterlambatan (Menit)')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Jadwal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Jam Masuk')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Jam Pulang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('late_tolerance_minutes')
                    ->label('Toleransi (Menit)')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListWorkSchedules::route('/'),
            'create' => Pages\CreateWorkSchedule::route('/create'),
            'edit' => Pages\EditWorkSchedule::route('/{record}/edit'),
        ];
    }    
}

