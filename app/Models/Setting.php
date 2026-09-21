<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $fillable=['key','value'];

    public static function settings(){
        $data=Setting::get();
        $settings=[
            'is_gst'=>'',
        ];
        foreach ($data as $row) {
            $settings[$row->key]=$row->value;
        }
        return $settings;
    }
    public static function getValByKey($key){
        $settings=Setting::settings();
        if(!isset($settings[$key]) || empty($settings[$key])){
            return null;
        }
        return $settings[$key];
    }
}
