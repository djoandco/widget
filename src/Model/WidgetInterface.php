<?php

namespace App\Model;

interface WidgetInterface
{
    public function render($options = []);
}