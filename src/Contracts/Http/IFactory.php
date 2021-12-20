<?php

namespace Laravel\Http\Contracts\Http;

interface IFactory
{
    public function with($name = null);
}