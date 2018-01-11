<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\User;
use Carbon\Carbon;
use Auth;
use Alert;

class AdminController extends Controller
{
    public function GetAdmin(){
        $time = Carbon::now();

    	return view('admin.admin',['time' => $time]);
    }
    public function PostAdmin(){
    	
    }
    public function GetDashboard(){
        $time = Carbon::now();

        return view('admin.dashboard',['time' => $time]);
    }
    public function GetAddArticles(){
    	return view('admin.addarticles');
    }
    public function PostAddArticles(Request $request){
        if(!Auth::user()->admin){
            return redirect()->back()->with('errors', 'You do not have permission for this option.');
        }

        $this->validate($request, [
            'title' => 'required',
            'price' => 'required',
            'details' => 'required',
            'category' => 'required',
            'filter' => 'required',
            'color' => 'required',
            'size' => 'required',
            'img' => 'required',
        ]);

        $this->validate($request, [
            'img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $file = request()->file('img');
        $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
        $file->move('./img', $fileName);    

    	DB::table('articles')->insert(
		    [
		    'id' => null,
		    'img' => $fileName,
		    'title' => $request->input('title'),
		    'price' => $request->input('price'),
		    'details' => $request->input('details'),
		    'category_id' => $request->input('category'),
		    'category' => $request->input('filter'),
		    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
		    ]
		);

        DB::table('colors')->insert(
            [
                'id' => null,
                'img' => $fileName,
                'color' => $request->input('color'),
                'article_id' => $this->ColorsID(),
                'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
            ]
        );

        DB::table('size')->insert(
            [
                'id' => null,
                'comments_id' => $this->ColorsID(),
                'size' => $request->input('size'),
                'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
            ]
        );

        DB::table('comment_offs')->insert(
            [
                'id' => null,
                'comments_id' => $this->ColorsID(),
                'c_off' => 1,
                'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
            ]
        );

		return redirect()->back()->with('success', 'You have successfully created article'.' "'.$request->input('title').'".');
    }
    //This is for get id from colors, when you create first article.
    public function ColorsID(){
        $a = array();

        $id = DB::table('articles')
             ->orderBy('id', 'desc')
             ->first();

        foreach($id as $i){
            $a[] = $i;
        }     

        $x = $a[0];

        return $x;
    }
    public function GetAllArticles(){
    	$articles = DB::table('articles')
    	->paginate(8);
    	return view('admin.allarticles', ['articles' => $articles ]);
    }
    public function PostAllArticles(Request $request){
        if($request->has('e_article')){
    		$articles = DB::table('articles')
    			    	->where('id', $request->input('id'))
    			    	->get();

        	return view('admin.editarticles', ['articles' => $articles]);
        }
        if($request->has('d_articles')){
            if(!Auth::user()->admin){
                return redirect()->back()->with('errors', 'You do not have permission for this option.');
        }
           DB::table('articles')
           ->where('id', $request->input('id'))
           ->delete();

           return redirect()->back()->with('success', "You are successfully delete article.");
        }
    }
    public function GetEditArticles(Request $request){
    	$articles = DB::table('articles')
			    	->where('id', $request->input('id'))
			    	->get();

    	return view('admin.editarticles', ['articles' => $articles]);
    }
    public function PostEditArticles(Request $request){
        if(!Auth::user()->admin){
            return redirect()->back()->with('errors', 'You do not have permission for this option.');
        }

        if($request->has('action_price')){
            DB::table('articles')
            ->where('id', $request->input('id'))
            ->update(['title' => $request->input('title'),
            'price' => $request->input('price'),
            'action_price' => $request->input('action_price'),
            'details' => $request->input('details')]);

            return redirect()->back()->with('message','You have successfully edit article "'. $request->input('title').'".');
        }

    	DB::table('articles')
	    ->where('id', $request->input('id'))
		->update(['title' => $request->input('title'),
		'price' => $request->input('price'),
		'details' => $request->input('details')]);

		return redirect()->back()->with('message','You have successfully edit article "'. $request->input('title').'".');
    }
    public function GetAllUsers(){
    	$users = User::paginate(8);

    	return view('admin.allusers',['users' => $users]);
    }
    public function PostAllUsers(Request $request){
        if(!Auth::user()->admin){
            return redirect()->back()->with('errors', 'You do not have permission for this option.');
        }

        if($request->has('s_delete')){
            User::where('id', $request->input('id'))->delete();

            return redirect()->back()->with('errors', 'You are succesfflully delete user'.' "'. $request->input('name').'".');
        }

        if($request->has('s_edit')){
            $users = User::where('id',$request->input('id'))->get();

            return view('admin.edituser', ['users' => $users]);
        }
    }
    public function GetEditUser(Request $request){
        return view('admin.edituser');
    }
    public function PostEditUser(Request $request){
        DB::table('users')
                    ->where('id', $request->input('id'))
                    ->update(['name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'admin' => $request->input('admin')]);

        return redirect()->back()->with('success','You have successfully edit user "'. $request->input('name').'".');
    }
    public function GetAdminMessage(){
        $messages = DB::table('admincontacts')
        ->paginate(8);

        return view('admin.messages', ['messages' => $messages]);
    }
    public function PostAdminMessage(Request $request){
        DB::table('usercontacts')
        ->insert([
            'id' => null,
            'user_id' => $request->input('user_id'),
            'title' => $request->input('title'),
            'comment' => $request->input('message'),
            'rided' => 0,
            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
        ]);

        return redirect()->back()->with("success", "radi");
    }
    public function GetMessages(){
        return view('contact.sendmessage');
    }
    public function PostMessages(Request $request){
        DB::table('admincontacts')
        ->insert([
            'id' => null,
            'user_id' => Auth::user()->id,
            'title' => $request->input('title'),
            'comment' => $request->input('message'),
            'rided' => 0,
            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
        ]);

        return redirect()->back()->with('success', 'You are successfully send message to Admin.');
    }
    public function GetInbox(){
        $inbox = DB::table('usercontacts')
        ->where('user_id', Auth::user()->id)
        ->paginate(8);

        return view('contact.inbox', ['inbox' => $inbox]);
    }
    public function PostInbox(Request $request){
        if($request->ajax()){
            if($request->input('inbox_id')){
               DB::table('usercontacts')
               ->where('id', $request->input('inbox_id'))
               ->update(['rided' => 1]);
            }
            if($request->input('admin_id')){
               DB::table('admincontacts')
               ->where('id', $request->input('admin_id'))
               ->update(['rided' => 1]);
            }
        }
    }
}
