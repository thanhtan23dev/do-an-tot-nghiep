<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class Order extends Model
{
    protected $guarded = [];

    // protected static function booted()
    // {
    //     static::saving(function ($order) {
    //         $imageAdded = $order->isDirty('design_image') && !empty($order->design_image);
    //         $quycachAdded = $order->isDirty('quy_cach') && !empty($order->quy_cach);

    //         if ($imageAdded || $quycachAdded) {
    //             $order->status = 'processing';
    //         }
    //     });
    // }

    protected static function booted()
    {
        static::saving(function ($order) {
            if ($order->isDirty('design_image') && !empty($order->design_image)) {
                $order->status = 'processing';
            }
        });

        static::addGlobalScope('filterGroup', function (Builder $builder) {
            if (request() && request()->has('group_id') && request('group_id') != '') {
                $builder->where('orders.customer_group_id', request('group_id'));
            }
        });
    }

    public function updateStats()
    {
        $this->total_tasks = Task::where('order_id', $this->id)->count();
        $this->completed_tasks = Task::where('order_id', $this->id)->where('status', 'completed')->count();

        if ($this->total_tasks > 0) {
            $this->progress_percent = round(($this->completed_tasks / $this->total_tasks) * 100, 2);
        } else {
            $this->progress_percent = 0;
        }

        $this->saveQuietly();
    }

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function scopeProcessing($query)
    {
        return $query->whereIn('status', ['processing', 'waiting']);
    }

    public function designer()
    {
        return $this->belongsTo(NhanVien::class, 'designer_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }
}
