<?php

namespace App\Http\Controllers\Public;

use App\Domain\Content\Actions\GetLandingPageContent;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(GetLandingPageContent $getLandingPageContent): Response
    {
        return Inertia::render('welcome', $getLandingPageContent->handle());
    }
}
