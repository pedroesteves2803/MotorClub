<?php

namespace App\Filament\Resources\LegalDocuments\Pages;

use App\Filament\Resources\LegalDocuments\LegalDocumentResource;
use App\Services\AdminAuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateLegalDocument extends CreateRecord
{
    protected static string $resource = LegalDocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['updated_by_id'] = auth()->id();
        $data['published_at'] = ! empty($data['is_published']) ? now() : null;

        return $data;
    }

    protected function afterCreate(): void
    {
        app(AdminAuditLogger::class)->record('legal_document.created', $this->record, ['published' => $this->record->is_published]);
    }
}
