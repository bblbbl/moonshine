<?php

declare(strict_types=1);

namespace Leeto\MoonShine\Http\Requests\Resources;

use Leeto\MoonShine\MoonShineRequest;

final class CreateFormRequest extends MoonShineRequest
{
    public function authorize(): bool
    {
        if (! in_array('create', $this->getResource()->getActiveActions(), true)) {
            return false;
        }

        return $this->getResource()
            ->can('create', $this->getResource()->getModel());
    }

    public function rules(): array
    {
        return [];
    }
}
