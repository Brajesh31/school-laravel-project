<?php

namespace App\Http\Controllers;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Storage;

class PostController extends Controller
{
    public function index() : view{
        $posts = Post::latest()->paginate(5);
        return view('posts.index', compact('posts'));
    }

    public function create(): View{
        return view('posts.create');
    }

    public function store(Request $request): RedirectResponse{
        //validasi form
        $this->validate($request, [
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'title' => 'required|min:5',
            'content' => 'required|min:10'
        ]);
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //create post
        Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content
        ]);

        return redirect()->route('posts.index') -> with(['success' => 'Data berhasil disimpan']);
    }
    public function destroy($id): RedirectResponse{
        $post = Post::findOrFail($id);
        Storage::delete('public/posts/'.$post->image);
        $post->delete();

        return redirect()->route('posts.index') -> with(['success'=> 'Data dihapus']);
    }
    public function show(string $id): View{
        $post = Post::findOrFail($id);
        return view('posts.show', compact('post'));
    }

    public function edit(string $id): View{
        $post = Post::findOrFail($id);
        return view('posts.edit', compact('post'));
    }
    public function update(Request $request, $id): RedirectResponse{
        //validate form
        $this->validate($request, ['image|mimes:jpeg,jpg,png|max:2048', 'title'=>'required|min:5', 'content'=> 'required|min:10']);
        
        //get post by id
        $post = Post::findOrFail($id);

        //check image upload
        if($request->hasFile('image')){
            //upload new image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            //delete old image
            Storage::delete('public/posts'.$post->image);

            //update post with new image
            $post->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content
            ]);
        }
    }
}
