<?php

use App\Filament\Excellence\Resources\Initiatives\Pages\EvaluateInitiative;
use App\Models\Initiative;
use App\Models\InitiativeKpiValue;
use App\Models\KpiDefinition;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\KpiDefinitionsSeeder;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(KpiDefinitionsSeeder::class);

    $this->reviewer = User::factory()->create(['status' => 'active']);
    $this->reviewer->assignRole('excellence_manager');

    $this->actingAs($this->reviewer);

    $this->org = Organization::factory()->create([
        'type' => 'association',
        'status' => 'active',
    ]);

    $this->initiative = Initiative::factory()
        ->for($this->org, 'organization')
        ->submitted()
        ->create();

    $kpis = KpiDefinition::query()->take(3)->get();
    foreach ($kpis as $kpi) {
        InitiativeKpiValue::query()->create([
            'initiative_id' => $this->initiative->id,
            'kpi_definition_id' => $kpi->id,
            'baseline' => '0',
            'target' => '10',
        ]);
    }
});

it('saves an evaluation, scores KPIs, and approves the initiative when decision is approved', function () {
    $kpiValues = InitiativeKpiValue::query()
        ->where('initiative_id', $this->initiative->id)
        ->get();

    $kpiPayload = [];
    foreach ($kpiValues as $idx => $value) {
        $kpiPayload[] = [
            'kpi_value_id' => $value->id,
            'indicator' => 'X',
            'baseline' => '0',
            'target' => '10',
            'score' => 4 + ($idx % 2),
            'reviewer_notes' => 'مؤشر ممتاز',
        ];
    }

    Livewire::test(EvaluateInitiative::class, ['record' => $this->initiative->getRouteKey()])
        ->set('data', [
            'overall_score' => 4.2,
            'strengths' => 'تخطيط جيد',
            'improvements' => 'تحتاج لمؤشرات تأثير إضافية',
            'recommendation' => 'موصى بالاعتماد',
            'decision' => 'approved',
            'kpis' => $kpiPayload,
        ])
        ->call('save');

    $this->initiative->refresh();
    expect($this->initiative->status)->toBe('approved');

    $this->initiative->load('evaluation');
    expect($this->initiative->evaluation)->not->toBeNull()
        ->and($this->initiative->evaluation->decision)->toBe('approved')
        ->and((float) $this->initiative->evaluation->overall_score)->toBe(4.2);

    $reloadedKpis = InitiativeKpiValue::query()
        ->where('initiative_id', $this->initiative->id)
        ->get();

    foreach ($reloadedKpis as $kpi) {
        expect($kpi->score)->not->toBeNull();
    }
});

it('changes initiative to under_review when decision is pending and was just submitted', function () {
    Livewire::test(EvaluateInitiative::class, ['record' => $this->initiative->getRouteKey()])
        ->set('data.decision', 'pending')
        ->set('data.kpis', [])
        ->call('save');

    $this->initiative->refresh();
    expect($this->initiative->status)->toBe('under_review');
});
