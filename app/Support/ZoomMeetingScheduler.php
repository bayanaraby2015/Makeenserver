<?php

namespace App\Support;

use App\Models\Consultation;
use App\Models\ZoomSetting;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ZoomMeetingScheduler
{
    protected bool $settingsLoaded = false;

    protected ?ZoomSetting $cachedSettings = null;

    public function isConfigured(): bool
    {
        if ($settings = $this->settings()) {
            return $settings->isConfigured();
        }

        return filled(config('services.zoom.account_id'))
            && filled(config('services.zoom.client_id'))
            && filled(config('services.zoom.client_secret'))
            && filled(config('services.zoom.user_id'));
    }

    /**
     * @return array{provider: string, meeting_id: string|null, join_url: string|null, password: string|null}|null
     */
    public function create(Consultation $consultation, CarbonInterface|string $scheduledAt): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $startsAt = $scheduledAt instanceof CarbonInterface
            ? $scheduledAt
            : Carbon::parse($scheduledAt, config('makeen.timezone', config('app.timezone')));

        $token = $this->accessToken();

        if ($token === null) {
            return null;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->post(sprintf('https://api.zoom.us/v2/users/%s/meetings', $this->userId()), [
                'topic' => $consultation->subject,
                'type' => 2,
                'start_time' => $startsAt->toIso8601String(),
                'timezone' => config('makeen.timezone', config('app.timezone')),
                'duration' => $this->defaultDuration(),
                'agenda' => $consultation->details,
                'settings' => [
                    'join_before_host' => false,
                    'waiting_room' => true,
                    'approval_type' => 2,
                    'audio' => 'both',
                    'auto_recording' => 'none',
                ],
            ]);

        if ($response->failed()) {
            Log::warning('Zoom meeting creation failed', [
                'consultation_id' => $consultation->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $payload = $response->json();

        return [
            'provider' => 'zoom',
            'meeting_id' => isset($payload['id']) ? (string) $payload['id'] : null,
            'join_url' => $payload['join_url'] ?? null,
            'password' => $payload['password'] ?? null,
        ];
    }

    protected function accessToken(): ?string
    {
        $response = Http::withBasicAuth(
            $this->clientId(),
            $this->clientSecret(),
        )
            ->asForm()
            ->acceptJson()
            ->post('https://zoom.us/oauth/token', [
                'grant_type' => 'account_credentials',
                'account_id' => $this->accountId(),
            ]);

        if ($response->failed()) {
            Log::warning('Zoom access token request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $token = $response->json('access_token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    protected function settings(): ?ZoomSetting
    {
        if ($this->settingsLoaded) {
            return $this->cachedSettings;
        }

        $this->settingsLoaded = true;

        if (! Schema::hasTable('zoom_settings')) {
            return $this->cachedSettings = null;
        }

        return $this->cachedSettings = ZoomSetting::query()->latest('id')->first();
    }

    protected function accountId(): string
    {
        return (string) ($this->settings()?->account_id ?: config('services.zoom.account_id'));
    }

    protected function clientId(): string
    {
        return (string) ($this->settings()?->client_id ?: config('services.zoom.client_id'));
    }

    protected function clientSecret(): string
    {
        return (string) ($this->settings()?->client_secret ?: config('services.zoom.client_secret'));
    }

    protected function userId(): string
    {
        return (string) ($this->settings()?->user_id ?: config('services.zoom.user_id', 'me'));
    }

    protected function defaultDuration(): int
    {
        return (int) ($this->settings()?->default_duration ?: config('services.zoom.default_duration', 60));
    }
}
