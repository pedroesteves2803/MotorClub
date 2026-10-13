<?php

// Todos os valores, modificações e registros deste catálogo são SIMULADOS.
// Não usar como ficha técnica verificada ou histórico dos criadores reais.
$projects = [
    'aventador' => ['V12 para os fins de semana', 'Uma proposta de garagem que combina presença, som e cuidado com cada detalhe.', 2020, '6.5 V12', '770 cv', 'Automatizado de 7 marchas', 'Integral', '8.400 km', 'Rosa', 'São Paulo · SP', ['Escape esportivo', 'Rodas de liga leve', 'Proteção de pintura'], 'Esportivos'],
    'touareg' => ['A nave-mãe da garagem', 'Um SUV com espaço para viagens e uma lista de melhorias que faz parte da diversão.', 2008, '4.2 V8', '350 cv', 'Automático de 6 marchas', 'Integral', '148.000 km', 'Preto', 'São Paulo · SP', ['Revisão da suspensão', 'Pneus de uso misto', 'Multimídia atualizada'], 'Projetos'],
    'caravan' => ['Clássico com personalidade', 'Uma perua brasileira em um projeto de demonstração voltado a encontros e preparação.', 1978, '4.1 · 6 cilindros', '320 cv', 'Manual de 5 marchas', 'Traseira', '42.000 km', 'Bege', 'São Paulo · SP', ['Comando esportivo', 'Escape dimensionado', 'Rodas de época'], 'Clássicos'],
    'golf' => ['Equilíbrio para todo dia', 'Um hatch discreto com conforto para a rotina e um projeto de melhorias graduais.', 2015, '1.4 TSI', '150 cv', 'Manual de 6 marchas', 'Dianteira', '86.500 km', 'Prata', 'Campinas · SP', ['Pneus novos', 'Filtro esportivo', 'Detalhamento da pintura'], 'Uso diário'],
    'golf-gti' => ['Hatch de fim de semana', 'Uma montagem de demonstração inspirada na tradição dos hatches esportivos.', 2017, '2.0 TSI', '280 cv', 'DSG de 6 marchas', 'Dianteira', '64.200 km', 'Vermelho', 'Curitiba · PR', ['Remapeamento simulado', 'Escape esportivo', 'Pastilhas de uso esportivo'], 'Esportivos'],
    'civic' => ['Giro alto, bons encontros', 'Um sedã pensado para quem gosta de câmbio manual e de cuidar da própria garagem.', 2008, '2.0 aspirado', '192 cv', 'Manual de 6 marchas', 'Dianteira', '126.000 km', 'Preto', 'Belo Horizonte · MG', ['Admissão esportiva', 'Pneus de rua', 'Suspensão revisada'], 'JDM'],
    'polo' => ['Compacto com atitude', 'Um projeto urbano com detalhes esportivos e manutenção organizada.', 2022, '1.4 TSI', '150 cv', 'Automático de 6 marchas', 'Dianteira', '34.500 km', 'Branco', 'Florianópolis · SC', ['Proteção cerâmica', 'Pneus novos', 'Iluminação interna'], 'Uso diário'],
    'uno' => ['Memórias sobre quatro rodas', 'Um clássico popular preservado para passeios e encontros da comunidade.', 2001, '1.0', '57 cv', 'Manual de 5 marchas', 'Dianteira', '182.000 km', 'Azul', 'Sorocaba · SP', ['Bancos restaurados', 'Rodas recuperadas', 'Revisão de arrefecimento'], 'Clássicos'],
    'sandero' => ['Diversão com câmbio manual', 'Um hatch para aproveitar estradas e encontros, com um histórico de cuidados bem registrado.', 2019, '2.0 aspirado', '150 cv', 'Manual de 6 marchas', 'Dianteira', '58.300 km', 'Amarelo', 'Porto Alegre · RS', ['Fluido de freio novo', 'Pneus esportivos', 'Alinhamento revisado'], 'Esportivos'],
    'opala' => ['O seis-cilindros da família', 'Um sedã clássico em uma restauração fictícia que preserva os detalhes de época.', 1988, '4.1 · 6 cilindros', '140 cv', 'Manual de 5 marchas', 'Traseira', '95.000 km', 'Vinho', 'Londrina · PR', ['Interior restaurado', 'Carburador revisado', 'Cromados recuperados'], 'Clássicos'],
    'mx-5' => ['Menos peso, mais estrada', 'Um roadster de demonstração para passeios ao ar livre e curvas de fim de semana.', 2016, '2.0 aspirado', '160 cv', 'Manual de 6 marchas', 'Traseira', '39.000 km', 'Vermelho', 'Santos · SP', ['Pneus de rua', 'Proteção da capota', 'Alinhamento esportivo'], 'Roadsters'],
];

return collect($projects)->map(function ($project) {
    [$title, $description, $year, $engine, $power, $gearbox, $drive, $mileage, $color, $city, $mods, $category] = $project;

    return [
        'title' => $title, 'description' => $description, 'category' => $category,
        'specs' => ['Ano' => (string) $year, 'Motor' => $engine, 'Potência' => $power, 'Câmbio' => $gearbox, 'Tração' => $drive, 'Quilometragem' => $mileage, 'Cor' => $color, 'Cidade do perfil' => $city],
        'mods' => $mods,
        'history' => [
            ['date' => '02/10/2026', 'title' => 'Pronto para o próximo encontro', 'description' => 'Lavagem detalhada, conferência dos pneus e registro de fotos para a garagem.', 'cost' => 'R$ 280,00'],
            ['date' => '15/09/2026', 'title' => 'Revisão preventiva', 'description' => 'Troca de óleo e filtros, inspeção de freios e conferência dos fluidos.', 'cost' => 'R$ 950,00'],
            ['date' => '20/08/2026', 'title' => 'Primeira etapa do projeto', 'description' => 'Inspeção inicial e planejamento das próximas melhorias.', 'cost' => 'R$ 450,00'],
        ],
        'next' => 'Conferir a suspensão, organizar notas de manutenção e preparar novas fotos do projeto.',
    ];
})->all();
