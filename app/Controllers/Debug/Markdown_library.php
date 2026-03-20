<?php

namespace App\Controllers\Debug;

use App\Libraries\Markdown;

class Markdown_library extends BaseController
{
    public function convert_to_html()
    {
        // Get the class name
        $class         = str_replace(__NAMESPACE__, '', static::class);
        $data['class'] = ltrim($class, '\\');
        // Store the function name
        $data['function'] = __FUNCTION__;

        // Create an instance of the Markdown library
        $markdown = new Markdown();
        // Set the markdown to convert
        $markdown->setMarkdown("# Hello World\nThis is a test of the Markdown library.");
        // Get the html output
        $result = $markdown->convert();

        $data['dump'] = $result;

        $data['js']    = ['debug/debug'];
        $data['css']   = [];
        $data['title'] = 'Debug';

        return view('debug/default', $data);
    }
}
