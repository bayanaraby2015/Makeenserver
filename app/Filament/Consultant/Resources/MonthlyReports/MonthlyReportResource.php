<?php

namespace App\Filament\Consultant\Resources\MonthlyReports;

use App\Filament\Consultant\Resources\MonthlyReports\Pages\CreateMonthlyReport;
use App\Filament\Consultant\Resources\MonthlyReports\Pages\EditMonthlyReport;
use App\Filament\Consultant\Resources\MonthlyReports\Pages\ListMonthlyReports;
use App\Filament\Consultant\Resources\MonthlyReports\Pages\ViewMonthlyReport;
use App\Models\MonthlyReport;
use App\Support\AttachmentLinks;
use App\Support\ConsultantInitiativeScope;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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

class MonthlyReportResource extends Resource
{
    protected static ?string $model = MonthlyReport::class;

    protected static ?string $slug = 'monthly-reports';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static ?int $navigationSort = 40;

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

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([
                Step::make('بيانات التقرير')
                    ->description('اختر المبادرة والشهر وحالة التقرير.')
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
                    DatePicker::make('report_month')
                        ->label('شهر التقرير')
                        ->native(false)
                        ->displayFormat('Y-m')
                        ->required(),
                    Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'draft' => 'مسودة',
                            'submitted' => 'مرسل للمراجعة',
                            'reviewed' => 'تمت المراجعة',
                        ])
                        ->required()
                        ->default('draft'),
                    ]),
                Step::make('الملخص التنفيذي')
                    ->description('اكتب الملخص ومؤشرات تقدم الإنجاز.')
                    ->schema([
                    RichEditor::make('executive_summary')
                        ->label('الملخص التنفيذي')
                        ->required()
                        ->columnSpanFull(),
                    RichEditor::make('progress_summary')
                        ->label('ملخص تقدم الإنجاز')
                        ->columnSpanFull(),
                    ]),
                Step::make('المتابعة والتوصيات')
                    ->description('أضف المخاطر والأسئلة والتوصيات والمرفقات.')
                    ->schema([
                    RichEditor::make('risks_summary')
                        ->label('المخاطر والتحديات')
                        ->columnSpanFull(),
                    RichEditor::make('questions_summary')
                        ->label('ملخص الأسئلة والاستفسارات')
                        ->columnSpanFull(),
                    RichEditor::make('recommendations')
                        ->label('التوصيات')
                        ->columnSpanFull(),
                    FileUpload::make('attachments')
                        ->label('مرفقات التقرير')
                        ->multiple()
                        ->disk('local')
                        ->visibility('private')
                        ->directory('monthly-reports')
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
            Section::make('بيانات التقرير')
                ->columns(2)
                ->schema([
                    TextEntry::make('initiative.name_ar')->label('المبادرة'),
                    TextEntry::make('organization.name_ar')->label('الجهة')->placeholder('-'),
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
                TextColumn::make('organization.name_ar')->label('الجهة')->placeholder('-'),
                TextColumn::make('report_month')->label('الشهر')->date('Y-m')->sortable(),
                TextColumn::make('status')->label('الحالة')->badge(),
                TextColumn::make('updated_at')->label('آخر تحديث')->since(),
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
            'index' => ListMonthlyReports::route('/'),
            'create' => CreateMonthlyReport::route('/create'),
            'view' => ViewMonthlyReport::route('/{record}'),
            'edit' => EditMonthlyReport::route('/{record}/edit'),
        ];
    }
}
