<?php

declare(strict_types=1);

namespace Leeto\MoonShine\Traits\Fields;

use Leeto\MoonShine\Fields\Field;

/**
 * @mixin Field
 */
trait WithFullPageMode
{
    protected bool $fullPage = false;

    public function fullPage(): static
    {
        $this->fullPage = true;

        return $this;
    }

    public function isFullPage(): bool
    {
        return $this->fullPage;
    }
}
