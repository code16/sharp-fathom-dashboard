<?php

namespace Code16\SharpFathomDashboard\Client\ValueObjects;

use Illuminate\Support\Carbon;

final readonly class Site
{
    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?string $sharing = null,
        public ?Carbon $createdAt = null,
    ) {
    }

    public static function fromPayload(array $payload): self
    {
        return new self(
            id: $payload['id'] ?? null,
            name: $payload['name'] ?? null,
            sharing: $payload['sharing'] ?? null,
            createdAt: $payload['created_at'] ? Carbon::parse($payload['created_at']) : null,
        );
    }
}
