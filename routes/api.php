<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Profile\ClientProfileController;
use App\Http\Controllers\Profile\FreelancerProfileController;
use App\Http\Controllers\Profile\FreelancerExperienceController;
use App\Http\Controllers\Profile\FreelancerSkillController;
use App\Http\Controllers\Profile\FreelancerLanguageController;
use App\Http\Controllers\Api\Client\JobController as ClientJobController;
use App\Http\Controllers\Api\Client\ProposalController as ClientProposalController;
use App\Http\Controllers\Api\Freelancer\ProposalController;
use App\Http\Controllers\Api\Freelancer\NegotiationController;
use App\Http\Controllers\Api\JobController;

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


        //proposals

        Route::prefix("proposals")->group(function () {
            Route::post("/", [ProposalController::class, "store"]);
            Route::get("/", [ProposalController::class, "index"]);
            Route::get("/{proposal}", [ProposalController::class, "show"]);

            Route::patch('/{proposal}', [
                ProposalController::class,
                'update',
            ]);

            Route::patch('/{proposal}/withdraw', [
                ProposalController::class,
                'withdraw',
            ]);

            // Negotiations
            Route::get('/{proposal}/negotiations', [
                NegotiationController::class,
                'index',
            ]);

            Route::get('/{proposal}/negotiations/{negotiation}', [
                NegotiationController::class,
                'show',
            ]);

            Route::patch('/{proposal}/negotiations/{negotiation}/accept', [
                NegotiationController::class,
                'accept',
            ]);
        });
    });





Route::middleware(["auth:sanctum", "client"])
    ->prefix("client")
    ->group(function () {

        // Profile
        Route::get("profile", [ClientProfileController::class, "show"]);
        Route::put("profile", [ClientProfileController::class, "update"]);


        // Jobs
        Route::get("jobs", [ClientJobController::class, "index"]);
        Route::post("jobs", [ClientJobController::class, "store"]);
        Route::get("jobs/{job}", [ClientJobController::class, "show"]);
        Route::patch("jobs/{job}", [ClientJobController::class, "update"]);
        Route::patch("jobs/{job}/status", [ClientJobController::class, "changeStatus"]);
        Route::delete("jobs/{job}", [ClientJobController::class, "destroy"]);

        //proposals

        Route::get("jobs/{job}/proposals", [ClientProposalController::class, "index"]);
        Route::get("jobs/{job}/proposals/{proposal}", [ClientProposalController::class, "show"]);
        Route::post("jobs/{job}/proposals/{proposal}/negotiations", [ClientProposalController::class, "storeNegotiation"]);
    });


// Public job discovery (any authenticated user)
Route::middleware("auth:sanctum")->group(function () {
    Route::get("jobs", [JobController::class, "index"]);
    Route::get("jobs/{job}", [JobController::class, "show"]);
});
