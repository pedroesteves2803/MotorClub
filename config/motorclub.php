<?php

return ['ai_provider' => env('MOTORCLUB_AI_PROVIDER', 'fake'),
    'admin_emails' => array_values(array_filter(array_map('strtolower', array_map('trim', explode(',', (string) env('ADMIN_EMAILS', '')))))),
    'shots_platform_commission' => (float) env('SHOTS_PLATFORM_COMMISSION', 10),
    'shots_preview_max_dimension' => (int) env('SHOTS_PREVIEW_MAX_DIMENSION', 1200),
    'shots_preview_webp_quality' => (int) env('SHOTS_PREVIEW_WEBP_QUALITY', 80),
    'shots_watermark_opacity' => (int) env('SHOTS_WATERMARK_OPACITY', 52),
    'shots_watermark_count' => (int) env('SHOTS_WATERMARK_COUNT', 7),
    'shots_watermark_text' => env('SHOTS_WATERMARK_TEXT', 'MOTORCLUB SHOTS'),
    'shots_watermark_photographer' => env('SHOTS_WATERMARK_PHOTOGRAPHER', true),
    'states' => ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'],
    'vehicle_types' => ['car' => 'Carro', 'motorcycle' => 'Moto', 'pickup' => 'Picape', 'truck' => 'Caminhão', 'classic' => 'Clássico', 'offroad' => 'Off-road', 'other' => 'Outro'],
    'categories' => ['maintenance' => 'Manutenção', 'performance' => 'Performance', 'appearance' => 'Visual', 'restoration' => 'Restauração'],
    'project_status' => ['planning' => 'Planejamento', 'ongoing' => 'Em andamento', 'completed' => 'Concluído'],
    'quote_status' => ['pending' => 'Aguardando oficina', 'answered' => 'Respondido', 'declined' => 'Não atendido', 'cancelled' => 'Cancelado'],
    'event_categories' => ['meet' => 'Encontro', 'exhibition' => 'Exposição', 'track' => 'Evento em autódromo', 'workshop' => 'Oficina de aprendizado']];
