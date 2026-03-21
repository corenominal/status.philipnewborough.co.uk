<?php

namespace App\Controllers;

class About extends BaseController
{
    public function index(): string
    {
        return view('about', [
            'title' => 'About',
            'css'   => ['about'],
            'js'    => ['shared/network-animation'],
        ]);
    }
}
