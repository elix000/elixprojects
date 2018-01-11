<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\User;
use Alert;
use App\Comment;
use App\Commentstwo;

class DetailsController extends Controller
{
    public function GetDetails(Request $request)
    {
        if(!$request->input('id')){
            return redirect()->route('home');
        }

        $details = DB::table('articles')->where('id', $request->input('id'))->get();

        $a = DB::table('articles')->where('id', $request->input('id'))->first();

        $b = $request->input('id');

        if(!$a == $b){
            return redirect()->route('home');
        }
        $comment_id = $request->input('id');

        $comments = $this->Comments($request->input('id'));


        $commentc = $this->CommentCount($request->input('id'));

        $colors = DB::table('colors')->where('article_id', $request->input('id'))->get(); 

        $comments_off = $this->Comments_off($request->input('id'));

        $sizes = DB::table('size')->where('comments_id', $request->input('id'))->get();

    	return view('shop/details',['details' => $details, 'comments' => $comments, 'commentc' => $commentc, 'colors' => $colors, 'comments_off' => $comments_off, 'comment_id' => $comment_id, 'sizes' => $sizes]);
    }
    public function AllComments($id){
        $allcomments = array();

        $comment = DB::table('comments')
        ->where('details_id', $id)
        ->get();

        foreach($comment as $c){
            $comment_two = DB::table('commentstwos')
            ->where('comment_id', $c->id)
            ->get();

            $allcomments[] = $comment_two;
        }

        return $allcomments;
    }
    public function PostDetails(Request $request)
    {
        if(!Auth::check()){
            return redirect()->back()->with('errors', 'You need to login first.');
        }

        if($request->input('comment_two')){
            $this->AddComment_t($request->input('comment_two'), $request->input('comment_id'));

            return redirect()->back()->with('success', "You successfully add new comment.");
        }
        if($request->input('comment_one')){
            $this->AddComment_one($request->input('comment_one'), $request->input('comment_id'));

            return redirect()->back()->with('success', "You successfully add new comment.");
        }

        if($request->ajax()){
            if($request->input('article_id')){
                $img = DB::table('colors')
                ->select('img')
                ->where('article_id', $request->input('article_id'))
                ->where('color', $request->Input('color'))
                ->get();

                $a = array();

                foreach ($img as $i) {
                    $a[] = $i->img;
                }

                return $a;
            }
        }

        if($request->input('d_cart')) {
            DB::table('cart')->insert(
                ['id' => null, 
                'img' => $request->input('img'),
                'name' => $request->input('title'),
                'status' => 1,
                'quantity' => $request->input('d_quantity'),
                'price' => $request->input('price'),
                'user_id' => Auth::user()->id,
                ]
            );

            return redirect()->back()->with('success', 'You are successfully buy the article'.' "'.$request->input('title').'".');
        }

        if($request->input('d_wish')) {
            DB::table('wishlist')->insert(
                ['id' => null, 
                'img' => $request->input('img'),
                'name' => $request->input('title'),
                'status' => 1,
                'price' => $request->input('price'),
                'user_id' => Auth::user()->id,
                ]
            );
                
            return redirect()->back()->with('success', 'You are successfully addedd article'.' "'.$request->input('title').'" in yours wish list.');
        }

        if($request->input('c_off')){
            if(!Auth::user()->admin){
                Alert::success('this is success alert');
                return redirect()->back();
            }
            DB::table('comment_offs')->where('comments_id', $request->input('comment_id'))
            ->update(['c_off' => 0]);

            return redirect()->back();
        }

        if($request->input('c_on')){
            if(!Auth::user()->admin){
                Alert::success('this is success alert');
                return redirect()->back();
            }
            DB::table('comment_offs')->where('comments_id', $request->input('comment_id'))
            ->update(['c_off' => 1]);

            return redirect()->back();
        }

        if($request->input('d_colors')){
            if(!Auth::user()->admin){
                return redirect()->back()->with('errors', 'You do not have permission for this option.');
            }
            DB::table('colors')
            ->insert(
                ['id' => null,
                'img' => $request->input('img'),
                'color' => $request->input('color'),
                'article_id' => $request->input('comment_id')
                ]
            );

            return redirect()->back()->with("success", "Color successfully added.");
        }
        if($request->has('d_size_admin')){
            if(!Auth::user()->admin){
                return redirect()->back()->with('errors', 'You do not have permission for this option.');
            }       
            if($request->input('d_size')){
                DB::table('size')->insert(
                    [
                        'id' =>null,
                        'comments_id' => $request->input('comment_id'),
                        'size' => $request->input('d_size'),
                        'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                        'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                    ]
                );

                return redirect()->back()->with("success", "Size successfully added.");
            }
            return redirect()->back();
        }
    }
    public function CommentCount($id){
        $ccount = DB::table('comments')
        ->where('details_id', $id)
        ->count();

        return $ccount;
    }
    public function AddComment_one($comment, $id){
        DB::table('comments')->insert(
            ['id' => null, 
            'comment' => $comment,
            'author' => Auth::user()->name,
            'autho_img' => Auth::user()->img,
            'author_id' => Auth::user()->id,
            'details_id' => $id,
            'comments_off' => 0,
            'created_at' => \Carbon\Carbon::now()->today(),
            'updated_at' => \Carbon\Carbon::now()->today()
            ]
        );
    }
    public function Comments_off($id){
        $cf = array();

        $c_of = DB::table('comment_offs')
        ->where('comments_id', $id)
        ->get();

        foreach($c_of as $c){
            $cf[] = $c;
        }

        $a = $c->c_off;

        return $a;
    }
    public function Comments($commentid){
        $comments = DB::table('comments')
        ->where('details_id', $commentid)
        ->get();
        return $comments;
    }
}
