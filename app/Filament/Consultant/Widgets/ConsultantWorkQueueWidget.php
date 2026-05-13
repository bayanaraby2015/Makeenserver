<?php

namespace App\Filament\Consultant\Widgets;

use App\Filament\Consultant\Resources\Consultations\ConsultationResource;
use App\Models\Consultation;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ConsultantWorkQueueWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getTableHeading(): ?string
    {
        return 'قائمة عمل المستشار';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Consultation::query()
                    ->with(['requesterOrganization', 'initiative'])
                    ->where('consultant_user_id', Auth::id())
                    ->whereIn('status', ['requested', 'accepted', 'scheduled'])
                    ->latest('updated_at')
                    ->limit(8)
            )
            ->recordUrl(fn (Consultation $record): string => ConsultationResource::getUrl('view', ['record' => $record]))
            ->paginated(false)
            ->columns([
                TextColumn::make('subject')->label('الاستشارة')->wrap(),
                TextColumn::make('requesterOrganization.name_ar')->label('الجهة')->placeholder('-'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('consultations.statuses.'.$state)),
                TextColumn::make('scheduled_at')
                    ->label('موعد الجلسة')
                    ->dateTime('Y-m-d h:i A')
                    ->placeholder('-'),
            ]);
    }
}
