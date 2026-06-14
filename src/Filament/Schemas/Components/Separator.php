<?php

namespace Syriable\Filament\Plugins\Utilities\Filament\Schemas\Components;

use Closure;
use Filament\Schemas\Components\Component;

class Separator extends Component
{
    protected string $view = 'filament-utilities::filament.schemas.components.separator';

    protected Closure | null | string $orientation = null;

    protected Closure | bool | null $vertical = null;

    protected Closure | null | string $variant = null;

    protected Closure | bool | null $faint = null;

    protected Closure | null | string $text = null;

    public function orientation(Closure | string | null $orientation): static
    {
        $this->orientation = $orientation;

        return $this;
    }

    public function vertical(Closure | bool | null $vertical): static
    {
        $this->vertical = $vertical;

        return $this;
    }

    public function variant(Closure | string | null $variant): static
    {
        $this->variant = $variant;

        return $this;
    }

    public function faint(Closure | bool | null $faint): static
    {
        $this->faint = $faint;

        return $this;
    }

    public function text(Closure | string | null $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getOrientation(): ?string
    {
        return $this->evaluate($this->orientation);
    }

    public function getVertical(): ?bool
    {
        return $this->evaluate($this->vertical);
    }

    public function getVariant(): ?string
    {
        return $this->evaluate($this->variant);
    }

    public function getFaint(): ?bool
    {
        return $this->evaluate($this->faint);
    }

    public function getText(): ?string
    {
        return $this->evaluate($this->text);
    }

    public static function make(string $name): static
    {
        $static = app(static::class);

        $static->key($name);
        $static->configure();

        return $static;
    }
}
