<?php

namespace App\Filament\Consultant\Resources\Consultations\Pages;

use App\Filament\Consultant\Resources\Consultations\ConsultationResource;
use App\Filament\Support\BuildsConsultationCalendarEvents;
use App\Models\Consultation;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CalendarConsultations extends Page
{
    use BuildsConsultationCalendarEvents;

    protected static string $resource = ConsultationResource::class;

    protected string $view = 'filament.consultations.calendar';

    public function getTitle(): string
    {
        return __('consultations.calendar.title');
    }

    protected function getConsultationCalendarQuery(): Builder
    {
        return Consultation::query()
            ->where('consultant_user_id', Auth::id())
            ->with(['requesterOrganization', 'consultant']);
    }

    protected function eventUrl(Consultation $consultation): string
    {
        return ConsultationResource::getUrl('view', ['record' => $consultation]);
    }
}
