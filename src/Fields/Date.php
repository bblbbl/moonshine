<?php

declare(strict_types=1);

namespace Leeto\MoonShine\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Leeto\MoonShine\Traits\Fields\DateTrait;

class Date extends Text
{
    use DateTrait;

    protected string $type = 'date';

    public function formViewValue(Model $item): mixed
    {
        $value = parent::formViewValue($item);

        if (! $value && ! $this->getDefault() && $this->isNullable()) {
            return '';
        }

        if (! $value) {
            return $this->getDefault();
        }

        if ($value instanceof Carbon) {
            return $value->format($this->inputFormat);
        }

        return date($this->inputFormat, strtotime((string) $value));
    }

    public function indexViewValue(Model $item, bool $container = false): string
    {
        $value = parent::indexViewValue($item, $container);

        return $value ? date($this->format, strtotime((string) $value)) : '';
    }
}
