<?php
/**
 * Zend Framework 3 interaction library
 *
 * This file is part of a suite of software to ease interaction with ZF3,
 * particularly Apigility.
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2017 Mike Hill
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace vorgas\ZfaActions;

use Zend\Hydrator\Reflection;

/**
 * Allows entity declaration of properties only
 *
 * By implementing this class an apiglity resource entity only has to declare
 * public properties. These are then hydrated automatically.
 */
abstract class AbstractReflectionEntity
{
    protected $hydrator;
    public function __construct()
    {
        $this->hydrator = new Reflection();
    }


    /**
     * Extracts the property values and returns them as an array
     */
    public function getArrayCopy()
    {
        $array = $this->hydrator->extract($this);
        unset($array['hydrator']);
        return $array;
    }


    /**
     * Hydrates the object from an array of information
     *
     * @param array $array
     */
    public function exchangeArray(array $array)
    {
        $this->hydrator->hydrate($array, $this);
    }
}

