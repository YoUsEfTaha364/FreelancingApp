<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Skill;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class SharedController extends Controller
{
     public function GetSkills(){
        $skills=Skill::all();
        return ResponseService::Response(["skills"=>$skills],200,"get skills");
        
    }
    
    public function GetLanguages(){
        $languages=Language::all();
        return ResponseService::Response(["languages"=>$languages],200,"get languages");
        
    }
}
