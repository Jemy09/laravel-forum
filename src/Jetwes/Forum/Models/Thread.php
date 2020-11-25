<?php namespace Jemy09\Forum\Models;

use Redirect;
use Jemy09\Forum\Libraries\AccessControl;
use Jemy09\Forum\Libraries\Alerts;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Thread extends BaseModel {

    use SoftDeletes;

    protected $table      = 'forum_threads';
    public    $timestamps = true;
    protected $dates      = ['deleted_at'];
    protected $appends    = ['lastPage', 'lastPost', 'lastPostRoute', 'route', 'lockRoute', 'pinRoute', 'replyRoute', 'deleteRoute'];
    protected $guarded    = ['id'];

    public function category()
    {
        return $this->belongsTo('\Jetwes\Forum\Models\Category', 'parent_category');
    }

    public function author()
    {
        return $this->belongsTo(config('forum.integration.user_model'), 'author_id');
    }

    public function posts()
    {
        return $this->hasMany('\Jetwes\Forum\Models\Post', 'parent_thread');
    }

    public function getPostsPaginatedAttribute()
    {
        return $this->posts()->paginate(config('forum.preferences.posts_per_thread'));
    }

    public function getPageLinksAttribute()
    {
        return $this->postsPaginated->render();
    }

    public function getLastPageAttribute()
    {
        return $this->postsPaginated->lastPage();
    }

    public function getLastPostAttribute()
    {
        return $this->posts()->orderBy('created_at', 'desc')->first();
    }

    public function getLastPostRouteAttribute()
    {
        return $this->Route . '?page=' . $this->lastPage . '#post-' . $this->lastPost->id;
    }

    public function getLastPostTimeAttribute()
    {
        return $this->lastPost->created_at;
    }

    protected function getRouteComponents()
    {
        $components = array(
            'categoryID' => $this->category->id,
            'categoryAlias' => Str::slug($this->category->title, '-'),
            'threadID' => $this->id,
            'threadAlias' => Str::slug($this->title, '-')
        );

        return $components;
    }

    public function getRouteAttribute()
    {
        return $this->getRoute('forum.get.view.thread');
    }

    public function getReplyRouteAttribute()
    {
        return $this->getRoute('forum.get.reply.thread');
    }

    public function getPinRouteAttribute()
    {
        return $this->getRoute('forum.post.pin.thread');
    }

    public function getLockRouteAttribute()
    {
        return $this->getRoute('forum.post.lock.thread');
    }

    public function getDeleteRouteAttribute()
    {
        return $this->getRoute('forum.delete.thread');
    }

    public function getCanReplyAttribute()
    {
        return AccessControl::check($this, 'reply_to_thread', false);
    }

    public function getCanPinAttribute()
    {
        return AccessControl::check($this, 'pin_threads', false);
    }

    public function getCanLockAttribute()
    {
        return AccessControl::check($this, 'lock_threads', false);
    }

    public function getCanDeleteAttribute()
    {
        return AccessControl::check($this, 'delete_threads', false);
    }

    public function toggle($property)
    {
        parent::toggle($property);

        Alerts::add('success', trans('forum::base.thread_updated'));
    }

}
