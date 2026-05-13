<?php

namespace App\Filament\Resources\ServiceEvaluations;

use App\Filament\Resources\ServiceEvaluations\Pages\ListServiceEvaluations;
use App\Filament\Resources\ServiceEvaluations\Pages\ViewServiceEvaluation;
use App\Models\ServiceEvaluation;
use App\Support\ServiceEvaluationOptions;
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

class ServiceEvaluationResource extends Resource
{
    protected static ?string $model = ServiceEvaluation::class;

    protected static ?string $slug = 'service-evaluations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 55;

    public static function getNavigationLabel(): string
    {
        return 'تقييم الخدمة';
    }

    public static function getModelLabel(): string
    {
        return 'تقييم خدمة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'تقييمات الخدمة';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات التقييم')
                ->columns(2)
                ->schema([
                    TextEntry::make('service_type')
                        ->label('نوع الخدمة')
                        ->formatStateUsing(fn (?string $state): string => ServiceEvaluationOptions::serviceTypeLabel($state))
                        ->badge(),
                    TextEntry::make('service_id')->label('رقم الخدمة')->placeholder('-'),
                    TextEntry::make('rating')
                        ->label('التقييم')
                        ->formatStateUsing(fn (mixed $state): string => ServiceEvaluationOptions::ratingLabel($state))
                        ->badge(),
                    TextEntry::make('evaluated_at')->label('تاريخ التقييم')->dateTime('Y-m-d h:i A')->placeholder('-'),
                    TextEntry::make('organization.name_ar')->label('الجهة')->placeholder('-'),
                    TextEntry::make('evaluator.name')->label('المقيّم')->placeholder('-'),
                ]),
            Section::make('الملاحظات')
                ->schema([
                    TextEntry::make('comments')->label('ملاحظات التقييم')->html()->placeholder('-')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('service_type')
                    ->label('نوع الخدمة')
                    ->formatStateUsing(fn (?string $state): string => ServiceEvaluationOptions::serviceTypeLabel($state))
                    ->badge(),
                TextColumn::make('service_id')->label('رقم الخدمة')->placeholder('-')->sortable(),
                TextColumn::make('organization.name_ar')->label('الجهة')->searchable()->placeholder('-'),
                TextColumn::make('evaluator.name')->label('المقيّم')->searchable()->placeholder('-'),
                TextColumn::make('rating')
                    ->label('التقييم')
                    ->formatStateUsing(fn (mixed $state): string => ServiceEvaluationOptions::ratingLabel($state))
                    ->badge()
                    ->sortable(),
                TextColumn::make('evaluated_at')->label('تاريخ التقييم')->dateTime('Y-m-d h:i A')->placeholder('-')->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['evaluator', 'organization']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceEvaluations::route('/'),
            'view' => ViewServiceEvaluation::route('/{record}'),
        ];
    }
}
