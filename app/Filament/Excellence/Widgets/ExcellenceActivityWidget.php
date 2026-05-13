<?php

namespace App\Filament\Excellence\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class ExcellenceActivityWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function getTableHeading(): ?string
    {
        return 'سجل المتابعة والنشاط';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Activity::query()
                    ->with('causer')
                    ->whereIn('log_name', [
                        'initiatives',
                        'consultations',
                        'consultation_notes',
                        'initiative_evaluations',
                        'visit_reports',
                        'monthly_reports',
                        'service_evaluations',
                    ])
                    ->latest()
                    ->limit(10)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d h:i A'),

                TextColumn::make('log_name')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'initiatives' => 'مبادرة',
                        'consultations' => 'استشارة',
                        'consultation_notes' => 'رد أو ملاحظة',
                        'initiative_evaluations' => 'تقييم مبادرة',
                        'visit_reports' => 'تقرير زيارة',
                        'monthly_reports' => 'تقرير شهري',
                        'service_evaluations' => 'تقييم خدمة',
                        default => $state,
                    }),

                TextColumn::make('description')
                    ->label('النشاط')
                    ->wrap(),

                TextColumn::make('causer.name')
                    ->label('المستخدم')
                    ->placeholder('-'),
            ]);
    }
}
