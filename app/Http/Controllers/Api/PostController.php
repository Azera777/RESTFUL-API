<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\PostResource;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * index
     * 
     * @return void
     */
    public function index()
    {
        $posts = Post::latest()->paginate(5);

        return new PostResource(true, 'List Data Posts', $posts);
    }
    /**
     * store
     * 
     * @param mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' =>
            'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //create post
        $post = Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        //return response
        return new PostResource(true, 'Data Post Berhasil Ditambahkan', $post);
    }

    /**
     * show data
     * 
     * @param mixed $post
     * @return void
     */
    public function show($id)
    {
        $post = Post::find($id);

        return new PostResource(true, 'Detail Data Post', $post);
    }

    /**
     * delete data
     * 
     * @param int $id
     * @return void
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        //check if post exist
        if(!$post){
            return response()->json(['message' => 'Post Not Found'], 404);
        }

        //delete post
        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

    /**
     * update post
     * 
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if(!$post){
            return response()->json(['message' => 'Post Not Found'], 404);
        }

        //validator data
        $validator = Validator::make($request->all(), [
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $post->title = $request->title;
        $post->content = $request->content;

        if($request->hasFile('image')){
            Storage::delete('public/posts/' .$post->image);

            $newImage = $request->file('image');
            $newImage->storeAs('public/posts', $newImage->hashName());
            $post->image = $newImage->hashName();
        }

        $post->save();

        return new PostResource(true, 'Data Post Berhasil Diperbarui', $post);
    }
}
