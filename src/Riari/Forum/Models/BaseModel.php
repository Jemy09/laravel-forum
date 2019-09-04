<?php namespace Riari\Forum\Models;

use Cache;
use Carbon\Carbon;
use Eloquent;

abstract class BaseModel extends Eloquent {

    protected function rememberAttribute($item, $function)
    {
        $cacheItem = get_class($this).$this->id.$item;

        $value = Cache::remember($cacheItem, config('forum.preferences.cache_lifetime'), $function);

        return $value;
    }

    protected static function clearAttributeCache($model)
    {
        foreach ($model->appends as $attribute) {
            $cacheItem = get_class($model).$model->id.$attribute;
            Cache::forget($cacheItem);
        }
    }

    protected function getRouteComponents()
    {
        $components = array();

        return $components;
    }

    protected function getRoute($name, $components = array())
    {
        return route($name, array_merge($this->getRouteComponents(), $components));
    }

    protected function getTimeAgo($timestamp)
    {
        return Carbon::createFromTimeStamp(strtotime($timestamp))->diffForHumans();
    }

    public function getPostedAttribute()
    {
        return $this->getTimeAgo($this->created_at);
    }

    public function getUpdatedAttribute()
    {
        return $this->getTimeAgo($this->updated_at);
    }

    public function toggle($property)
    {
        $this->$property = !$this->$property;

        $this->save();
    }

}
