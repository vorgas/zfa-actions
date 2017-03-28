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

namespace vorgas\ZfaApiActions\ApiActions;

use Zend\Db\Adapter\AdapterInterface;

/**
 */
interface ApiActionFactoryInterface
{
    /**
     * Creates the appropriate api action class, initializes and returns it
     * The basic flow here is as follows:
     *  - Split up the method name to get the necessary parts for processing
     *  - Create the ApiAction class based on the method name
     *  - Convert GET parameters and other info into a valid data structure
     *  - Read in the appropriate config array from the apiactions file
     *  - Move the info from the data structure into the config array
     *  - Clean up the config array, removing unnecessary entries
     *  - Tell the ApiAction class to construct the sql object
     *  - return the ApiAction class, all primed and ready to go!
     *
     * @param AdapterInterface $adapter     Database adapter
     * @param string $method                The calling method. Just use __METHOD__
     * @param mixed $data                   Any additional data
     * @param mixed $id                     Used for patch, etc where an $id is also provided
     * @return AbstractApiAction class
     */
    public function build(AdapterInterface $adapter, string $method, $data, $id = null): ApiActionInterface;
}

