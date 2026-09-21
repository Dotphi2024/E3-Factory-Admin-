<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function add(){
        $settings = Setting::get();
        return view('master.settings.add',compact('settings'));
    }
    public function store(Request $request){
        foreach ($request->all() as $key => $value) {
            if ($key !== '_token') { // Exclude the CSRF token
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }
        return redirect()->back()->with('success','Settings Updated Successfully');
    }
    public function privacy_policy(){
        $privacy_policy = Setting::getValByKey('privacy_policy');
        return view('master.settings.privacy-policy',compact('privacy_policy'));
    }
    public function update_privacy_policy(Request $request){
        $validator = \Validator::make($request->all(), [
            'privacy_policy' => 'required',
        ]);
        if ($validator->fails()) {
            $message = $validator->getMessageBag();
            return redirect()->back()->with('error', $message->first());
        }
        Setting::updateOrCreate(['key' => 'privacy_policy'], ['value' => $request->privacy_policy]);
        return redirect()->back()->with('success','Privacy Policy Updated Successfully');
    }

    public function terms_of_service(){
        $terms_of_service = Setting::getValByKey('terms_of_service');
        return view('master.settings.terms-of-service',compact('terms_of_service'));
    }

    public function update_terms_of_service(Request $request){
        $validator = \Validator::make($request->all(), [
            'terms_of_service' => 'required',
        ]);
        if ($validator->fails()) {
            $message = $validator->getMessageBag();
            return redirect()->back()->with('error', $message->first());
        }
        Setting::updateOrCreate(['key' => 'terms_of_service'], ['value' => $request->terms_of_service]);
        return redirect()->back()->with('success','Terms Of Service Updated Successfully');
    }

    public function learn_about(){
        $learn_about = Setting::getValByKey('learn_about');
        return view('master.settings.learn-about',compact('learn_about'));
    }

    public function update_learn_about(Request $request){
        $validator = \Validator::make($request->all(), [
            'learn_about' => 'required',
        ]);
        if ($validator->fails()) {
            $message = $validator->getMessageBag();
            return redirect()->back()->with('error', $message->first());
        }
        Setting::updateOrCreate(['key' => 'learn_about'], ['value' => $request->learn_about]);
        return redirect()->back()->with('success','Learn About Updated Successfully');
    }

    public function get_help(){
        $get_help = Setting::getValByKey('get_help');
        return view('master.settings.get-help',compact('get_help'));
    }

    public function update_get_help(Request $request){
        $validator = \Validator::make($request->all(), [
            'get_help' => 'required',
        ]);
        if ($validator->fails()) {
            $message = $validator->getMessageBag();
            return redirect()->back()->with('error', $message->first());
        }
        Setting::updateOrCreate(['key' => 'get_help'], ['value' => $request->get_help]);
        return redirect()->back()->with('success','Get Help Updated Successfully');
    }

    public function recommendation_banner(){
        $recommendation_banner = Setting::getValByKey('recommendation_banner');
        return view('master.settings.recommendation-banner',compact('recommendation_banner'));
    }

    public function update_recommendation_banner(Request $request){
        $validator = \Validator::make($request->all(), [
            'image' => 'required|image',
        ]);
        if ($validator->fails()) {
            $message = $validator->getMessageBag();
            return redirect()->back()->with('error', $message->first());
        }
        $getRecommendationBanner = Setting::getValByKey('recommendation_banner');
        if($getRecommendationBanner && file_exists(public_path('uploads/recommendation-banner/'.$getRecommendationBanner))){
            unlink(public_path('uploads/recommendation-banner/'.$getRecommendationBanner));
        }
        $image = $request->file('image');
        $imageName = time().'.'.str_replace(' ','-',$image->getClientOriginalName());
        $image->move(public_path('uploads/recommendation-banner'), $imageName);
        Setting::updateOrCreate(['key' => 'recommendation_banner'], ['value' => $imageName]);
        return redirect()->back()->with('success','Recommendation Banner Updated Successfully');
    }
}
