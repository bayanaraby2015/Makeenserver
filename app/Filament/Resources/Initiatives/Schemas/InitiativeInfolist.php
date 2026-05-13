<?php

namespace App\Filament\Resources\Initiatives\Schemas;

use App\Filament\Support\ServiceEvaluationSummary;
use App\Models\Initiative;
use App\Support\DisplayNumber;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class InitiativeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            self::statusTracker(),
            Tabs::make('initiative_view_tabs')
                ->columnSpanFull()
                ->tabs([
                    self::identityTab(),
                    self::outputsTab(),
                    self::milestonesTab(),
                    self::paymentsTab(),
                    self::kpisTab(),
                    self::risksTab(),
                    self::workflowTab(),
                ]),
        ]);
    }

    protected static function identityTab(): Tab
    {
        return Tab::make(__('initiatives.tabs.identity'))->schema([
            Section::make(__('initiatives.sections.basic'))
                ->columns(2)
                ->schema([
                    TextEntry::make('organization.name_ar')
                        ->label(__('initiatives.fields.organization'))
                        ->placeholder('-'),
                    TextEntry::make('specializations')
                        ->label(__('initiatives.fields.specializations'))
                        ->formatStateUsing(fn (mixed $state, Initiative $record): string => implode(', ', $record->specializationLabels()))
                        ->placeholder('-')
                        ->badge()
                        ->columnSpanFull(),
                    TextEntry::make('name_ar')->label(__('initiatives.fields.name_ar')),
                    TextEntry::make('name_en')->label(__('initiatives.fields.name_en'))->placeholder('-'),
                    TextEntry::make('status')
                        ->label(__('initiatives.fields.status'))
                        ->formatStateUsing(fn (string $state): string => __('initiatives.statuses.'.$state))
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'draft' => 'gray',
                            'submitted', 'under_review' => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            'revisions_requested' => 'info',
                            default => 'gray',
                        }),
                    TextEntry::make('grand_total')
                        ->label(__('initiatives.fields.grand_total'))
                        ->formatStateUsing(fn (mixed $state) => DisplayNumber::riyalHtml($state))
                        ->html(),
                ]),

            Section::make(__('initiatives.sections.descriptive'))
                ->collapsible()
                ->schema([
                    TextEntry::make('related_criteria')
                        ->label(__('initiatives.fields.related_criteria'))
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('development_justification')
                        ->label(__('initiatives.fields.development_justification'))
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('main_goal')
                        ->label(__('initiatives.fields.main_goal'))
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('description')
                        ->label(__('initiatives.fields.description'))
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('strategic_objectives')
                        ->label(__('initiatives.fields.strategic_objectives'))
                        ->placeholder('-')
                        ->columnSpanFull(),
                ]),

            Section::make(__('initiatives.sections.ownership'))
                ->columns(2)
                ->collapsible()
                ->schema([
                    TextEntry::make('responsible_department')
                        ->label(__('initiatives.fields.responsible_department'))
                        ->placeholder('-'),
                    TextEntry::make('owner_name')
                        ->label(__('initiatives.fields.owner_name'))
                        ->placeholder('-'),
                    TextEntry::make('partners')
                        ->label(__('initiatives.fields.partners'))
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('beneficiaries_scope')
                        ->label(__('initiatives.fields.beneficiaries_scope'))
                        ->placeholder('-'),
                    TextEntry::make('duration_weeks')
                        ->label(__('initiatives.fields.duration_weeks'))
                        ->formatStateUsing(fn (mixed $state): string => DisplayNumber::plain($state))
                        ->placeholder('-'),
                    TextEntry::make('start_date')
                        ->label(__('initiatives.fields.start_date'))
                        ->date('Y-m-d')
                        ->placeholder('-'),
                    TextEntry::make('end_date')
                        ->label(__('initiatives.fields.end_date'))
                        ->date('Y-m-d')
                        ->placeholder('-'),
                ]),

            Section::make(__('initiatives.sections.financial_summary'))
                ->columns(3)
                ->schema([
                    TextEntry::make('total_cost')
                        ->label(__('initiatives.fields.total_cost'))
                        ->formatStateUsing(fn (mixed $state) => DisplayNumber::riyalHtml($state))
                        ->html(),
                    TextEntry::make('vat_amount')
                        ->label(__('initiatives.fields.vat_amount'))
                        ->formatStateUsing(fn (mixed $state) => DisplayNumber::riyalHtml($state))
                        ->html(),
                    TextEntry::make('grand_total')
                        ->label(__('initiatives.fields.grand_total'))
                        ->formatStateUsing(fn (mixed $state) => DisplayNumber::riyalHtml($state))
                        ->html(),
                ]),
        ]);
    }

    protected static function outputsTab(): Tab
    {
        return Tab::make(__('initiatives.tabs.outputs'))->schema([
            RepeatableEntry::make('outputs')
                ->label(__('initiatives.fields.outputs'))
                ->columns(2)
                ->schema([
                    TextEntry::make('phase')->label(__('initiatives.fields.phase'))->placeholder('-'),
                    TextEntry::make('output')->label(__('initiatives.fields.output'))->placeholder('-'),
                    TextEntry::make('activities')
                        ->label(__('initiatives.fields.activities'))
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('quantity')
                        ->label(__('initiatives.fields.quantity'))
                        ->formatStateUsing(fn (mixed $state): string => DisplayNumber::plain($state))
                        ->placeholder('-'),
                    TextEntry::make('output_description')
                        ->label(__('initiatives.fields.output_description'))
                        ->placeholder('-')
                        ->columnSpanFull(),
                ])
                ->placeholder(__('initiatives.empty.no_outputs')),
        ]);
    }

    protected static function milestonesTab(): Tab
    {
        return Tab::make(__('initiatives.tabs.milestones'))->schema([
            RepeatableEntry::make('milestones')
                ->label(__('initiatives.fields.milestones'))
                ->columns(2)
                ->schema([
                    TextEntry::make('phase')->label(__('initiatives.fields.phase'))->placeholder('-'),
                    TextEntry::make('quantity')
                        ->label(__('initiatives.fields.quantity'))
                        ->formatStateUsing(fn (mixed $state): string => DisplayNumber::plain($state))
                        ->placeholder('-'),
                    TextEntry::make('outputs')
                        ->label(__('initiatives.fields.milestone_outputs'))
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('execution_months')
                        ->label(__('initiatives.fields.execution_months'))
                        ->placeholder('-')
                        ->formatStateUsing(function ($state): string {
                            if (is_array($state)) {
                                return implode(', ', $state);
                            }

                            return (string) ($state ?? '-');
                        })
                        ->columnSpanFull(),
                    TextEntry::make('unit_cost')
                        ->label(__('initiatives.fields.unit_cost'))
                        ->formatStateUsing(fn (mixed $state) => DisplayNumber::riyalHtml($state))
                        ->html(),
                    TextEntry::make('total_cost')
                        ->label(__('initiatives.fields.total_cost'))
                        ->formatStateUsing(fn (mixed $state) => DisplayNumber::riyalHtml($state))
                        ->html(),
                ])
                ->placeholder(__('initiatives.empty.no_milestones')),
        ]);
    }

    protected static function paymentsTab(): Tab
    {
        return Tab::make(__('initiatives.tabs.payments'))->schema([
            RepeatableEntry::make('payments')
                ->label(__('initiatives.fields.payments'))
                ->columns(2)
                ->schema([
                    TextEntry::make('percentage')
                        ->label(__('initiatives.fields.percentage'))
                        ->formatStateUsing(fn (mixed $state): string => DisplayNumber::percentage($state))
                        ->placeholder('-'),
                    TextEntry::make('amount')
                        ->label(__('initiatives.fields.amount'))
                        ->formatStateUsing(fn (mixed $state) => DisplayNumber::riyalHtml($state))
                        ->html(),
                    TextEntry::make('due_date')
                        ->label(__('initiatives.fields.due_date'))
                        ->date('Y-m-d')
                        ->placeholder('-'),
                    TextEntry::make('linked_outputs')
                        ->label(__('initiatives.fields.linked_outputs'))
                        ->formatStateUsing(fn (mixed $state): string => DisplayNumber::listText($state))
                        ->placeholder('-')
                        ->columnSpanFull(),
                ])
                ->placeholder(__('initiatives.empty.no_payments')),
        ]);
    }

    protected static function kpisTab(): Tab
    {
        return Tab::make(__('initiatives.tabs.kpis'))->schema([
            RepeatableEntry::make('kpiValues')
                ->label(__('initiatives.fields.kpis'))
                ->columns(2)
                ->schema([
                    TextEntry::make('definition.indicator')
                        ->label(__('initiatives.fields.kpi_indicator'))
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('baseline')->label(__('initiatives.fields.baseline'))->placeholder('-'),
                    TextEntry::make('target')->label(__('initiatives.fields.target'))->placeholder('-'),
                    TextEntry::make('score')
                        ->label(__('initiatives.fields.score'))
                        ->placeholder('-')
                        ->badge()
                        ->color(fn ($state): string => $state === null ? 'gray' : 'primary'),
                    TextEntry::make('reviewer_notes')
                        ->label(__('initiatives.fields.reviewer_notes'))
                        ->placeholder('-')
                        ->columnSpanFull(),
                ])
                ->placeholder(__('initiatives.empty.no_kpis')),
        ]);
    }

    protected static function risksTab(): Tab
    {
        return Tab::make(__('initiatives.tabs.risks'))->schema([
            RepeatableEntry::make('risks')
                ->label(__('initiatives.fields.risks'))
                ->columns(2)
                ->schema([
                    TextEntry::make('risk')
                        ->label(__('initiatives.fields.risk'))
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('likelihood')
                        ->label(__('initiatives.fields.likelihood'))
                        ->placeholder('-')
                        ->formatStateUsing(fn (?string $state): string => $state ? __('initiatives.severity.'.$state) : '-')
                        ->badge()
                        ->color(fn (?string $state): string => match ($state) {
                            'high' => 'danger',
                            'medium' => 'warning',
                            'low' => 'success',
                            default => 'gray',
                        }),
                    TextEntry::make('impact')
                        ->label(__('initiatives.fields.impact'))
                        ->placeholder('-')
                        ->formatStateUsing(fn (?string $state): string => $state ? __('initiatives.severity.'.$state) : '-')
                        ->badge()
                        ->color(fn (?string $state): string => match ($state) {
                            'high' => 'danger',
                            'medium' => 'warning',
                            'low' => 'success',
                            default => 'gray',
                        }),
                    TextEntry::make('mitigation')
                        ->label(__('initiatives.fields.mitigation'))
                        ->placeholder('-')
                        ->columnSpanFull(),
                ])
                ->placeholder(__('initiatives.empty.no_risks')),
        ]);
    }

    protected static function workflowTab(): Tab
    {
        return Tab::make(__('initiatives.sections.workflow'))->schema([
            Section::make(__('initiatives.sections.workflow'))
                ->columns(2)
                ->schema([
                    TextEntry::make('submitted_at')
                        ->label(__('initiatives.fields.submitted_at'))
                        ->dateTime('Y-m-d H:i')
                        ->placeholder('-'),
                    TextEntry::make('approved_at')
                        ->label(__('initiatives.fields.approved_at'))
                        ->dateTime('Y-m-d H:i')
                        ->placeholder('-'),
                    TextEntry::make('approver.name')
                        ->label(__('initiatives.fields.approved_by'))
                        ->placeholder('-'),
                    TextEntry::make('rejected_at')
                        ->label(__('initiatives.fields.rejected_at'))
                        ->dateTime('Y-m-d H:i')
                        ->placeholder('-'),
                    TextEntry::make('rejection_reason')
                        ->label(__('initiatives.fields.rejection_reason'))
                        ->placeholder('-')
                        ->columnSpanFull(),
                ]),
            Section::make(__('initiatives.sections.evaluations'))
                ->columns(2)
                ->schema([
                    RepeatableEntry::make('evaluations')
                        ->label(__('initiatives.fields.evaluations'))
                        ->columns(2)
                        ->schema([
                            TextEntry::make('evaluator.name')
                                ->label(__('initiatives.fields.evaluator'))
                                ->placeholder('-')
                                ->badge(),
                            TextEntry::make('decision')
                                ->label(__('initiatives.fields.decision'))
                                ->formatStateUsing(fn (?string $state): string => $state ? __('initiatives.decisions.'.$state) : '-')
                                ->badge(),
                            TextEntry::make('overall_score')
                                ->label(__('initiatives.fields.overall_score'))
                                ->placeholder('-'),
                            TextEntry::make('finalized_at')
                                ->label(__('initiatives.fields.finalized_at'))
                                ->dateTime('Y-m-d h:i A')
                                ->placeholder('-'),
                            TextEntry::make('strengths')
                                ->label(__('initiatives.fields.strengths'))
                                ->placeholder('-')
                                ->columnSpanFull(),
                            TextEntry::make('improvements')
                                ->label(__('initiatives.fields.improvements'))
                                ->placeholder('-')
                                ->columnSpanFull(),
                            TextEntry::make('recommendation')
                                ->label(__('initiatives.fields.recommendation'))
                                ->placeholder('-')
                                ->columnSpanFull(),
                        ])
                        ->placeholder(__('initiatives.empty.no_evaluations'))
                        ->columnSpanFull(),
                ]),
            Section::make('تقييمات الخدمة')
                ->schema([
                    TextEntry::make('service_evaluations')
                        ->state(fn (Initiative $record) => ServiceEvaluationSummary::render('initiative', $record->id))
                        ->html()
                        ->hiddenLabel()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    protected static function statusTracker(): ViewEntry
    {
        return ViewEntry::make('status_tracker')
            ->view('filament.initiatives.status-tracker')
            ->hiddenLabel()
            ->columnSpanFull();
    }

}
