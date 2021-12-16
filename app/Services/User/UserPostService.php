<?php
namespace App\Services\User;

use App\Models\PostNotes;
use App\Models\User;
use Illuminate\Support\Facades\DB;
class UserPostService
{
 /*
     |--------------------------------------------------------------------------
     | UserPostService
     |--------------------------------------------------------------------------|
     | This Service handles all UserPost controller needed 
     |
     */
    /**
     * user like post 
     * 
     * @param User user
     * @param string reblogKey
     * @param integer postId 
     *
     * @return bool 
     * @author Yousif Ahmed 
     */
    public function UserLikePost(int $userId, int $postId):bool
    {
        try {
            DB::table('user_like_posts')->insert([
                'user_id' =>  $userId,
                'post_id' => $postId,
                'type' => 'like',
            ]);
        } catch (\Throwable $th) {
            return false;
        }
        return true ;
    }
    
    /**
     * user like post 
     * 
     * @param User user
     * @param integer postId 
     *
     * @return bool 
     * @author Yousif Ahmed 
     */
    public function UserUnlikePost(int $userId, int $postId):bool
    {
        try {
            DB::table('user_like_posts')->where(
                'user_id', $userId,
                'post_id', $postId)->delete();
        } catch (\Throwable $th) {
            return false;
        }
        return true ;
    }
   
}

