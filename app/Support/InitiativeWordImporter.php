<?php

namespace App\Support;

use App\Models\Initiative;
use App\Models\InitiativeMilestone;
use App\Models\InitiativeOutput;
use App\Models\InitiativePayment;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use ZipArchive;

class InitiativeWordImporter
{
    /**
     * @param  array{organization_id:int, domain:string, specializations:array<int, string>}  $defaults
     */
    public function import(string $path, array $defaults): Initiative
    {
        $text = $this->readDocx($path);
        $fields = $this->parseFields($text);

        return DB::transaction(function () use ($defaults, $fields): Initiative {
            $total = $fields['total_cost'] ?? 0;
            $vat = $fields['vat_amount'] ?? round($total * (float) config('makeen.vat_rate', 0.15), 2);
            $grandTotal = $fields['grand_total'] ?? ($total + $vat);

            $initiative = Initiative::query()->create([
                'organization_id' => $defaults['organization_id'],
                'domain' => $defaults['domain'],
                'specializations' => $defaults['specializations'],
                'name_ar' => $fields['name_ar'] ?? __('initiatives.import.untitled_name'),
                'name_en' => $fields['name_en'] ?? null,
                'related_criteria' => $fields['related_criteria'] ?? null,
                'development_justification' => $fields['development_justification'] ?? null,
                'main_goal' => $fields['main_goal'] ?? null,
                'description' => $fields['description'] ?? null,
                'strategic_objectives' => $fields['strategic_objectives'] ?? null,
                'responsible_department' => $fields['responsible_department'] ?? null,
                'owner_name' => $fields['owner_name'] ?? null,
                'partners' => $fields['partners'] ?? null,
                'beneficiaries_scope' => $fields['beneficiaries_scope'] ?? null,
                'duration_weeks' => $fields['duration_weeks'] ?? null,
                'start_date' => $fields['start_date'] ?? null,
                'end_date' => $fields['end_date'] ?? null,
                'total_cost' => $total,
                'vat_amount' => $vat,
                'grand_total' => $grandTotal,
                'currency' => 'SAR',
                'status' => 'draft',
            ]);

            foreach (($fields['outputs'] ?? []) as $index => $output) {
                InitiativeOutput::query()->create([
                    'initiative_id' => $initiative->id,
                    'order_index' => $index + 1,
                    'phase' => $output['phase'] ?? __('initiatives.import.imported_phase'),
                    'output' => $output['output'] ?? null,
                    'activities' => $output['activities'] ?? null,
                    'quantity' => $output['quantity'] ?? 1,
                    'output_description' => $output['output_description'] ?? null,
                ]);
            }

            foreach (($fields['milestones'] ?? []) as $index => $milestone) {
                InitiativeMilestone::query()->create([
                    'initiative_id' => $initiative->id,
                    'order_index' => $index + 1,
                    'phase' => $milestone['phase'] ?? __('initiatives.import.imported_phase'),
                    'outputs' => $milestone['outputs'] ?? null,
                    'quantity' => $milestone['quantity'] ?? 1,
                    'unit_cost' => $milestone['unit_cost'] ?? 0,
                    'total_cost' => $milestone['total_cost'] ?? 0,
                ]);
            }

            foreach (($fields['payments'] ?? []) as $index => $payment) {
                InitiativePayment::query()->create([
                    'initiative_id' => $initiative->id,
                    'order_index' => $index + 1,
                    'percentage' => $payment['percentage'] ?? 0,
                    'amount' => $payment['amount'] ?? 0,
                    'due_date' => $payment['due_date'] ?? null,
                    'linked_outputs' => $payment['linked_outputs'] ?? null,
                ]);
            }

            return $initiative;
        });
    }

    protected function readDocx(string $path): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException(__('initiatives.import.zip_extension_missing'));
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException(__('initiatives.import.invalid_document'));
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (! is_string($xml) || $xml === '') {
            throw new RuntimeException(__('initiatives.import.invalid_document'));
        }

        $xml = preg_replace('/<\/w:p>/', "\n", $xml) ?? $xml;
        $xml = preg_replace('/<\/w:tc>/', "\t", $xml) ?? $xml;
        $text = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');

