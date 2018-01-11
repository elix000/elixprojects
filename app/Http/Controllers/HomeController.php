<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use Alert;

class HomeController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
    }
    public function index()
    {
        $boffer = DB::table('articles')
        ->orderBy('price', 'asc')
        ->paginate(4);

        return view('home',['boffer' => $boffer]);
    }
    public function PostIndex(Request $request){
        if(Auth::check())
        {
            if($request->has('cart')){
                DB::table('cart')->insert(
                    ['id' => null, 
                    'img' => $request->input('img'),
                    'name' => $request->input('title'),
                    'status' => 1,
                    'quantity' => 1,
                    'price' => $request->input('price'),
                    'user_id' => Auth::user()->id,
                    ]
                );

                return redirect()->back()->with('success', 'You are successfully buy the article'.' '.$request->input('title').'.');
            }

            if($request->has('wishlist')){
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
        }   
        if($request->has('details')){
            return redirect()->route('details', ['id' => $request->input('id')]);
        }
        return redirect()->back()->with('errors', 'You must Login first.');
    }
    public function Geterrorpage(){
        return view('home.errorpage');
    }
    public function GetContact(){
        return view('home.contact');
    }
    public function PostContact(Request $request){
        return redirect()->back()->with('info', 'Radi!');
    }
    public function GetWishList(){
        
        $wishlist = DB::table('wishlist')
        ->where('user_id', Auth::user()->id)
        ->paginate(3);

        $wish_count = DB::table('wishlist')
        ->where('user_id', Auth::user()->id)
        ->count();

        if(!$wish_count){
            return redirect()->route('home')->with("errors", "Wish list is emepty.");
        }

        return view('home.wishlist',['wishlist' => $wishlist]);
    }
    public function PostWishList(Request $request){
        if($request->has('w-cart')){
            DB::table('cart')->insert(
                ['id' => null, 
                'img' => $request->input('img'),
                'name' => $request->input('name'),
                'status' => 1,
                'quantity' => 1,
                'price' => $request->input('price'),
                'user_id' => Auth::user()->id,
                ]
            );

            DB::table('wishlist')
            ->where('id', $request->input('id'))
            ->delete();

            return redirect()->back()->with('success', 'You are successfully buy the article'.' "'.$request->input('name').'".');
        }

        if($request->has('w-delete')){
            DB::table('wishlist')
            ->where('id', $request->input('id'))
            ->delete();


            return redirect()->back()->with('success', 'You are successfully delete the article'.' "'.$request->input('name').'".');
        }
    }
}
