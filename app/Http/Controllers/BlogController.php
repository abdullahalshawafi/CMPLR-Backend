<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use App\Http\Misc\Helpers\Errors;
use App\Services\Blog\BlogService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\BlogCollection;
use App\Services\Blog\FollowBlogService;
use App\Http\Resources\BLogFollowersCollection;

class BlogController extends Controller
{


    protected $BlogService;
    protected $FollowBlogService;

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(FollowBlogService $FollowBlogService, BlogService $BlogService)
    {
        $this->BlogService = $BlogService;
        $this->FollowBlogService = $FollowBlogService;
    }

    /**
     * @OA\Get(
     *   path="/recommended/blogs",
     *   summary="Retrieve recommended blogs",
     *   description="Retrieve recommended blogs for the explore",
     *   operationId="GetRecommendedBlogs",
     *   tags={"Explore"},
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(property="meta", type="object",
     *         @OA\Property(property="status_code", type="integer", example=200),
     *         @OA\Property(property="msg", type="string", example="Success"),
     *       ),
     *       @OA\Property(property="response", type="object",
     *         @OA\Property(property="blogs", type="array",
     *           @OA\Items(type="object",
     *              @OA\Property(property="blog_id", type="integer", example=1),
     *              @OA\Property(property="blog_name", type="string", example="abdullahadel"),
     *              @OA\Property(property="title", type="string", example="Abdullah Adel"),
     *              @OA\Property(property="avatar", type="string", example="https://assets.tumblr.com/images/default_avatar/cone_closed_128.png"),
     *              @OA\Property(property="avatar_shape", type="string", example="circle"),
     *              @OA\Property(property="header_image", type="string", example="https://assets.tumblr.com/images/default_header/optica_pattern_02_640.png?_v=b976ee00195b1b7806c94ae285ca46a7"),
     *              @OA\Property(property="description", type="string", example=""),
     *              @OA\Property(property="background_color", type="string", example="white"),
     *          )
     *         ),
     *         @OA\Property(property="total_following", type="number", example=36),
     *         @OA\Property(property="next_url", type="string", example="https://www.cmplr.tech/api/recommended/blogs?page=2"),
     *         @OA\Property(property="current_page", type="number", example=1),
     *         @OA\Property(property="next_page", type="number", example=2),
     *         @OA\Property(property="posts_per_page", type="number", example=4),
     *         )
     *       )
     *     )
     *   ),
     * security ={{"bearer":{}}}
     * )
     */

    /**
     * This function is responsible for getting
     * recommended blogs (paginated)
     * 
     * @return \Illuminate\Http\Response
     * 
     * @author Abdullah Adel
     */
    public function GetRecommendedBlogs()
    {
        // Check if there is an authenticated user
        $user = auth('api')->user();
        $user_id = null;

        if ($user) {
            $user_id = $user->id;
        }

        $recommended_blogs = $this->BlogService->GetRandomBlogs($user_id);

        if (!$recommended_blogs) {
            return $this->error_response(Errors::ERROR_MSGS_404, '', 404);
        }

        $response = $this->success_response(new BlogCollection($recommended_blogs));

        return $response;
    }

