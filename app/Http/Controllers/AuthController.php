<?php
namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use DB;
use App\User;
use App\Answer;
use Cookie;

class AuthController extends Controller{

	public function GetSignin()
	{
		return view('auth/login');
	}
	public function GetLogout()
	{
		Auth::logout();

		return redirect()->route('home')->with('success', 'You have successfully logged out!');
	}
	public function Getsearch(Request $request)
	{
		$query = $request->input('q');
		if(!$query)
		{
			return redirect()->route('home');
		}
		$articles = DB::table('articles')
		->where('name', 'LIKE', $query)
		->get();
		return view('search.results', ['articles' => $articles]);
	}	
	public function GetSettings(){
		$user = Auth::user();
		return view('auth.settings',['user' => $user]);
	}
	public function PostSettings(Request $request){
		$user = Auth::user();
		$user->name = $request->input('name');
		$user->email = $request->input('email');
		
		if($request->file('img')){
			$this->validate($request, [
		        'img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
		    ]);
	        $file = request()->file('img');
	        $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
	      	$file->move('./img', $fileName);    

			$user->img = $fileName;

			DB::table("comments")
			->where('author_id', Auth::user()->id)
			->update(['autho_img' => $fileName]);
		}

		$user->save();

		return redirect()->back()->with("success", "You are successfully edit profil.");
	}
}