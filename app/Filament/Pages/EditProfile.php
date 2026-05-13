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
}