    /**
     * @OA\Get(
     *   path="/trending/blogs",
     *   summary="Retrieve trending blogs",
     *   description="Retrieve trending blogs for the explore",
     *   operationId="GetTrendingBlogs",
     *   tags={"Explore"},
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(property="meta", type="object",
     *         @OA\Property(property="status_code", type="integer", example=200),
     *         @OA\Property(property="msg", type="string", example="Success"),
     *       ),
     *       @OA\Property(property="response", type="object",
     *         @OA\Property(property="blogs", type="array",
     *           @OA\Items(type="object",
     *              @OA\Property(property="blog_id", type="integer", example=1),
     *              @OA\Property(property="blog_name", type="string", example="abdullahadel"),
     *              @OA\Property(property="title", type="string", example="Abdullah Adel"),
     *              @OA\Property(property="avatar", type="string", example="https://assets.tumblr.com/images/default_avatar/cone_closed_128.png"),
     *              @OA\Property(property="avatar_shape", type="string", example="circle"),
     *              @OA\Property(property="header_image", type="string", example="https://assets.tumblr.com/images/default_header/optica_pattern_02_640.png?_v=b976ee00195b1b7806c94ae285ca46a7"),
     *              @OA\Property(property="description", type="string", example=""),
     *              @OA\Property(property="background_color", type="string", example="white"),
     *          )
     *         ),
     *         @OA\Property(property="total_following", type="number", example=36),
     *         @OA\Property(property="next_url", type="string", example="https://www.cmplr.tech/api/trending/blogs?page=2"),
     *         @OA\Property(property="current_page", type="number", example=1),
     *         @OA\Property(property="next_page", type="number", example=2),
     *         @OA\Property(property="posts_per_page", type="number", example=4),
     *         )
     *       )
     *     )
     *   ),
     * security ={{"bearer":{}}}
     * )
     */

    /**
     * This function is responsible for getting
     * trending blogs (paginated)
     * 
     * @return \Illuminate\Http\Response
     * 
     * @author Abdullah Adel
     */
    public function GetTrendingBlogs()
    {
        // Check if there is an authenticated user
        $user = auth('api')->user();
        $user_id = null;

        if ($user) {
            $user_id = $user->id;
        }

        $trending_blogs = $this->BlogService->GetRandomBlogs($user_id);

        if (!$trending_blogs) {
            return $this->error_response(Errors::ERROR_MSGS_404, '', 404);
        }

        $response = $this->success_response(new BlogCollection($trending_blogs));

        return $response;
    }

    /**
     * @OA\Get(
     * path="/blog/{blog-identifier}/followed_by",
     * summary="Check If Followed By Blog",
     * description="This method can be used to check if one of your blogs is followed by another blog.",
     * operationId="followedBy",
     * tags={"Blogs"},
     *  @OA\Parameter(
     *         name="blog-identifier",
     *         in="path",
     *         required=true,
     *      ),
     * @OA\Response(
     *    response=404,
     *    description="Not Found",
     * ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated",
     *   ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="meta", type="object",
     *         @OA\Property(property="status", type="integer", example=200),
     *         @OA\Property(property="msg", type="string", example="OK"),
     *       ),
     *       @OA\Property(property="response", type="object",
     *         @OA\Property(property="followed_by", type="boolean", example=false),
     *       )
     *    )
     * ),
     * security ={{"bearer":{}}}
     * )
     */
    public function followedBy(Request $request, Blog $blog)
    {
    }

