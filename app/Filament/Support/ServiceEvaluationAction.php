<?php

namespace App\Filament\Support;

use App\Models\ServiceEvaluation;
use App\Support\ServiceEvaluationOptions;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ServiceEvaluationAction
{
    public static function make(string $serviceType, ?Closure $organizationId = null): Action
    {
        return Action::make('service_evaluation_'.$serviceType)
            ->label('تقييم الخدمة')
            ->icon(Heroicon::OutlinedSparkles)
            ->color('warning')
            ->modalHeading('تقييم الخدمة')
            ->visible(fn (Model $record): bool => ! ServiceEvaluation::existsForEvaluator($serviceType, $record->getKey(), Auth::id()))
            ->schema(fn (Schema $schema): Schema => $schema->components([
                Select::make('rating')
                    ->label('التقييم')
                    ->options(ServiceEvaluationOptions::ratings())
                    ->required(),
                RichEditor::make('comments')
                    ->label('ملاحظات التقييم')
                    ->columnSpanFull(),
            ]))
            /** @param array{rating: int|string, comments?: string|null} $data */
            ->action(function (Model $record, array $data) use ($serviceType, $organizationId): void {
                if (ServiceEvaluation::existsForEvaluator($serviceType, $record->getKey(), Auth::id())) {
                    Notification::make()
                        ->warning()
                        ->title('تم تقييم هذه الخدمة مسبقا')
                        ->body('لا يمكن إضافة أكثر من تقييم لنفس الخدمة من نفس المستخدم.')
                        ->send();

                    return;
                }

                ServiceEvaluation::query()->create([
                    'service_type' => $serviceType,
                    'service_id' => $record->getKey(),
                    'evaluator_id' => Auth::id(),
                    'organization_id' => self::resolveOrganizationId($record, $organizationId),
                    'rating' => (int) $data['rating'],
                    'comments' => $data['comments'] ?? null,
                    'evaluated_at' => now(),
                ]);

                Notification::make()
                    ->success()
                    ->title('تم حفظ تقييم الخدمة')
                    ->send();
            });
    }

    private static function resolveOrganizationId(Model $record, ?Closure $organizationId): ?int
    {
        if ($organizationId !== null) {
            $value = $organizationId($record);

            return filled($value) ? (int) $value : null;
        }

        foreach (['organization_id', 'requester_organization_id'] as $field) {
            if (isset($record->{$field})) {
                return (int) $record->{$field};
            }
        }

        return null;
    }
}
