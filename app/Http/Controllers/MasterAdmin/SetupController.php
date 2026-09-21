<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\ReferralSource;
use App\Models\Service;
use App\Models\Unit;
use App\Models\ProductCategory;
use App\Models\Tax;

class SetupController extends Controller
{
    // Department
    public function list_department(){
        $departments = Department::orderBy('id', 'desc')->get();
        return view('master.department.list-department', compact('departments'));
    }
    public function add_department(){
        return view('master.department.add-department');
    }
    public function store_department(Request $request){
        $validator = \Validator::make($request->all(), [
            'name' => 'required|unique:departments,name',
        ]);
        if($validator->fails()){
            $message = $validator->getMessageBag();

            return redirect()->back()->with('error', $message->first())->withInput();
        }
        $department = new Department();
        $department->name = $request->name;
        $department->created_by = auth()->user()->id;
        $department->save();
        return redirect()->route('master.department.list-department')->with('success', 'Department added successfully');
    }
    public function view_department($id){
        $department = Department::findOrFail($id);
        return view('master.department.view-department', compact('department'));
    }
    public function edit_department($id){
        $department = Department::findOrFail($id);
        return view('master.department.edit-department', compact('department'));
    }
    public function update_department(Request $request,$id){
        $validator = \Validator::make($request->all(), [
            'name' => 'required|unique:departments,name,'.$id,
        ]);
        if($validator->fails()){
            $message = $validator->getMessageBag();

            return redirect()->back()->with('error', $message->first())->withInput();
        }
        $department = Department::findOrFail($id);
        $department->name = $request->name;
        $department->save();
        return redirect()->route('master.department.list-department')->with('success', 'Department updated successfully');
    }
// End Department

// Referral Source
    public function list_source(){
        $sources = ReferralSource::orderBy('id', 'desc')->get();
        return view('master.source.list-source', compact('sources'));
    }
    public function add_source(){
        return view('master.source.add-source');
    }
    public function store_source(Request $request){
        $validator = \Validator::make($request->all(), [
            'source' => 'required|unique:referral_sources,source',
        ]);
        if($validator->fails()){
            $message = $validator->getMessageBag();

            return redirect()->back()->with('error', $message->first())->withInput();
        }
        $source = new ReferralSource();
        $source->source = $request->source;
        $source->created_by = auth()->user()->id;
        $source->save();
        return redirect()->route('master.source.list-source')->with('success', 'Source added successfully');
    }
    public function view_source($id){
        $source = ReferralSource::findOrFail($id);
        return view('master.source.view-source', compact('source'));
    }
    public function edit_source($id){
        $source = ReferralSource::findOrFail($id);
        return view('master.source.edit-source', compact('source'));
    }
    public function update_source(Request $request,$id){
        $validator = \Validator::make($request->all(), [
            'source' => 'required|unique:referral_sources,source,'.$id,
        ]);
        if($validator->fails()){
            $message = $validator->getMessageBag();

            return redirect()->back()->with('error', $message->first())->withInput();
        }
        $source = ReferralSource::findOrFail($id);
        $source->source = $request->source;
        $source->save();
        return redirect()->route('master.source.list-source')->with('success', 'Source updated successfully');
    }
    // End Referral Source

    // Services
    public function list_service(){
        $services = Service::orderBy('id', 'desc')->get();
        return view('master.service.list-services', compact('services'));
    }
    public function add_service(){
        return view('master.service.add-services');
    }
    public function store_service(Request $request){
        $validator = \Validator::make($request->all(), [
            'name' => 'required|unique:services,name',
            'rate'=> 'required|numeric',
            'seating'=>'required'
        ]);
        if($validator->fails()){
            $message = $validator->getMessageBag();

            return redirect()->back()->with('error', $message->first())->withInput();
        }
        $service = new Service();
        $service->name = $request->name;
        $service->rate = $request->rate;
        $service->seating = $request->seating;
        $service->created_by = auth()->user()->id;
        $service->save();
        return redirect()->route('master.service.list-service')->with('success', 'Service added successfully');
    }
    public function view_service($id){
        $service = Service::findOrFail($id);
        return view('master.service.view-services', compact('service'));
    }
    public function edit_service($id){
        $service = Service::findOrFail($id);
        return view('master.service.edit-services', compact('service'));
    }
    public function update_service(Request $request,$id){
        $validator = \Validator::make($request->all(), [
            'name' => 'required|unique:services,name,'.$id,
            'rate'=> 'required|numeric',
            'seating'=>'required'
        ]);
        if($validator->fails()){
            $message = $validator->getMessageBag();

            return redirect()->back()->with('error', $message->first())->withInput();
        }
        $service = Service::findOrFail($id);
        $service->name = $request->name;
        $service->rate = $request->rate;
        $service->seating = $request->seating;
        $service->save();
        return redirect()->route('master.service.list-service')->with('success', 'Service updated successfully');
    }
    // End Services

