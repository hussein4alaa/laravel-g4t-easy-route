<?php

namespace G4T\EaseRoute\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Route
{
    public function __construct(
        public ?string $uri = null,
        public array $middleware = []
    ) {}
}