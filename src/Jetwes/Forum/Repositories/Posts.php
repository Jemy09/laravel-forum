<?php namespace Jemy09\Forum\Repositories;

use Jemy09\Forum\Models\Post;

class Posts extends BaseRepository {

    public function __construct(Post $model)
    {
        $this->model = $model;

        $this->itemsPerPage = config('forum.integration.posts_per_thread');
    }

    public function getByID($postID, $with = array())
    {
        return $this->getFirstBy('id', $postID, $with);
    }

    public function getLastByThread($threadID, $count = 10, array $with = array())
    {
        $model = $this->model->where('parent_thread', '=', $threadID);
        $model = $model->orderBy('created_at', 'DESC')->take($count);
        $model = $model->with($with);

        return $model;
    }

}
