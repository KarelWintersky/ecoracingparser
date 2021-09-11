<?php

namespace EcoParser;

use Arris\App;

class API
{
    private $app;

    public function __construct()
    {
        $this->app = App::factory();

    }

    public function about()
    {
        return [
            'version'   =>  '1.0',
            'title'     =>  'GoogleSheets EcoParser API',
        ];
    }

}