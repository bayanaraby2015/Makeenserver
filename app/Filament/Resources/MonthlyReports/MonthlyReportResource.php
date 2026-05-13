<?php

namespace App\Filament\Resources\MonthlyReports;

use App\Filament\Resources\MonthlyReports\Pages\ListMonthlyReports;
use App\Filament\Resources\MonthlyReports\Pages\ViewMonthlyReport;
use App\Models\MonthlyReport;
use App\Support\AttachmentLinks;
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

class MonthlyReportResource extends Resource
{
    protected static ?string $model = MonthlyReport::class;

    protected static ?string $slug = 'monthly-reports';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static ?int $navigationSort = 50;

    public static function getNavigationLabel(): string
    {
        return 'التقارير الشهرية';
    }

    public static function getModelLabel(): string
    {
        return 'تقرير شهري';
    }

    public static function getPluralModelLabel(): string
    {
        return 'التقارير الشهرية';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات التقرير')
                ->columns(2)
                ->schema([
                    TextEntry::make('initiative.name_ar')->label('المبادرة'),
                    TextEntry::make('organization.name_ar')->label('الجهة')->placeholder('-'),
                    TextEntry::make('consultant.name')->label('المستشار')->placeholder('-'),
                    TextEntry::make('report_month')->label('شهر التقرير')->date('Y-m'),
                    TextEntry::make('status')->label('الحالة')->badge(),
                    TextEntry::make('submitted_at')->label('تاريخ الإرسال')->dateTime('Y-m-d h:i A')->placeholder('-'),
                    TextEntry::make('reviewed_at')->label('تاريخ المراجعة')->dateTime('Y-m-d h:i A')->placeholder('-'),
                ]),
            Section::make('محتوى التقرير')
                ->schema([
                    TextEntry::make('executive_summary')->label('الملخص التنفيذي')->html()->columnSpanFull(),
                    TextEntry::make('progress_summary')->label('ملخص تقدم الإنجاز')->html()->placeholder('-')->columnSpanFull(),
                    TextEntry::make('risks_summary')->label('المخاطر والتحديات')->html()->placeholder('-')->columnSpanFull(),
                    TextEntry::make('questions_summary')->label('ملخص الأسئلة والاستفسارات')->html()->placeholder('-')->columnSpanFull(),
                    TextEntry::make('recommendations')->label('التوصيات')->html()->placeholder('-')->columnSpanFull(),
                    TextEntry::make('attachments')
                        ->label('المرفقات')
                        ->formatStateUsing(fn (mixed $state) => AttachmentLinks::render($state))
                        ->html()
                        ->placeholder('-')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('report_month', 'desc')
            ->columns([
                TextColumn::make('initiative.name_ar')->label('المبادرة')->searchable()->wrap(),
                TextColumn::make('organization.name_ar')->label('الجهة')->searchable()->placeholder('-'),
                TextColumn::make('consultant.name')->label('المستشار')->searchable()->placeholder('-'),
                TextColumn::make('report_month')->label('الشهر')->date('Y-m')->sortable(),
                TextColumn::make('status')->label('الحالة')->badge(),
                TextColumn::make('updated_at')->label('آخر تحديث')->since(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['initiative', 'organization', 'consultant']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMonthlyReports::route('/'),
            'view' => ViewMonthlyReport::route('/{record}'),
        ];
    }
}
