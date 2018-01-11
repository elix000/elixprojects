<?php
namespace App;
use DB;
use Auth;

Class MyLibrary {
	public static function SumCart(){
		$cart = DB::table('cart')
		->where('user_id', Auth::user()->id)
	    ->count();

	     return $cart;
	}

	public static function SumWish(){
		$wcount = DB::table('wishlist')
		->where('user_id', Auth::user()->id)
        ->count();

        return $wcount;
	}

	public static function CountUsers(){
		$count_users = DB::table('users')
        ->count();

        return $count_users;
	}

	public static function CountArticles(){
		$count_articles = DB::table('articles')
        ->count();

        return $count_articles;
	}

	public static function CountComments(){
		$count_comments = DB::table('comments')
        ->count();

        return $count_comments;
	}
	public static function CountAdminMessages(){
		$count_messages = DB::table('admincontacts')
        ->count();

        return $count_messages;
	}
	public static function CountMessages(){
		$countm = DB::table('usercontacts')
		->where('user_id', Auth::user()->id)
		->where('rided', 0)
        ->count();

        return $countm;
	}
}