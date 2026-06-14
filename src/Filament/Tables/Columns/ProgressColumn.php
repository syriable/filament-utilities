<?php

namespace Syriable\Filament\Plugins\Utilities\Filament\Tables\Columns;

use Closure;
use Filament\Tables\Columns\Column;

class ProgressColumn extends Column
{
    protected string $view = 'filament-utilities::filament.tables.columns.progress-column';

    protected string | Closure $color = 'primary';

    protected ?Closure $progress = null;

    protected string | Closure | null $poll = null;

    protected bool | Closure $showPercentage = true;

    public function showPercentage(bool | Closure $condition = true): static
    {
        $this->showPercentage = $condition;

        return $this;
    }

    public function getShowPercentage(): bool
    {
        return $this->evaluate($this->showPercentage);
    }

    public function color(string | Closure $callback): static
    {
        $this->color = $callback;

        return $this;
    }

    public function getColor(): string
    {
        return (string) $this->evaluate($this->color);
    }

    public function progress(Closure $callback): static
    {
        $this->progress = $callback;

        return $this;
    }

    public function getProgress(): int | float | Closure
    {
        if (! $this->progress instanceof Closure) {
            return floor($this->getStateFromRecord() ?? 0);
        }

        return $this->evaluate($this->progress);
    }

    public function poll(string | Closure $duration): static
    {
        $this->poll = $duration;

        return $this;
    }

    public function getPoll(): string
    {
        return (string) $this->evaluate($this->poll);
    }
}
