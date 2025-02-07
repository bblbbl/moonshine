<?php

declare(strict_types=1);

namespace Leeto\MoonShine\QueryTags;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Leeto\MoonShine\Traits\HasCanSee;
use Leeto\MoonShine\Traits\Makeable;
use Leeto\MoonShine\Traits\WithIcon;
use Leeto\MoonShine\Traits\WithLabel;

final class QueryTag
{
    use Makeable;
    use WithIcon;
    use HasCanSee;
    use WithLabel;

    public function __construct(
        string $label,
        protected Builder|Closure $builder,
    ) {
        $this->setLabel($label);
    }

    public function uri(): string
    {
        return str($this->label())->slug()->value();
    }

    public function builder(): Builder
    {
        return is_callable($this->builder)
            ? call_user_func($this->builder)
            : $this->builder;
    }
}
