<?php

namespace Code16\SharpFathomDashboard\Sharp\Commands;


use Code16\Sharp\Dashboard\Commands\DashboardCommand;

class OpenFathomSharpCommand extends DashboardCommand
{

    public function label(): ?string
    {
        return __('Open Fathom dashboard');
    }

    public function execute(array $data = []): array
    {
        return $this->link(config('sharp-fathom-dashboard.fathom_access_url'));
    }

    public function authorize(): bool
    {
        return config('sharp-fathom-dashboard.fathom_access_url') !== null;
    }
}
