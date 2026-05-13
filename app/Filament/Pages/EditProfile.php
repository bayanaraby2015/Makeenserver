<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
     * Ensure avatar_url is always present in the save payload — even
     * when the user cleared the existing image (FileUpload returns
     * null in that case but won't include the key by default on some
     * Filament versions).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! array_key_exists('avatar_url', $data)) {
            $data['avatar_url'] = null;
        }

        return $data;
    }
}
