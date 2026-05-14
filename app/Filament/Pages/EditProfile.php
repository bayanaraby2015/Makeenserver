<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Custom profile page that adds an "avatar" file upload alongside the
 * default name / email / password fields. Used by every panel via
 * `->profile(\App\Filament\Pages\EditProfile::class)`.
 *
 * Avatar is stored on the public disk under storage/app/public/avatars
 * and the relative path is persisted in users.avatar_url. The
 * `php artisan storage:link` symlink must exist for the uploaded
 * image to be served back to the browser.
 */
class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar_url')
                    ->label(__('profile.avatar'))
                    ->image()
                    ->avatar()
                    ->imageEditor()
                    ->imageCropAspectRatio('1:1')
                    ->disk('public')
                    ->directory('avatars')
                    ->visibility('public')
                    ->maxSize(4096)
                    ->columnSpanFull(),

                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),

                TextInput::make('phone')
                    ->label(__('profile.phone'))
                    ->tel()
                    ->maxLength(32),
            ]);
    }

    /**
     * Ensure avatar_url is correctly written on save. FileUpload yields
     * either:
     *   - the existing path (unchanged) — pass through
     *   - a newly stored disk-relative path (e.g. "avatars/abc.png")
     *   - null (user cleared the field)
     *
     * Some Filament 4 builds also wrap a single upload as an array. We
     * collapse it back to a scalar so the DB column receives a string.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $userId = Auth::id();

        try {
            Log::info('EditProfile: form data before save', [
                'user_id' => $userId,
                'avatar_url_raw' => $data['avatar_url'] ?? '(missing)',
                'avatar_url_type' => gettype($data['avatar_url'] ?? null),
            ]);
        } catch (\Throwable $e) {
            // logging must never break the save
        }

        // FileUpload sometimes wraps the value in an array.
        if (is_array($data['avatar_url'] ?? null)) {
            $data['avatar_url'] = $data['avatar_url'][0] ?? null;
        }

        // Coerce empty string to null.
        if (($data['avatar_url'] ?? null) === '') {
            $data['avatar_url'] = null;
        }

        if (! array_key_exists('avatar_url', $data)) {
            $data['avatar_url'] = null;
        }

        try {
            Log::info('EditProfile: form data after mutation', [
                'user_id' => $userId,
                'avatar_url' => $data['avatar_url'],
            ]);
        } catch (\Throwable $e) {
            // logging must never break the save
        }

        return $data;
    }
}
