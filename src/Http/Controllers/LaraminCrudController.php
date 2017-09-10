<?php

namespace Simoja\Laramin\Http\Controllers;

use App\DataInfo;
use App\DataType;
// use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Simoja\Laramin\Facades\Laramin;

class LaraminCrudController extends Controller
{
    protected $currentView;
    protected $currentType;

    public function __construct()
    {
        $prefix = request()->route()->getPrefix();
        $prefix = explode('/', $prefix);
        $this->currentType = $prefix[2];
        $this->currentView = 'laramin::'.$prefix[2];
    }
    public function index()
    {
        $type = $this->getTypeBySlug($this->currentType);

        $columns = $this->getTypeBySlug($this->currentType)->infos;

        $items = Laramin::model($type->name)->all();
        return view($this->currentView.'.browse')->withColumns($columns)->withItems($items)->withType($type->name);
    }

    public function create()
    {
        return view($this->currentView.'.add-edit');
    }
    public function update($type,$id)
    {
        dd($type);
    }



    // public function getDataType()
    // {
    //     $type = strtoupper($this->currentType);
    //     return DataType::where('name', $type)->first();
    // }
    //Get The Data Type And Extract DataInfo Validate DataInfo
    // public function getDataInfo()
    // {
    //     return $this->getDataType()->infos;
    // }
    public function getTypeBySlug($slug)
    {
        return Laramin::model('DataType')->where('slug',$slug)->first();
    }
    public function Validation($request)
    {
        $something = collect();
        $this->getDataInfo()->each(function ($item, $index) use ($something) {
            $something->put($item->column, json_decode($item->validation)[0]);
        });
        $this->validate($request, $something->toArray());
    }

    //check Type if has image than do that
    public function uploadImage($image)
    {
        $filename = auth()->id().'_'.date('Y_m_d_H_i_s');
        $extension = $image->getClientOriginalExtension();
        $filename = $filename.'.'.$extension;
        $path = $image->storeAs($this->currentType, $filename);
        return $filename;
    }

    public function PostModifications($requests, $imageRequest)
    {
        $requests['image'] = $this->uploadImage($imageRequest);
        return $requests;
    }

    public function store(Request $request)
    {
        /**

            TODO:
            - Featured CheckBox
         */
        switch ($this->currentType) {
                    case 'post':
                          $this->Validation($request);
                          $values = $this->PostModifications($request->all(), $request->file('image'));
                        break;
                    case 'tag':
                          $this->Validation($request);
                          $values = $request->all();
                        break;
                    case 'category':
                          $this->Validation($request);
                          $values = $request->all();
                        break;
                    default:
                          $values = $request->all();
                        break;
                }

        $type = Laramin::model($this->currentType)->create($values);
        return redirect()->route('laramin.'.$this->currentType.'.browse');
    }
}