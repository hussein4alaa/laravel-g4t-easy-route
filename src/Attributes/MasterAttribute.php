<?php

namespace G4T\EaseRoute\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class MasterAttribute
{
    public function __construct(
        public ?string $uri = null,
        public ?string $name = null,
        public array $middleware = [],
        public bool $onController = false
    ) {}
}