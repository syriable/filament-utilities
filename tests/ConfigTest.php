<?php

it('merges the package config under the filament-utilities key', function () {
    expect(config('filament-utilities.translator.create_missing_translation_keys'))->toBeTrue()
        ->and(config('filament-utilities.translator.path_aliases'))->toBe([
            'App\\Livewire' => 'livewire',
            'Modules\\Users\\Filament\\Resources' => 'modules/users',
        ]);
});
