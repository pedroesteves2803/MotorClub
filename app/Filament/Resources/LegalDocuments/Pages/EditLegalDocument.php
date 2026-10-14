<?php

namespace App\Filament\Resources\LegalDocuments\Pages;

use App\Filament\Resources\LegalDocuments\LegalDocumentResource;
use App\Services\AdminAuditLogger;
use Filament\Resources\Pages\EditRecord;

class EditLegalDocument extends EditRecord
{
    protected static string $resource = LegalDocumentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by_id'] = auth()->id();
        $data['published_at'] = ! empty($data['is_published']) ? ($this->record->published_at ?: now()) : null;

        return $data;
    }

    protected function afterSave(): void
    {
        app(AdminAuditLogger::class)->record('legal_document.updated', $this->record, ['published' => $this->record->is_published]);
    }
}
