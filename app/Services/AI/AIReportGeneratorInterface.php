<?php

namespace App\Services\AI;

use App\Models\Project;

interface AIReportGeneratorInterface
{
    /** @return array{sections: array<string, array{title: string, content: string, type: string}>, missing_information: list<string>} */
    public function generate(Project $project): array;
}
