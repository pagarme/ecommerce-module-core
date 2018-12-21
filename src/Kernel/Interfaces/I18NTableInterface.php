<?php

namespace Mundipagg\Core\Kernel\Interfaces;

interface I18NTableInterface
{
    /**
     *
     * @param  string $string
     * @return string
     */
    public function get($string);
}