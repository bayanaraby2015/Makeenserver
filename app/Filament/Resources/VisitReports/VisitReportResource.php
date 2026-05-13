<?php

namespace App\Filament\Resources\VisitReports;

use App\Filament\Resources\VisitReports\Pages\ListVisitReports;
use App\Filament\Resources\VisitReports\Pages\ViewVisitReport;
use App\Models\VisitReport;
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

class VisitReportResource extends Resource
{
    protected static ?string $model = VisitReport::class;

    protected static ?string $slug = 'visit-reports';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 45;

    public static function getNavigationLabel(): string
    {
        return 'تقارير الزيارات';
    }

    public static function getModelLabel(): string
    {
        return 'تقرير زيارة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'تقارير الزيارات';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('تفاصيل الزيارة')
                ->columns(2)
                ->schema([
                    TextEntry::make('initiative.name_ar')->label('المبادرة'),
                    TextEntry::make('organization.name_ar')->label('الجهة')->placeholder('-'),
                    TextEntry::make('consultant.name')->label('المستشار')->placeholder('-'),
                    TextEntry::make('visit_type')->label('نوع الزيارة')->badge(),
                    TextEntry::make('status')
                        ->label('الحالة')
                        ->formatStateUsing(fn (?string $state): string => [
                            'proposed' => 'بانتظار اختيار الجهة',
                            'planned' => 'مجدولة',
                            'completed' => 'منفذة',
                            'cancelled' => 'ملغاة',
                        ][$state] ?? '-')
                        ->badge(),
                    TextEntry::make('scheduled_at')->label('الموعد المعتمد')->dateTime('Y-m-d h:i A')->placeholder('-'),
                    TextEntry::make('selected_at')->label('وقت اعتماد الموعد')->dateTime('Y-m-d h:i A')->placeholder('-'),
                    TextEntry::make('completed_at')->label('تاريخ التنفيذ')->dateTime('Y-m-d h:i A')->placeholder('-'),
                ]),
            Section::make('محتوى التقرير')
                ->schema([
                    TextEntry::make('pre_visit_notes')->label('تقرير ما قبل الزيارة')->html()->placeholder('-')->columnSpanFull(),
                    TextEntry::make('summary')->label('ملخص الزيارة')->html()->placeholder('-')->columnSpanFull(),
                    TextEntry::make('achievements')->label('أبرز الإنجازات')->html()->placeholder('-')->columnSpanFull(),
                    TextEntry::make('challenges')->label('التحديات')->html()->placeholder('-')->columnSpanFull(),
                    TextEntry::make('recommendations')->label('التوصيات')->html()->placeholder('-')->columnSpanFull(),
                    TextEntry::make('evidence_files')
                        ->label('الشواهد والمرفقات')
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
            ->defaultSort('scheduled_at', 'desc')
            ->columns([
                TextColumn::make('initiative.name_ar')->label('المبادرة')->searchable()->wrap(),
                TextColumn::make('organization.name_ar')->label('الجهة')->searchable()->placeholder('-'),
                TextColumn::make('consultant.name')->label('المستشار')->searchable()->placeholder('-'),
                TextColumn::make('visit_type')->label('النوع')->badge(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (?string $state): string => [
                        'proposed' => 'بانتظار اختيار الجهة',
                        'planned' => 'مجدولة',
                        'completed' => 'منفذة',
                        'cancelled' => 'ملغاة',
                    ][$state] ?? '-')
                    ->badge(),
                TextColumn::make('scheduled_at')->label('الموعد المعتمد')->dateTime('Y-m-d h:i A')->placeholder('-')->sortable(),
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
            'index' => ListVisitReports::route('/'),
            'view' => ViewVisitReport::route('/{record}'),
        ];
    }
}