    // Unit
    public function list_unit(){
        $units = Unit::orderBy('id', 'desc')->get();
        return view('master.unit.list-unit', compact('units'));
    }
    public function add_unit(){
        return view('master.unit.add-unit');
    }
    public function store_unit(Request $request){
        $validator = \Validator::make($request->all(), [
            'unit' => 'required|unique:units,unit',
        ]);
        if($validator->fails()){
            $message = $validator->getMessageBag();

            return redirect()->back()->with('error', $message->first())->withInput();
        }
        $unit = new Unit();
        $unit->unit = $request->unit;
        $unit->created_by = auth()->user()->id;
        $unit->save();
        return redirect()->route('master.unit.list-unit')->with('success', 'Unit added successfully');
    }
    public function view_unit($id){
        $unit = Unit::findOrFail($id);
        return view('master.unit.view-unit', compact('unit'));
    }
    public function edit_unit($id){
        $unit = Unit::findOrFail($id);
        return view('master.unit.edit-unit', compact('unit'));
    }
    public function update_unit(Request $request,$id){
        $validator = \Validator::make($request->all(), [
            'unit' => 'required|unique:units,unit,'.$id,
        ]);
        if($validator->fails()){
            $message = $validator->getMessageBag();
            return redirect()->back()->with('error', $message->first())->withInput();
        }
        $unit = Unit::findOrFail($id);
        $unit->unit = $request->unit;
        $unit->save();
        return redirect()->route('master.unit.list-unit')->with('success', 'Unit updated successfully');
    }
    // End Unit

    // Product Category
    public function list_product_category(){
        $product_categories = ProductCategory::orderBy('id', 'desc')->get();
        return view('master.product-category.list-productcategory', compact('product_categories'));
    }
    public function add_product_category(){
        return view('master.product-category.add-productcategory');
    }
    public function store_product_category(Request $request){
        $validator = \Validator::make($request->all(), [
            'name' => 'required|unique:product_categories,name',
        ]);
        if($validator->fails()){
            $message = $validator->getMessageBag();

            return redirect()->back()->with('error', $message->first())->withInput();
        }
        $product_category = new ProductCategory();
        $product_category->name = $request->name;
        $product_category->created_by = auth()->user()->id;
        $product_category->save();
        return redirect()->route('master.product-category.list-productcategory')->with('success', 'Product Category added successfully');
    }
    public function view_product_category($id){
        $product_category = ProductCategory::findOrFail($id);
        return view('master.product-category.view-productcategory', compact('product_category'));
    }
    public function edit_product_category($id){
        $product_category = ProductCategory::findOrFail($id);
        return view('master.product-category.edit-productcategory', compact('product_category'));
    }
    public function update_product_category(Request $request,$id){
        $validator = \Validator::make($request->all(), [
            'name' => 'required|unique:product_categories,name,'.$id,
        ]);
        if($validator->fails()){
            $message = $validator->getMessageBag();
            return redirect()->back()->with('error', $message->first())->withInput();
        }
        $product_category = ProductCategory::findOrFail($id);
        $product_category->name = $request->name;
        $product_category->save();
        return redirect()->route('master.product-category.list-productcategory')->with('success', 'Product Category updated successfully');
    }
    // End Product Category

    // Tax
    public function list_tax(){
        $taxes = Tax::orderBy('id', 'desc')->get();
        return view('master.tax.list-tax', compact('taxes'));
    }
    public function add_tax(){
        return view('master.tax.add-tax');
    }
    public function store_tax(Request $request){
        $validator = \Validator::make($request->all(), [
            'name' => 'required|unique:taxes,name',
            'percentage' => 'required|numeric',
        ]);
        if($validator->fails()){
            $message = $validator->getMessageBag();

            return redirect()->back()->with('error', $message->first())->withInput();
        }
        $tax = new Tax();
        $tax->name = $request->name;
        $tax->percentage = $request->percentage;
        $tax->created_by = auth()->user()->id;
        $tax->save();
        return redirect()->route('master.tax.list-tax')->with('success', 'Tax added successfully');
    }
    public function edit_tax($id){
        $tax = Tax::findOrFail($id);
        return view('master.tax.edit-tax', compact('tax'));
    }
    public function update_tax(Request $request,$id){
        $validator = \Validator::make($request->all(), [
            'name' => 'required|unique:taxes,name,'.$id,
            'percentage' => 'required|numeric',
        ]);
        if($validator->fails()){
            $message = $validator->getMessageBag();
            return redirect()->back()->with('error', $message->first())->withInput();
        }
        $tax = Tax::findOrFail($id);
        $tax->name = $request->name;
        $tax->percentage = $request->percentage;
        $tax->save();
        return redirect()->route('master.tax.list-tax')->with('success', 'Tax updated successfully');
    }
    // End Tax
}