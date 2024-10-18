<?php

namespace Elenyum\Dashboard\Attribute;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
class StatCountRequest
{
    public function __construct(
    ) {
    }
}
