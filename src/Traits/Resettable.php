<?php

namespace Veneridze\PricingPlans\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;
use Veneridze\PricingPlans\Period;

trait Resettable
{
    /**
     * Get Interval Name
     *
     * @return mixed string|null
     */
    public function getIntervalNameAttribute()
    {
        $intervals = Period::getAllIntervals();

        return $intervals[$this->interval_unit] ?? null;
    }

    /**
     * Get Interval Description
     *
     * @return string
     */
    public function getIntervalDescriptionAttribute(): string
    {
        return Lang::choice('plans::messages.interval_description.' . $this->interval_unit, $this->interval_count);
    }

    /**
     * @return bool
     */
    public function isResettable(): bool
    {
        return is_string($this->interval_unit) && is_int($this->interval_count);
    }

    /**
     * @param string|null|int|\DateTime $startedAt
     * @return \Illuminate\Support\Carbon
     */
    public function getResetTime($startedAt = null): Carbon
    {
        return (new Period($this->interval_unit, $this->interval_count, $startedAt))->getEndAt();
    }
}
