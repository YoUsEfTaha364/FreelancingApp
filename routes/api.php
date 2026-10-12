<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Profile\ClientProfileController;
use App\Http\Controllers\Profile\FreelancerProfileController;
use App\Http\Controllers\Profile\FreelancerExperienceController;
use App\Http\Controllers\Profile\FreelancerSkillController;
use App\Http\Controllers\Profile\FreelancerLanguageController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::prefix('auth')->group(function () {

    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});






Route::middleware(["auth:sanctum", "freelancer"])
    ->prefix("freelancer")
    ->group(function () {

        // Profile
        Route::get("profile", [FreelancerProfileController::class, "show"]);
        Route::put("profile", [FreelancerProfileController::class, "update"]);


        // Experiences
        Route::get("experiences", [FreelancerExperienceController::class, "index"]);
        Route::post("experiences", [FreelancerExperienceController::class, "store"]);

        Route::get("experiences/{experience}", [FreelancerExperienceController::class, "show"]);
        Route::put("experiences/{experience}", [FreelancerExperienceController::class, "update"]);
        Route::delete("experiences/{experience}", [FreelancerExperienceController::class, "destroy"]);


        // Skills
        Route::get("skills", [FreelancerSkillController::class, "index"]);
        Route::put("skills", [FreelancerSkillController::class, "update"]);


        // Languages
        Route::get("languages", [FreelancerLanguageController::class, "index"]);
        Route::put("languages", [FreelancerLanguageController::class, "update"]);


    });

Route::middleware(["auth:sanctum", "client"])
    ->prefix("client")
    ->group(function () {

        // Profile
        Route::get("profile", [ClientProfileController::class, "show"]);
        Route::put("profile", [ClientProfileController::class, "update"]);


    });