        return trim(preg_replace("/[ \t\x{00A0}]+/u", ' ', $text) ?? $text);
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseFields(string $text): array
    {
        $lines = collect(preg_split('/\R/u', $text) ?: [])
            ->map(fn (string $line): string => trim($line, " \t\n\r\0\x0B:-–—•"))
            ->filter()
            ->values()
            ->all();

        $fields = [
            'name_ar' => $this->valueAfterLabels($lines, ['اسم المبادرة', 'عنوان المبادرة', 'المبادرة']) ?? ($lines[0] ?? null),
            'name_en' => $this->valueAfterLabels($lines, ['Initiative name', 'English name']),
            'related_criteria' => $this->valueAfterLabels($lines, ['المعايير المرتبطة', 'المعيار المرتبط']),
            'development_justification' => $this->valueAfterLabels($lines, ['مبررات التطوير', 'مبرر التطوير']),
            'main_goal' => $this->valueAfterLabels($lines, ['الهدف العام', 'الهدف الرئيسي']),
            'description' => $this->valueAfterLabels($lines, ['الوصف العام', 'وصف المبادرة', 'نبذة عن المبادرة']),
            'strategic_objectives' => $this->valueAfterLabels($lines, ['الأهداف الاستراتيجية', 'الاهداف الاستراتيجية']),
            'responsible_department' => $this->valueAfterLabels($lines, ['الإدارة المسؤولة', 'الادارة المسؤولة']),
            'owner_name' => $this->valueAfterLabels($lines, ['مالك المبادرة', 'صاحب المبادرة']),
            'partners' => $this->valueAfterLabels($lines, ['شركاء التنفيذ', 'الشركاء']),
            'beneficiaries_scope' => $this->valueAfterLabels($lines, ['النطاق البشري', 'المستفيدون', 'الفئة المستهدفة']),
            'duration_weeks' => $this->numberAfterLabels($lines, ['المدة', 'مدة المبادرة']),
            'total_cost' => $this->numberAfterLabels($lines, ['الإجمالي قبل الضريبة', 'اجمالي قبل الضريبة', 'التكلفة الإجمالية', 'التكلفة']),
            'vat_amount' => $this->numberAfterLabels($lines, ['ضريبة القيمة المضافة', 'الضريبة']),
            'grand_total' => $this->numberAfterLabels($lines, ['الإجمالي شامل', 'اجمالي شامل', 'الإجمالي النهائي']),
            'start_date' => $this->dateAfterLabels($lines, ['تاريخ البداية', 'بداية المبادرة']),
            'end_date' => $this->dateAfterLabels($lines, ['تاريخ النهاية', 'نهاية المبادرة']),
        ];

        $fields['outputs'] = $this->sectionItems($lines, ['المخرجات', 'المخرجات الرئيسية'], ['المراحل', 'الدفعات', 'مؤشرات', 'المخاطر'])
            ->map(fn (string $line): array => ['phase' => __('initiatives.import.imported_phase'), 'output' => $line, 'quantity' => 1])
            ->take(20)
            ->all();

        $fields['milestones'] = $this->sectionItems($lines, ['المراحل', 'مراحل المبادرة'], ['الدفعات', 'مؤشرات', 'المخاطر'])
            ->map(fn (string $line): array => ['phase' => $line, 'outputs' => $line, 'quantity' => 1])
            ->take(20)
            ->all();

        $fields['payments'] = $this->sectionItems($lines, ['الدفعات', 'جدول الدفعات'], ['مؤشرات', 'المخاطر'])
            ->map(fn (string $line): array => [
                'percentage' => $this->firstNumber($line) ?? 0,
                'amount' => $this->lastNumber($line) ?? 0,
                'linked_outputs' => $line,
            ])
            ->take(10)
            ->all();

        return array_filter($fields, fn (mixed $value): bool => $value !== null && $value !== [] && $value !== '');
    }

    /**
     * @param  array<int, string>  $lines
     * @param  array<int, string>  $labels
     */
    protected function valueAfterLabels(array $lines, array $labels): ?string
    {
        foreach ($lines as $index => $line) {
            foreach ($labels as $label) {
                if (! str_contains($line, $label)) {
                    continue;
                }

                $value = trim((string) preg_replace('/^.*?'.preg_quote($label, '/').'\s*[:：-]?\s*/u', '', $line));

                if ($value !== '' && $value !== $line) {
                    return $value;
                }

                return $lines[$index + 1] ?? null;
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $lines
     * @param  array<int, string>  $labels
     */
    protected function numberAfterLabels(array $lines, array $labels): int|float|null
    {
        $value = $this->valueAfterLabels($lines, $labels);

        return is_string($value) ? $this->firstNumber($value) : null;
    }

    /**
     * @param  array<int, string>  $lines
     * @param  array<int, string>  $labels
     */
    protected function dateAfterLabels(array $lines, array $labels): ?string
    {
        $value = $this->valueAfterLabels($lines, $labels);

        if (! is_string($value) || ! preg_match('/(20\d{2})[-\/](\d{1,2})[-\/](\d{1,2})/', $value, $matches)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', (int) $matches[1], (int) $matches[2], (int) $matches[3]);
    }

    protected function firstNumber(string $line): int|float|null
    {
        if (! preg_match('/\d[\d,]*(?:\.\d+)?/', $line, $matches)) {
            return null;
        }

        return (float) str_replace(',', '', $matches[0]);
    }

    protected function lastNumber(string $line): int|float|null
    {
        if (! preg_match_all('/\d[\d,]*(?:\.\d+)?/', $line, $matches) || $matches[0] === []) {
            return null;
        }

        $last = $matches[0][array_key_last($matches[0])];

        return (float) str_replace(',', '', $last);
    }

    /**
     * @param  array<int, string>  $lines
     * @param  array<int, string>  $starts
     * @param  array<int, string>  $ends
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function sectionItems(array $lines, array $starts, array $ends)
    {
        $collect = false;
        $items = collect();

        foreach ($lines as $line) {
            if (! $collect && $this->containsAny($line, $starts)) {
                $collect = true;
                continue;
            }

            if ($collect && $this->containsAny($line, $ends)) {
                break;
            }

            if ($collect && mb_strlen($line) > 2) {
                $items->push($line);
            }
        }

        return $items;
    }

    /**
     * @param  array<int, string>  $needles
     */
    protected function containsAny(string $line, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($line, $needle)) {
                return true;
            }
        }

        return false;
    }
}
