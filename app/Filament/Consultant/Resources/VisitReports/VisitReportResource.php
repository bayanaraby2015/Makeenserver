<?php

namespace App\Filament\Consultant\Resources\VisitReports;

use App\Filament\Consultant\Resources\VisitReports\Pages\CreateVisitReport;
use App\Filament\Consultant\Resources\VisitReports\Pages\EditVisitReport;
use App\Filament\Consultant\Resources\VisitReports\Pages\ListVisitReports;
use App\Filament\Consultant\Resources\VisitReports\Pages\ViewVisitReport;
use App\Models\VisitReport;
use App\Support\AttachmentLinks;
use App\Support\ConsultantInitiativeScope;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 35;

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

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([
                Step::make('بيانات الزيارة')
                    ->description('اختر المبادرة ونوع الزيارة.')
                    ->columns(2)
                    ->schema([
                    Select::make('initiative_id')
                        ->label('المبادرة')
                        ->options(fn (): array => ConsultantInitiativeScope::queryFor(Auth::user())
                            ->orderBy('name_ar')
                            ->pluck('name_ar', 'id')
                            ->all())
                        ->searchable()
                        ->required(),
                    Select::make('visit_type')
                        ->label('نوع الزيارة')
                        ->options([
                            'office' => 'حضور للمكتب',
                            'zoom' => 'زوم',
                            'field' => 'زيارة ميدانية',
                        ])
                        ->required()
                        ->default('office'),
                    Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'proposed' => 'بانتظار اختيار الجهة',
                            'planned' => 'مجدولة',
                            'completed' => 'منفذة',
                            'cancelled' => 'ملغاة',
                        ])
                        ->required()
                        ->default('proposed'),
                    DateTimePicker::make('scheduled_at')
                        ->label('الموعد المعتمد')
                        ->native(false)
                        ->displayFormat('Y-m-d h:i A')
                        ->seconds(false)
                        ->disabled()
                        ->dehydrated(),
                    DateTimePicker::make('completed_at')
                        ->label('تاريخ التنفيذ')
                        ->native(false)
                        ->displayFormat('Y-m-d h:i A')
                        ->seconds(false),
                    ]),
                Step::make('المواعيد المقترحة')
                    ->description('أضف أكثر من موعد لتختار الجهة الأنسب.')
                    ->schema([
                    Repeater::make('appointment_options')
                        ->label('المواعيد المقترحة للجهة')
                        ->schema([
                            DateTimePicker::make('starts_at')
                                ->label('الموعد المقترح')
                                ->native(false)
                                ->displayFormat('Y-m-d h:i A')
                                ->seconds(false)
                                ->required(),
                        ])
                        ->minItems(1)
                        ->defaultItems(2)
                        ->addActionLabel('إضافة موعد آخر')
                        ->columns(1)
                        ->columnSpanFull(),
                    ]),
                Step::make('نموذج الزيارة')
                    ->description('اكتب تقرير ما قبل الزيارة ومخرجاتها بمحرر نصوص.')
                    ->schema([
                    RichEditor::make('pre_visit_notes')
                        ->label('تقرير ما قبل الزيارة')
                        ->columnSpanFull(),
                    RichEditor::make('summary')
                        ->label('ملخص الزيارة')
                        ->columnSpanFull(),
                    RichEditor::make('achievements')
                        ->label('أبرز الإنجازات')
                        ->columnSpanFull(),
                    RichEditor::make('challenges')
                        ->label('التحديات')
                        ->columnSpanFull(),
                    RichEditor::make('recommendations')
                        ->label('التوصيات')
                        ->columnSpanFull(),
                    FileUpload::make('evidence_files')
                        ->label('الشواهد والمرفقات')
                        ->multiple()
                        ->disk('public')
                        ->directory('visit-reports')
                        ->downloadable()
                        ->columnSpanFull(),
                    ]),
            ])
                ->columnSpanFull()
                ->skippable(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('تفاصيل الزيارة')
                ->columns(2)
                ->schema([
                    TextEntry::make('initiative.name_ar')->label('المبادرة'),
                    TextEntry::make('organization.name_ar')->label('الجهة')->placeholder('-'),
                    TextEntry::make('visit_type')
                        ->label('نوع الزيارة')
                        ->formatStateUsing(fn (?string $state): string => [
                            'office' => 'حضور للمكتب',
                            'zoom' => 'زوم',
                            'field' => 'زيارة ميدانية',
                        ][$state] ?? '-')
                        ->badge(),
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
                TextColumn::make('organization.name_ar')->label('الجهة')->placeholder('-'),
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
                EditAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['initiative', 'organization', 'consultant'])
            ->where('consultant_user_id', Auth::id());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisitReports::route('/'),
            'create' => CreateVisitReport::route('/create'),
            'view' => ViewVisitReport::route('/{record}'),
            'edit' => EditVisitReport::route('/{record}/edit'),
        ];
    }
}