    /**
     * @OA\Get(
     * path="/blog/{blog-identifier}/followers",
     * summary="Retrieve a Blog's Followers",
     * description="This method can be used to get the followers of a specific blog",
     * operationId="GetFollowers",
     * tags={"Blogs"},
     *  @OA\Parameter(
     *         name="blog-identifier",
     *         in="path",
     *         required=true,
     *      ),
     *  @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="The number of results to return: 1–20",
     *      ),
     *  @OA\Parameter(
     *         name="offset",
     *         in="query",
     *         required=false,
     *         description="Result to start at",
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"blog-identifier"},
     *       @OA\Property(property="blog-identifier", type="string", format="text", example="summer_blog"),
     *       @OA\Property(property="limit", type="integer", format="integer", example= 10),
     *    ),
     * ),
     * @OA\Response(
     *    response=404,
     *    description="Not Found",
     * ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="meta", type="object",
     *          @OA\Property(property="status", type="integer", example=200),
     *           @OA\Property(property="msg", type="string", example="OK"),
     *        ),
     *       @OA\Property(property="response", type="object",
     *             @OA\Property(property="total_users", type="integer", example=1235),           
     *             @OA\Property(property="Users", type="array",
     *                @OA\Items(
     *                      @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="david"
     *                      ),
     *                      @OA\Property(
     *                         property="following",
     *                         type="Boolean",
     *                         example=true
     *                      ),
     *                      @OA\Property(
     *                         property="url",
     *                         type="string",
     *                         example="https://www.davidslog.com"
     *                      ),
     *                      @OA\Property(
     *                         property="updated",
     *                         type="integer",
     *                         example=1308781073
     *                      ),
     *                ),
     *             ),           
     *           ),
     *        ),
     *     ),
     * security ={{"bearer":{}}}
     * )
     */
    /**
     * Get Blog Followers
     * 
     * @param Request $request
     * @param Blog $blog
     * 
     * @return Response $response
     * 
     */
    public function GetFollowers(Request $request, Blog $blog)
    {
        // get blog_name
        $blog_name = $request->route('blog_name');
        // Get Blog using blog_name

        $blog = $this->FollowBlogService->GetBlog($blog_name);
        if (!$blog)
            return $this->error_response(Errors::ERROR_MSGS_404, 'Blog Not Found ', 404);

        // Check if This USer is Authorize to do this action
        try {
            $this->authorize('BlogBelongsToUser', $blog);
        } catch (\Throwable $th) {
            return $this->error_response(Errors::ERROR_MSGS_403, 'This action is unauthorized.', 403);
        }
        // Get followers'sid that follow this blog
        $followersId = $this->FollowBlogService->GetFollowersID($blog->id);

        // Get Followers Information
        $followersInfo = $this->FollowBlogService->GetFollowersInfo($followersId);
        //  $followers = $this->FollowBlogService->GetFollowers($followers_id );

        $response['number_of_followers'] = count($followersId);
        $response['followers'] = $followersInfo;

        return $this->success_response($response);
    }

    /**
     * @OA\Get(
     *   path="/blog/{blog-identifier}/following",
     *   summary="Retrieve Blog's following",
     *   description="This method can be used to retrieve the publicly exposed list of blogs that a blog follows, in order from most recently-followed to first.",
     *   operationId="getFollowing",
     *   tags={"Blogs"},
     *   @OA\Parameter(
     *     name="blog-identifier",
     *     in="path",
     *     description="Any blog identifier",
     *     required=true,
     *   ),
     *   @OA\Parameter(
     *     name="offset",
     *     in="query",
     *     description="Followed blog index to start at",
     *     required=false,
     *   ),
     *   @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="The number of results to retrieve, 1-20, inclusive",
     *     required=false,
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *       @OA\Property(property="meta", type="object",
     *         @OA\Property(property="status", type="integer", example=200),
     *         @OA\Property(property="msg", type="string", example="OK"),
     *       ),
     *       @OA\Property(property="response", type="object",
     *         @OA\Property(property="total_blogs", type="number", example=20),
     *         @OA\Property(property="blogs", type="array",
     *           @OA\Items(
     *             @OA\Property(property="title", type="string", example="John Doe"),
     *             @OA\Property(property="name", type="string", example="john-doe"),
     *             @OA\Property(property="updated", type="number", example=1308953007),
     *             @OA\Property(property="url", type="string", example="https://www.cmplr.com/blogs/john-doe"),
     *             @OA\Property(property="description", type="string", example="<p><strong>Mr. Karp</strong> is tall and skinny, with unflinching blue eyes a mop of brown hair.\r\nHe speaks incredibly fast and in complete paragraphs.</p>"),
     *           )
     *         ),
     *         @OA\Property(property="_links", type="object",
     *           @OA\Property(property="next", type="object",
     *             @OA\Property(property="href", type="string", example="/api/v1/blogs/john-doe/blocks?offset=20"),
     *             @OA\Property(property="method", type="string", example="GET"),
     *             @OA\Property(property="query_params", type="object",
     *               @OA\Property(property="offset", type="number", example=20),
     *             )
     *           )
     *         )
     *       )
     *     )
     *   ),
     * security ={{"bearer":{}}}
     * )
     */
    public function getFollowing(Request $request, Blog $blog)
    {
    }
}