<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use DB;
use User;

class ShopController extends Controller
{
    public function GetMens(Request $request)
    {
        $mens = DB::table('articles')
        ->where('category', 1)
        ->paginate(8);
        $cat_name = 'Mens';
        if(isset($_GET['cat'])){

            $cat_name = $this->CatName($_GET['cat']);

            if(isset($_GET['sort'])){
                $mens  = $this->SortOne($request->input('cat'),1);

                return  view('shop/mens', ['mens' => $mens, 'cat_name' => $cat_name]);
            }

            $mens = $this->Category($request->input('cat'),1);
            return view('shop/mens', ['mens' => $mens, 'cat_name' => $cat_name]);
        }

        if(isset($_GET['sort'])){
            $mens  = $this->SortAll(1);

            return  view('shop/mens', ['mens' => $mens, 'cat_name' => $cat_name]);
        }

        return view('shop/mens', ['mens' => $mens, 'cat_name' => $cat_name]);
    }
    public function CatName($id){
        $name = null;

        switch ($id) {
            case '1':
            $name = 'T-shirts';
            break;
            case '2':
            $name = 'Hoodies';
            break;
            case '3':
            $name = 'Pants';
            break;
            
            default:
            $name = 'Mens';
            break;
        }

        return $name;
    }
    public function PostMens(Request $request){
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
        }

        if($request->has('details')){
            return redirect()->route('details', ['id' => $request->input('id')]);
        }

        return redirect()->back()->with('errors', 'You must Login first.');
    }
    public function GetWomens(Request $request)
    {
        $womens = DB::table('articles')
        ->where('category', 2)
        ->paginate(8);
        $cat_name = 'Womens';

        if(isset($_GET['cat'])){
            $cat_name = $this->CatName($_GET['cat']);

            if(isset($_GET['sort'])){
                $womens  = $this->SortOne($request->input('cat'),2);

                return  view('shop/womens', ['womens' => $womens,'cat_name' => $cat_name]);
            }

           $womens = $this->Category($request->input('cat'),2);
           return view('shop/womens', ['womens' => $womens,'cat_name' => $cat_name]);
        }

        if(isset($_GET['sort'])){
            $womens  = $this->SortAll(2);

            return  view('shop/womens', ['womens' => $womens,'cat_name' => $cat_name]);
        }

        return view('shop/womens',['womens' => $womens,'cat_name' => $cat_name]);
    }
    public function PostWomens(Request $request){
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

                return redirect()->back()->with('success', 'You are successfully buy the article'.' "'.$request->input('title').'".');
            }
        }

        if($request->has('details')){
            return redirect()->route('details', ['id' => $request->input('id')]);
        }
        
        return redirect()->route('signin')->with('errors', 'You must Sign In first.');
    }
    public function GetCart()
    {
        $price = DB::table('cart')
        ->where('user_id', Auth::user()->id)
        ->sum(DB::raw('quantity * price'));

        $cart = DB::table('cart')
        ->where('user_id', Auth::user()->id)
        ->get();

        $cart_count = DB::table('cart')
        ->where('user_id', Auth::user()->id)
        ->count();
        
        if(!$cart_count){
            return redirect()->route('home')->with("errors", "Cart is emepty.");
        }

        return view('shop/cart', ['cart' => $cart, 'price' => $price]);
    }
    public function PostCart(Request $request){
        $check_quantity = DB::table('cart')
        ->where('id', $request->input('id'))
        ->first();


        if($request->has('cart_plus')){
            $quantity = $request->input('cart_quantity')+1;

            DB::table('cart')
            ->where('id', $request->input('id'))
            ->update(['quantity' => $quantity]);

            return redirect()->back();
        }
        
        if($check_quantity->quantity)
        {
            if($request->has('cart_minus')){
                $quantity = $request->input('cart_quantity')-1;

                DB::table('cart')
                ->where('id', $request->input('id'))
                ->update(['quantity' => $quantity]);

                return redirect()->back();
            }
        }else{
            /*Alert::error('Cannot be less than 0.');
            return redirect()->back();*/
            //alert()->success('You have been logged out.', 'Good bye!');
            return redirect()->back()->with("errors", "Cannot be less than 0.");
        }
        
        if($request->has('cart_delete')){
            DB::table('cart')
            ->where('id', $request->input('id'))
            ->delete();

           // Alert::success('You are successfully delete article.');
            return redirect()->back();
        }

    }
    public function GetDetails(Request $requst)
    {
    	$id = $requst->input('id');

    	$articles = DB::select('select * from articles where id = :id', ['id' => $id]);

    	if($articles)
    	{
    		return view('details', ['articles' => $articles]);
    	}

    	return redirect()->back();
    }
        public function mens($id){
        echo $id;
    }
    public function GetSearch(Request $request){
        if($request->has('search')){
            $search = DB::table('articles')
            ->where('title', 'like', $request->input('search'))
            ->paginate(8);

            $scount = DB::table('articles')
            ->where('title', 'like', $request->input('search'))
            ->count();

            return view('shop/search', ['search' => $search,'scount' => $scount]);
        }

        return redirect()->back();
    }
    public function Category($id,$cat){
        $mens = DB::table('articles')
        ->where([
            ['category_id', $id],
            ['category', $cat]
        ])->paginate(8);

        return $mens;
    }
    public function SortAll($cat){
        $sort_id = $_GET['sort'];
        $orderBy = null;
        switch ($sort_id) {
            case '1':
            $orderBy = 'desc';
            break;
            case '2':
            $orderBy = 'asc';
            break;
            default:
            $orderBy = 'desc';
            break;
        }

        $mens = DB::table('articles')
             ->orderBy('price', $orderBy)
             ->where('category', $cat)
             ->paginate(8); 

        return $mens;
    }
    public function SortOne($id,$cat){
        $sort_id = $_GET['sort'];
        $orderBy = null;
        switch ($sort_id) {
            case '1':
            $orderBy = 'desc';
            break;
            case '2':
            $orderBy = 'asc';
            break;
            default:
            $orderBy = 'desc';
            break;
        }

        $mens = DB::table('articles')
             ->orderBy('price', $orderBy)
             ->where([
                ['category_id', $id],
                ['category', $cat],
            ])->paginate(8); 

        return $mens;
    }
    public function GetD(){
        return view('shop/details');
    }
}
