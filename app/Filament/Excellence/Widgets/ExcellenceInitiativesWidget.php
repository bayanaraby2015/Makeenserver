<?php

namespace App\Filament\Excellence\Widgets;

use App\Filament\Excellence\Resources\Initiatives\InitiativeResource;
use App\Models\Initiative;
use App\Support\DisplayNumber;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ExcellenceInitiativesWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getTableHeading(): ?string
    {
        return 'متابعة المبادرات ذات الأولوية';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Initiative::query()
                    ->with('organization')
                    ->whereIn('status', ['submitted', 'under_review', 'revisions_requested'])
                    ->latest('updated_at')
                    ->limit(8)
            )
            ->recordUrl(fn (Initiative $record): string => InitiativeResource::getUrl('view', ['record' => $record]))
            ->paginated(false)
            ->columns([
                TextColumn::make('name_ar')
                    ->label('المبادرة')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('organization.name_ar')
                    ->label('الجهة')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('initiatives.statuses.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'submitted', 'under_review' => 'warning',
                        'revisions_requested' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('grand_total')
                    ->label('القيمة')
                    ->formatStateUsing(fn (mixed $state) => DisplayNumber::riyalHtml($state))
                    ->html(),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->since(),
            ]);
    }
}
