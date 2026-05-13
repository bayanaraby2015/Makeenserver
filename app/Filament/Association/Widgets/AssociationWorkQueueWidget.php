<?php

namespace App\Filament\Association\Widgets;

use App\Filament\Association\Resources\Initiatives\InitiativeResource;
use App\Models\Initiative;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AssociationWorkQueueWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getTableHeading(): ?string
    {
        return 'متابعة مبادرات الجهة';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Initiative::query()
                    ->with(['organization', 'evaluations.evaluator'])
                    ->where('organization_id', Auth::user()?->primary_organization_id)
                    ->whereIn('status', ['draft', 'submitted', 'under_review', 'revisions_requested', 'rejected'])
                    ->latest('updated_at')
                    ->limit(8)
            )
            ->recordUrl(fn (Initiative $record): string => InitiativeResource::getUrl('view', ['record' => $record]))
            ->paginated(false)
            ->columns([
                TextColumn::make('name_ar')->label('المبادرة')->wrap(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('initiatives.statuses.'.$state)),
                TextColumn::make('rejection_reason')
                    ->label('ملاحظة المتابعة')
                    ->limit(48)
                    ->placeholder('-'),
                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->since(),
            ]);
    }
}
