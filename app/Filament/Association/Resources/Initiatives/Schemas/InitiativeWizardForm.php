<?php

namespace App\Filament\Association\Resources\Initiatives\Schemas;

use App\Models\KpiDefinition;
use App\Support\InitiativeSpecializations;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class InitiativeWizardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([
                self::stepIdentity(),
                self::stepOutputs(),
                self::stepMilestones(),
                self::stepPayments(),
                self::stepKpis(),
                self::stepRisks(),
                self::stepReview(),
            ])
                ->skippable()
                ->columnSpanFull(),
        ]);
    }

    protected static function stepIdentity(): Step
    {
        return Step::make(__('initiatives.tabs.identity'))
            ->icon(Heroicon::OutlinedIdentification)
            ->schema([
                Section::make(__('initiatives.sections.basic'))
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name_ar')
                            ->label(__('initiatives.fields.name_ar'))
                            ->prefixIcon(Heroicon::OutlinedTag)
                            ->required()
                            ->maxLength(255),
                        TextInput::make('name_en')
                            ->label(__('initiatives.fields.name_en'))
                            ->prefixIcon(Heroicon::OutlinedTag)
                            ->maxLength(255),
                        Select::make('domain')
                            ->label(__('initiatives.fields.domain'))
                            ->prefixIcon(Heroicon::OutlinedRectangleGroup)
                            ->options([
                                'developmental_impact' => __('initiatives.domains.developmental_impact'),
                                'sustainability' => __('initiatives.domains.sustainability'),
                                'institutional_empowerment' => __('initiatives.domains.institutional_empowerment'),
                            ])
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),
                        Select::make('specializations')
                            ->label(__('initiatives.fields.specializations'))
                            ->prefixIcon(Heroicon::OutlinedRectangleGroup)
                            ->multiple()
                            ->options(InitiativeSpecializations::options())
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('initiatives.sections.descriptive'))
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->schema([
                        Textarea::make('related_criteria')
                            ->label(__('initiatives.fields.related_criteria'))
                            ->rows(2),
                        Textarea::make('development_justification')
                            ->label(__('initiatives.fields.development_justification'))
                            ->rows(3),
                        Textarea::make('main_goal')
                            ->label(__('initiatives.fields.main_goal'))
                            ->rows(2),
                        Textarea::make('description')
                            ->label(__('initiatives.fields.description'))
                            ->rows(4),
                        Textarea::make('strategic_objectives')
                            ->label(__('initiatives.fields.strategic_objectives'))
                            ->rows(2),
                    ]),

                Section::make(__('initiatives.sections.ownership'))
                    ->icon(Heroicon::OutlinedUsers)
                    ->columns(2)
                    ->schema([
                        TextInput::make('responsible_department')
                            ->label(__('initiatives.fields.responsible_department'))
                            ->prefixIcon(Heroicon::OutlinedBuildingOffice2)
                            ->maxLength(255),
                        TextInput::make('owner_name')
                            ->label(__('initiatives.fields.owner_name'))
                            ->prefixIcon(Heroicon::OutlinedUserCircle)
                            ->maxLength(255),
                        Textarea::make('partners')
                            ->label(__('initiatives.fields.partners'))
                            ->rows(2)
                            ->columnSpanFull(),
                        TextInput::make('beneficiaries_scope')
                            ->label(__('initiatives.fields.beneficiaries_scope'))
                            ->prefixIcon(Heroicon::OutlinedUserGroup),
                        TextInput::make('duration_weeks')
                            ->label(__('initiatives.fields.duration_weeks'))
                            ->prefixIcon(Heroicon::OutlinedClock)
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(520),
                        DatePicker::make('start_date')
                            ->label(__('initiatives.fields.start_date'))
                            ->prefixIcon(Heroicon::OutlinedCalendar)
                            ->native(false),
                        DatePicker::make('end_date')
                            ->label(__('initiatives.fields.end_date'))
                            ->prefixIcon(Heroicon::OutlinedCalendar)
                            ->native(false)
                            ->afterOrEqual('start_date'),
                    ]),

                Section::make(__('initiatives.sections.financial_summary'))
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->columns(3)
                    ->schema([
                        TextInput::make('total_cost')
                            ->label(__('initiatives.fields.total_cost'))
                            ->numeric()
                            ->prefix('ر.س')
                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                            ->default(0),
                        TextInput::make('vat_amount')
                            ->label(__('initiatives.fields.vat_amount'))
                            ->numeric()
                            ->prefix('ر.س')
                            ->prefixIcon(Heroicon::OutlinedReceiptPercent)
                            ->default(0),
                        TextInput::make('grand_total')
                            ->label(__('initiatives.fields.grand_total'))
                            ->numeric()
                            ->prefix('ر.س')
                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                            ->default(0),
                    ]),
            ]);
    }

    protected static function stepOutputs(): Step
    {
        return Step::make(__('initiatives.tabs.outputs'))
            ->icon(Heroicon::OutlinedRectangleStack)
            ->schema([
                Repeater::make('outputs')
                    ->label(__('initiatives.fields.outputs'))
                    ->relationship()
                    ->orderColumn('order_index')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phase')
                            ->label(__('initiatives.fields.phase'))
                            ->prefixIcon(Heroicon::OutlinedFlag),
                        TextInput::make('output')
                            ->label(__('initiatives.fields.output'))
                            ->prefixIcon(Heroicon::OutlinedSparkles),
                        Textarea::make('activities')
                            ->label(__('initiatives.fields.activities'))
                            ->rows(2)
                            ->columnSpanFull(),
                        TextInput::make('quantity')
                            ->label(__('initiatives.fields.quantity'))
                            ->prefixIcon(Heroicon::OutlinedHashtag)
                            ->numeric()
                            ->minValue(0)
                            ->default(1),
                        Textarea::make('output_description')
                            ->label(__('initiatives.fields.output_description'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->defaultItems(1)
                    ->addActionLabel(__('initiatives.actions.add_output'))
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['phase'] ?? null),
            ]);
    }

    protected static function stepMilestones(): Step
    {
        return Step::make(__('initiatives.tabs.milestones'))
            ->icon(Heroicon::OutlinedCalendarDays)
            ->schema([
                Repeater::make('milestones')
                    ->label(__('initiatives.fields.milestones'))
                    ->relationship()
                    ->orderColumn('order_index')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phase')
                            ->label(__('initiatives.fields.phase'))
                            ->prefixIcon(Heroicon::OutlinedFlag),
                        TextInput::make('quantity')
                            ->label(__('initiatives.fields.quantity'))
                            ->prefixIcon(Heroicon::OutlinedHashtag)
                            ->numeric()
                            ->minValue(0)
                            ->default(1),
                        Textarea::make('outputs')
                            ->label(__('initiatives.fields.milestone_outputs'))
                            ->rows(2)
                            ->columnSpanFull(),
                        Select::make('execution_months')
                            ->label(__('initiatives.fields.execution_months'))
                            ->prefixIcon(Heroicon::OutlinedCalendar)
                            ->multiple()
                            ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8', 9 => '9', 10 => '10', 11 => '11', 12 => '12'])
                            ->columnSpanFull(),
                        DatePicker::make('start_date')
                            ->label(__('initiatives.fields.start_date'))
                            ->prefixIcon(Heroicon::OutlinedCalendar)
                            ->native(false),
                        DatePicker::make('end_date')
                            ->label(__('initiatives.fields.end_date'))
                            ->prefixIcon(Heroicon::OutlinedCalendar)
                            ->native(false)
                            ->afterOrEqual('start_date'),
                        TextInput::make('unit_cost')
                            ->label(__('initiatives.fields.unit_cost'))
                            ->numeric()
                            ->prefix('ر.س')
                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                            ->default(0),
                        TextInput::make('total_cost')
                            ->label(__('initiatives.fields.total_cost'))
                            ->numeric()
                            ->prefix('ر.س')
                            ->prefixIcon(Heroicon::OutlinedBanknotes)
                            ->default(0),
                    ])
                    ->defaultItems(1)
                    ->addActionLabel(__('initiatives.actions.add_milestone'))
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['phase'] ?? null),
            ]);
    }

    protected static function stepPayments(): Step
    {
        return Step::make(__('initiatives.tabs.payments'))
            ->icon(Heroicon::OutlinedCreditCard)
            ->schema([
                Repeater::make('payments')
                    ->label(__('initiatives.fields.payments'))
                    ->relationship()
                    ->orderColumn('order_index')
                    ->columns(2)
                    ->schema([
                        TextInput::make('percentage')
                            ->label(__('initiatives.fields.percentage'))
                            ->prefixIcon(Heroicon::OutlinedChartPie)
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('amount')
                            ->label(__('initiatives.fields.amount'))
                            ->numeric()
                            ->prefix('ر.س')
                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                            ->minValue(0),
                        DatePicker::make('due_date')
                            ->label(__('initiatives.fields.due_date'))
                            ->prefixIcon(Heroicon::OutlinedCalendar)
                            ->native(false),
                        Select::make('linked_outputs')
                            ->label(__('initiatives.fields.linked_outputs'))
                            ->prefixIcon(Heroicon::OutlinedLink)
                            ->multiple()
                            ->options(function (Get $get): array {
                                /** @var array<int, array<string, mixed>>|null $outputs */
                                $outputs = $get('../../outputs');
                                if (! is_array($outputs)) {
                                    return [];
                                }
                                $options = [];
                                foreach ($outputs as $row) {
                                    $label = $row['output'] ?? null;
                                    if (! is_string($label) || $label === '') {
                                        continue;
                                    }
                                    $prefix = isset($row['phase']) && is_string($row['phase']) && $row['phase'] !== ''
                                        ? '['.$row['phase'].'] '
                                        : '';
                                    $options[$label] = $prefix.$label;
                                }

                                return $options;
                            })
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ])
                    ->defaultItems(0)
                    ->maxItems(10)
                    ->addActionLabel(__('initiatives.actions.add_payment'))
                    ->reorderableWithButtons()
                    ->collapsible(),
            ]);
    }

    protected static function stepKpis(): Step
    {
        return Step::make(__('initiatives.tabs.kpis'))
            ->icon(Heroicon::OutlinedChartBar)
            ->schema([
                Repeater::make('kpiValues')
                    ->label(__('initiatives.fields.kpis'))
                    ->relationship()
                    ->columns(2)
                    ->schema([
                        Select::make('kpi_definition_id')
                            ->label(__('initiatives.fields.kpi_indicator'))
                            ->prefixIcon(Heroicon::OutlinedChartBarSquare)
                            ->options(fn () => KpiDefinition::query()
                                ->orderBy('domain')
                                ->orderBy('order_index')
                                ->get()
                                ->mapWithKeys(fn (KpiDefinition $k) => [
                                    $k->id => __('initiatives.domains.'.$k->domain).' — '.$k->indicator,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                        TextInput::make('baseline')
                            ->label(__('initiatives.fields.baseline'))
                            ->prefixIcon(Heroicon::OutlinedMinusCircle)
                            ->maxLength(255),
                        TextInput::make('target')
                            ->label(__('initiatives.fields.target'))
                            ->prefixIcon(Heroicon::OutlinedFlag)
                            ->maxLength(255),
                    ])
                    ->defaultItems(0)
                    ->addActionLabel(__('initiatives.actions.add_kpi'))
                    ->collapsible(),
            ]);
    }

    protected static function stepRisks(): Step
    {
        return Step::make(__('initiatives.tabs.risks'))
            ->icon(Heroicon::OutlinedExclamationTriangle)
            ->schema([
                Repeater::make('risks')
                    ->label(__('initiatives.fields.risks'))
                    ->relationship()
                    ->orderColumn('order_index')
                    ->columns(2)
                    ->schema([
                        Textarea::make('risk')
                            ->label(__('initiatives.fields.risk'))
                            ->rows(2)
                            ->columnSpanFull(),
                        Select::make('likelihood')
                            ->label(__('initiatives.fields.likelihood'))
                            ->prefixIcon(Heroicon::OutlinedScale)
                            ->options([
                                'high' => __('initiatives.severity.high'),
                                'medium' => __('initiatives.severity.medium'),
                                'low' => __('initiatives.severity.low'),
                            ])
                            ->native(false),
                        Select::make('impact')
                            ->label(__('initiatives.fields.impact'))
                            ->prefixIcon(Heroicon::OutlinedBolt)
                            ->options([
                                'high' => __('initiatives.severity.high'),
                                'medium' => __('initiatives.severity.medium'),
                                'low' => __('initiatives.severity.low'),
                            ])
                            ->native(false),
                        Textarea::make('mitigation')
                            ->label(__('initiatives.fields.mitigation'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->defaultItems(0)
                    ->addActionLabel(__('initiatives.actions.add_risk'))
                    ->reorderableWithButtons()
                    ->collapsible(),
            ]);
    }

    protected static function stepReview(): Step
    {
        return Step::make(__('initiatives.sections.workflow'))
            ->icon(Heroicon::OutlinedClipboardDocumentCheck)
            ->schema([
                Section::make(__('initiatives.sections.workflow'))->schema([
                    Textarea::make('reviewer_notes_preview')
                        ->label(__('initiatives.fields.rejection_reason'))
                        ->disabled()
                        ->placeholder(__('initiatives.actions.submit_modal_description'))
                        ->rows(3)
                        ->dehydrated(false),
                ]),
            ]);
    }
}
