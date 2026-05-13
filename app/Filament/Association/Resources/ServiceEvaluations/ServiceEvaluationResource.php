<?php

namespace App\Filament\Association\Resources\ServiceEvaluations;

use App\Filament\Association\Resources\ServiceEvaluations\Pages\CreateServiceEvaluation;
use App\Filament\Association\Resources\ServiceEvaluations\Pages\EditServiceEvaluation;
use App\Filament\Association\Resources\ServiceEvaluations\Pages\ListServiceEvaluations;
use App\Filament\Association\Resources\ServiceEvaluations\Pages\ViewServiceEvaluation;
use App\Models\ServiceEvaluation;
use App\Support\ServiceEvaluationOptions;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ServiceEvaluationResource extends Resource
{
    protected static ?string $model = ServiceEvaluation::class;

    protected static ?string $slug = 'service-evaluations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 45;

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

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات التقييم')
                ->columns(2)
                ->schema([
                    Select::make('service_type')
                        ->label('نوع الخدمة')
                        ->options(ServiceEvaluationOptions::serviceTypes())
                        ->required(),
                    TextInput::make('service_id')
                        ->label('رقم الخدمة أو السجل')
                        ->numeric()
                        ->helperText('اختياري: رقم المبادرة أو الاستشارة أو الزيارة إذا كان التقييم مرتبطاً بسجل محدد.'),
                    Select::make('rating')
                        ->label('التقييم')
                        ->options(ServiceEvaluationOptions::ratings())
                        ->required(),
                    RichEditor::make('comments')
                        ->label('ملاحظات التقييم')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('تقييم الخدمة')
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
                    TextEntry::make('comments')->label('الملاحظات')->html()->placeholder('-')->columnSpanFull(),
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
                TextColumn::make('service_id')->label('رقم الخدمة')->placeholder('-'),
                TextColumn::make('rating')
                    ->label('التقييم')
                    ->formatStateUsing(fn (mixed $state): string => ServiceEvaluationOptions::ratingLabel($state))
                    ->badge(),
                TextColumn::make('created_at')->label('تاريخ الإنشاء')->dateTime('Y-m-d h:i A')->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['evaluator', 'organization'])
            ->where('organization_id', Auth::user()?->primary_organization_id);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceEvaluations::route('/'),
            'create' => CreateServiceEvaluation::route('/create'),
            'view' => ViewServiceEvaluation::route('/{record}'),
            'edit' => EditServiceEvaluation::route('/{record}/edit'),
        ];
    }
}
