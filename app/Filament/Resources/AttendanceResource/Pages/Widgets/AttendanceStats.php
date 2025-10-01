<?php

namespace App\Filament\Resources\AttendanceResource\Widgets;

use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AttendanceStats extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();

        // Mengambil data absensi untuk hari ini
        $totalToday = Attendance::whereDate('check_in_time', $today)->count();
        $onTimeToday = Attendance::whereDate('check_in_time', $today)->where('status', 'On Time')->count();
        $lateToday = Attendance::whereDate('check_in_time', $today)->where('status', 'Late')->count();

        return [
            Stat::make('Total Absensi Hari Ini', $totalToday)
                ->description('Jumlah karyawan yang sudah absen masuk')
                ->color('primary'),
            Stat::make('Tepat Waktu Hari Ini', $onTimeToday)
                ->description('Jumlah absensi yang tepat waktu')
                ->color('success'),
            Stat::make('Terlambat Hari Ini', $lateToday)
                ->description('Jumlah absensi yang terlambat')
                ->color('danger'),
        ];
    }
}

