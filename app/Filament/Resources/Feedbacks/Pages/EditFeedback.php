<?php

namespace App\Filament\Resources\Feedbacks\Pages;

use App\Filament\Resources\Feedbacks\FeedbackResource;
use App\Services\AdminAuditLogger;
use Filament\Resources\Pages\EditRecord;

class EditFeedback extends EditRecord
{
    protected static string $resource = FeedbackResource::class;

    protected function afterSave(): void
    {
        app(AdminAuditLogger::class)->record('feedback.updated', $this->record, ['status' => $this->record->status]);
    }
}
