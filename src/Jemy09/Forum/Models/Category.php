<?php namespace Jemy09\Forum\Models;

use Illuminate\Support\Str;
use Jemy09\Forum\Models\Thread;
use Jemy09\Forum\Libraries\AccessControl;


class Category extends BaseModel {

    protected $table      = 'forum_categories';
    public    $timestamps = false;
    protected $appends    = ['threadCount', 'replyCount', 'route', 'newThreadRoute'];

    public function parentCategory()
    {
        return $this->belongsTo('\Jetwes\Forum\Models\Category', 'parent_category')->orderBy('weight');
    }

    public function subcategories()
    {
        return $this->hasMany('\Jetwes\Forum\Models\Category', 'parent_category')->orderBy('weight');
    }

    public function threads()
    {
        return $this->hasMany('\Jetwes\Forum\Models\Thread', 'parent_category')->with('category', 'posts');
    }

    public function getThreadsPaginatedAttribute()
    {
        return $this->threads()->orderBy('pinned', 'desc')->orderBy('updated_at', 'desc')->paginate(config('forum.preferences.threads_per_category'));
    }

    public function getPageLinksAttribute()
    {
        return $this->threadsPaginated->render();
    }

    public function getNewestThreadAttribute()
    {
        return $this->threads()->orderBy('created_at', 'desc')->first();
    }

    public function getLatestActiveThreadAttribute()
    {
        return $this->threads()->orderBy('updated_at', 'desc')->first();
    }

    public function getThreadCountAttribute()
    {
        return $this->rememberAttribute('threadCount', function(){
            return $this->threads->count();
        });
    }

    public function getReplyCountAttribute()
    {
        return $this->rememberAttribute('replyCount', function(){
            $replyCount = 0;

            $threads = $this->threads()->get(array('id'));

            foreach ($threads as $thread) {
                $replyCount += $thread->posts->count();
            }

            return $replyCount;
        });
    }

    protected function getRouteComponents()
    {
        $components = array(
            'categoryID' => $this->id,
            'categoryAlias'    => Str::slug($this->title, '-')
        );

        return $components;
    }

    public function getRouteAttribute()
    {
        return $this->getRoute('forum.get.view.category');
    }

    public function getNewThreadRouteAttribute()
    {
        return $this->getRoute('forum.post.create.thread');
    }

    public function getCanViewAttribute()
    {
        return AccessControl::check($this, 'access_category', false);
    }

    public function getCanPostAttribute()
    {
        return AccessControl::check($this, 'create_threads', false);
    }

}
