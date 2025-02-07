<?php

declare(strict_types=1);

namespace Leeto\MoonShine\Traits\Fields;

use Leeto\MoonShine\Fields\Field;
use Leeto\MoonShine\Filters\Filter;

/**
 * @mixin Field|Filter
 */
trait DateTrait
{
    protected string $format = 'Y-m-d H:i:s';

    protected string $inputFormat = 'Y-m-d';

    public function format($format): static
    {
        $this->format = $format;

        return $this;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function withTime(): static
    {
        $this->type = "datetime-local";
        $this->inputFormat = "Y-m-d\TH:i";

        return $this;
    }
}
