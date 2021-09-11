<?php

namespace EcoParser;

/**
 * @param $data
 */
function say($data)
{
    \Arris\App::factory()->set('json', $data);
}