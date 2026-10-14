<?php

return [
    'component_layout' => 'components.layouts.app',
    'temporary_file_upload' => [
        // Let Livewire use its test disk during the suite; production defaults to
        // the application's private local disk through FILESYSTEM_DISK.
        'disk' => null,
        // Keep the temporary gate permissive enough for batch recovery; each
        // item is checked again before it is persisted to the private library.
        'rules' => ['required', 'file', 'max:5120'],
        'middleware' => 'throttle:10,1',
    ],
];
