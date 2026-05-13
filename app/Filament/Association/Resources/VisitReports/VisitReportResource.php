<?php

namespace App\Filament\Association\Resources\VisitReports;

use App\Filament\Association\Resources\VisitReports\Pages\ListVisitReports;
use App\Filament\Association\Resources\VisitReports\Pages\ViewVisitReport;
use App\Models\VisitReport;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class VisitReportResource extends Resource
{
    protected static ?string $model = VisitReport::class;

    protected static ?string $slug = 'visit-reports';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 35;

    public static function getNavigationLabel(): string
    {
        return 'مواعيد الزيارات';
    }

    public static function getModelLabel(): string
    {
        return 'موعد زيارة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'مواعيد الزيارات';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('تفاصيل الزيارة')
                ->columns(2)
                ->schema([
                    TextEntry::make('initiative.name_ar')->label('المبادرة'),
                    TextEntry::make('consultant.name')->label('المستشار')->placeholder('-'),
                    TextEntry::make('visit_type')->label('نوع الزيارة')->badge(),
                    TextEntry::make('status')
                        ->label('الحالة')
                        ->formatStateUsing(fn (?string $state): string => [
                            'proposed' => 'بانتظار اختيار موعد',
                            'planned' => 'مجدولة',
                            'completed' => 'منفذة',
                            'cancelled' => 'ملغاة',
                        ][$state] ?? '-')
                        ->badge(),
                    TextEntry::make('scheduled_at')->label('الموعد المعتمد')->dateTime('Y-m-d h:i A')->placeholder('-'),
                    TextEntry::make('selected_at')->label('وقت اختيار الموعد')->dateTime('Y-m-d h:i A')->placeholder('-'),
                    TextEntry::make('pre_visit_notes')->label('ملاحظات ما قبل الزيارة')->html()->placeholder('-')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('initiative.name_ar')->label('المبادرة')->searchable()->wrap(),
                TextColumn::make('consultant.name')->label('المستشار')->placeholder('-'),
                TextColumn::make('visit_type')->label('النوع')->badge(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (?string $state): string => [
                        'proposed' => 'بانتظار اختيار موعد',
                        'planned' => 'مجدولة',
                        'completed' => 'منفذة',
                        'cancelled' => 'ملغاة',
                    ][$state] ?? '-')
                    ->badge(),
                TextColumn::make('scheduled_at')->label('الموعد المعتمد')->dateTime('Y-m-d h:i A')->placeholder('-'),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['initiative', 'organization', 'consultant'])
            ->where('organization_id', Auth::user()?->primary_organization_id);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisitReports::route('/'),
            'view' => ViewVisitReport::route('/{record}'),
        ];
    }
}
