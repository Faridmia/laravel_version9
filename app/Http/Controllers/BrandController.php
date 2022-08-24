<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Multipic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
// use Intervention\Image\ImageManager;
// use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use Intervention\Image\Facades\Image;
class BrandController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function AllBrand() {
        

        $brands = Brand::latest()->paginate(5);

        return view('admin.brand.index',compact('brands'));
    }

    public function StoreBrand(Request $request) {
        $validatedData = $request->validate([
            'brand_name' => 'required|unique:brands|max:50',
            'brand_image' => 'required|mimes:jpg,jpeg,png',
        ],
        [
            'brand_name.required' => 'Please Input Brand Name',
            'brand_name.min' => 'Brand Longer Then 4 characters',
        ]);

        $brand_image = $request->file('brand_image');

        // $name_gen = hexdec(uniqid());

        // $img_ext = strtolower($brand_image->getClientOriginalExtension()); 

        // $img_name = $name_gen. '.'.$img_ext;

        // $up_location = 'image/brand/';

        // $last_img = $up_location.$img_name;

        // $brand_image->move($up_location,$img_name);

        $name_gen = hexdec(uniqid()).'.'.$brand_image->getClientOriginalExtension(); 
        Image::make($brand_image)->resize(300,200)->save('image/brand/'.$name_gen );
        

        $last_img = 'image/brand/'.$name_gen;

        Brand::insert([
            'brand_name' => $request->brand_name,
            'brand_image' =>  $last_img,
            'created_at'  => Carbon::now(),
        ]);

        // $cat = $request->all();
        // $cat->save();

        // $category = new Category;

        // $user_id = Auth::user()->id;
 
        // $category->category_name = $request->category_name;
        // $category->user_id = $user_id;
 
        // $category->save();

        // Brand::insert([
        //    'category_name' => $request->category_name,
        //    'user_id' => Auth::user()->id,
        //    'created_at' => Carbon::now()
        // ]);


        return redirect('/brand/all')->with('message','Brand Save Successfully..');
    }

    public function BrandEdit( $id ) {
        $brands = Brand::find($id); // elequoent ORM

        //$brands = DB::table('brands')->where('id',$id)->first();

        return view('admin.brand.edit',compact('brands'));
    }

    public function BrandUpdate( Request $request,$id ) {

        $validatedData = $request->validate([
            'brand_name' => 'required|min:4',
        ],
        [
            'brand_name.required' => 'Please Input Brand Name',
        ]);

        $old_image = $request->old_image;

        $brand_image = $request->file('brand_image');

        if( $brand_image ) { 

            $name_gen    = hexdec(uniqid());
            $img_ext     = strtolower($brand_image->getClientOriginalExtension()); 
            $img_name    = $name_gen. '.'.$img_ext;
            $up_location = 'image/brand/';
            $last_img    = $up_location.$img_name;
            $brand_image->move($up_location,$img_name);
    
            unlink($old_image);
    
            Brand::find($id)->update([
                'brand_name' => $request->brand_name,
                'brand_image' =>  $last_img,
                'created_at'  => Carbon::now(),
            ]);

            return Redirect()->route('all.brand')->with('success','brand updated');

        } else {
            Brand::find($id)->update([
                'brand_name' => $request->brand_name,
                'created_at'  => Carbon::now(),
            ]);
            return Redirect()->route('all.brand')->with('success','brand updated');

        }

    }

    public function BrandDelete( $id ) {

        $img = Brand::find($id);
        $old_image = $img->brand_image;

        unlink($old_image);

        Brand::find($id)->delete();

        return Redirect()->back()->with('success','Brand Delete Success');
        
    }

    // this is for multi image all method

    public function Multipic() {

        $images = Multipic::all();


        return view('admin.multipic.index',compact('images'));
    }

    public function Storeimage( Request $request ) {

        $image = $request->file('image');

        foreach( $image  as $multi_img ) {

            $name_gen = hexdec(uniqid()).'.'.$multi_img->getClientOriginalExtension(); 
            Image::make($multi_img)->resize(300,200)->save('image/multi/'.$name_gen );

            $last_img = 'image/multi/'.$name_gen;

            Multipic::insert([
                'image' =>  $last_img,
                'created_at'  => Carbon::now(),
            ]);

        }

        return Redirect()->back()->with('success','Brand Delete Success');

    }

    public function Logout() {
        Auth::logout();
        return Redirect()->route('login')->with('success','User Logout');
    }
}
