<?php

use Code16\SharpFathomDashboard\Sharp\Commands\OpenFathomSharpCommand;

it('has a label', function () {
    $command = new OpenFathomSharpCommand();
    expect($command->label())->toBe('Open Fathom dashboard');
});

it('authorizes if fathom_access_url is set', function () {
    $command = new OpenFathomSharpCommand();

    config(['sharp-fathom-dashboard.fathom_access_url' => null]);
    expect($command->authorize())->toBeFalse();

    config(['sharp-fathom-dashboard.fathom_access_url' => 'https://app.usefathom.com/share/SITE_123/site']);
    expect($command->authorize())->toBeTrue();
});

it('returns a link on execute', function () {
    $url = 'https://app.usefathom.com/share/SITE_123/site';
    config(['sharp-fathom-dashboard.fathom_access_url' => $url]);

    $command = new OpenFathomSharpCommand();
    $result = $command->execute();

    expect($result)->toBe([
        'action' => 'link',
        'link' => $url,
        'openInNewTab' => false,
    ]);
});
