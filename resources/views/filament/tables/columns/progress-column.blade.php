@php
    $evaluatedColor = $getColor();
    $color = match ($evaluatedColor) {
        'primary' => 'bg-primary-500',
        'info' => 'bg-info-500',
        'danger' => 'bg-danger-500',
        'success' => 'bg-success-500',
        'warning' => 'bg-warning-500',
        default => $evaluatedColor,
    };

    $progress = $getProgress();
    $showPercentage = $getShowPercentage();
    $poll = $getPoll();
@endphp

<div class="flex w-full" @if ($poll) wire:poll.{{ $poll }} @endif>
    <div class="flex items-center w-full px-4 space-x-4 rtl:space-x-reverse">
        <div class="w-full h-2 bg-gray-200 rounded-full dark:bg-gray-600">
            <div @class([
                'h-2 rounded-full w-0 flex items-center transition-all ease-out duration-1000',
                $color,
            ]) x-data :style="'width: @js($progress)%'"></div>
        </div>
        @if ($showPercentage)
            <span class="text-sm text-gray-700 dark:text-gray-200">{{ $progress }}%</span>
        @endif
    </div>
</div>
