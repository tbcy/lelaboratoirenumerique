<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Display the home page.
     */
    public function home(): View
    {
        return view('pages.home');
    }

    /**
     * Display the projects page.
     */
    public function projects(): View
    {
        return view('pages.projects');
    }

    /**
     * Display the services page.
     */
    public function services(): View
    {
        return view('pages.services');
    }

    /**
     * Display the about page.
     */
    public function about(): View
    {
        return view('pages.about');
    }

    /**
     * Display the contact page.
     */
    public function contact(): View
    {
        return view('pages.contact');
    }
}
