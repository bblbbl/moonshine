<?php

declare(strict_types=1);

namespace Leeto\MoonShine\Fields;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Leeto\MoonShine\Contracts\Fields\HasAssets;
use Leeto\MoonShine\Contracts\Fields\HasFields;
use Leeto\MoonShine\Contracts\Fields\Relationships\BelongsToRelation;
use Leeto\MoonShine\Contracts\Fields\Relationships\HasRelationship;
use Leeto\MoonShine\Contracts\Fields\Relationships\ManyToManyRelation;
use Leeto\MoonShine\Contracts\Fields\Relationships\OneToManyRelation;
use Leeto\MoonShine\Contracts\Fields\Relationships\OneToOneRelation;
use Leeto\MoonShine\Contracts\ResourceRenderable;
use Leeto\MoonShine\Contracts\Resources\ResourceContract;
use Leeto\MoonShine\Helpers\Condition;
use Leeto\MoonShine\MoonShine;
use Leeto\MoonShine\Traits\Fields\ShowWhen;
use Leeto\MoonShine\Traits\Fields\XModel;
use Leeto\MoonShine\Traits\HasCanSee;
use Leeto\MoonShine\Traits\Makeable;
use Leeto\MoonShine\Traits\WithAssets;
use Leeto\MoonShine\Traits\WithHint;
use Leeto\MoonShine\Traits\WithHtmlAttributes;
use Leeto\MoonShine\Traits\WithLabel;
use Leeto\MoonShine\Traits\WithView;
use Leeto\MoonShine\Utilities\AssetManager;

abstract class FormElement implements ResourceRenderable, HasAssets
{
    use Makeable;
    use WithLabel;
    use WithHtmlAttributes;
    use WithView;
    use WithHint;
    use WithAssets;
    use ShowWhen;
    use HasCanSee;
    use XModel;

    protected string $field;

    protected ?string $relation = null;

    protected ?ResourceContract $resource;

    protected ?Field $parent = null;
    protected bool $group = false;

    protected string $resourceTitleField = '';

    protected ?Closure $valueCallback = null;

    protected ?string $default = null;

    protected bool $nullable = false;

    protected bool $fieldContainer = true;

    /**
     * @deprecated Will be deleted
     */
    protected bool $fullWidth = false;

    final public function __construct(
        string $label = null,
        string $field = null,
        Closure|ResourceContract|string|null $resource = null
    ) {
        $this->setLabel(trim($label ?? (string)str($this->label)->ucfirst()));
        $this->setField(trim($field ?? (string)str($this->label)->lower()->snake()));

        if ($this->hasRelationship()) {
            $this->setField($field ?? (string)str($this->label)->camel());

            if ($this->belongToOne() && ! str($this->field())->contains('_id')) {
                $this->setField(
                    (string)str($this->field())
                        ->append('_id')
                        ->snake()
                );
            }

            $this->setRelation($field ?? (string)str($this->label)->camel());

            if (str($this->relation())->contains('_id')) {
                $this->setRelation(
                    (string)str($this->relation())
                        ->remove('_id')
                        ->camel()
                );
            }

            if ($resource instanceof ResourceContract) {
                $this->setResource($resource);
            } elseif (is_string($resource)) {
                $this->setResourceTitleField($resource);
            }

            if ($this instanceof HasFields && ! $this->manyToMany() && ! $this->hasFields()) {
                $this->fields($this->resource()?->getFields()?->formFields()?->toArray() ?? []);
            }
        }

        if ($resource instanceof Closure) {
            $this->setValueCallback($resource);
        }
    }

    protected function afterMake(): void
    {
        if ($this->getAssets()) {
            app(AssetManager::class)->add($this->getAssets());
        }
    }

    public function field(): string
    {
        return $this->field;
    }

    public function setField(string $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function relation(): ?string
    {
        return $this->relation;
    }

    public function setRelation(string $relation): static
    {
        $this->relation = $relation;

        return $this;
    }

    public function resource(): ?ResourceContract
    {
        return $this->resource ?? $this->findResource();
    }

    protected function findResource(): ?ResourceContract
    {
        if (isset($this->resource)) {
            return $this->resource;
        }

        if (! $this->relation()) {
            return null;
        }

        return MoonShine::getResourceFromUriKey(
            str($this->relation())
                ->singular()
                ->append('Resource')
                ->kebab()
                ->value()
        );
    }

    public function setResource(?ResourceContract $resource): void
    {
        $this->resource = $resource;
    }

    public function resourceTitleField(): string
    {
        if ($this->resourceTitleField) {
            return $this->resourceTitleField;
        }

        return $this->resource() && $this->resource()->titleField()
            ? $this->resource()->titleField()
            : 'id';
    }

    public function setResourceTitleField(string $resourceTitleField): static
    {
        $this->resourceTitleField = trim($resourceTitleField);

        return $this;
    }

    public function valueCallback(): ?Closure
    {
        return $this->valueCallback;
    }

    protected function setValueCallback(Closure $valueCallback): void
    {
        $this->valueCallback = $valueCallback;
    }

    public function default(string $default): static
    {
        $this->default = $default;

        return $this;
    }

    public function getDefault(): ?string
    {
        $value = old($this->nameDot(), $this->default);

        return is_array($value) ? null : $value;
    }

    public function nullable($condition = null): static
    {
        $this->nullable = Condition::boolean($condition, true);

        return $this;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function parent(): ?Field
    {
        return $this->parent;
    }

    public function hasParent(): bool
    {
        return $this->parent instanceof self;
    }

    protected function setParent(Field $field): static
    {
        $this->parent = $field;

        return $this;
    }

    public function setParents(): static
    {
        if ($this instanceof HasFields) {
            $fields = [];

            foreach ($this->getFields() as $field) {
                $field = $field->setParents();

                $fields[] = $field->setParent($this);
            }

            $this->fields($fields);
        }

        return $this;
    }

    protected function group(): static
    {
        $this->group = true;

        return $this;
    }

    public function isGroup(): bool
    {
        return $this->group;
    }

    public function isResourceModeField(): bool
    {
        return ($this->toOne() || $this->toMany()) && $this->isResourceMode();
    }

    /**
     * Set field label block view on forms, based on condition
     *
     * @param  mixed  $condition
     * @return $this
     * @deprecated Will be deleted
     */
    public function fullWidth(mixed $condition = null): static
    {
        $this->fullWidth = Condition::boolean($condition, true);

        return $this;
    }

    /**
     * @deprecated Will be deleted
     */
    public function isFullWidth(): bool
    {
        return $this->fullWidth;
    }

    public function fieldContainer(mixed $condition = null): static
    {
        $this->fieldContainer = Condition::boolean($condition, true);

        return $this;
    }

    public function hasFieldContainer(): bool
    {
        return $this->fieldContainer;
    }

    public function hasRelationship(): bool
    {
        return $this instanceof HasRelationship;
    }

    public function belongToOne(): bool
    {
        return $this->hasRelationship() && $this instanceof BelongsToRelation;
    }

    public function toOne(): bool
    {
        return $this->hasRelationship() && $this instanceof OneToOneRelation;
    }

    public function toMany(): bool
    {
        return $this->hasRelationship() && $this instanceof OneToManyRelation;
    }

    public function manyToMany(): bool
    {
        return $this->hasRelationship() && $this instanceof ManyToManyRelation;
    }

    public function getRelated(Model $model): Model
    {
        return $model->{$this->relation()}()->getRelated();
    }

    public function requestValue(): mixed
    {
        return request(
            $this->nameDot(),
            $this->getDefault() ?? old($this->nameDot(), false)
        );
    }
}
