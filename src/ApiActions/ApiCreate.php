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

namespace vorgas\ZfaActions\ApiActions;


/**
 * Handles sql execution and return for an Apigility collection POST action
 *
 * A POST action on a collection is mapped to the ServiceResource::create()
 * method. The data is an array of values for creation of the resource.
 */
class ApiCreate extends AbstractApiAction
{
    /**
     * Supplied data is values to send to the INSERT command
     *
     * Puts $data into into an object that can be inserted into a config array
     *
     * {@inheritDoc}
     * @see \vorgas\ZfaActions\ApiActions\AbstractApiAction::convertData()
     */
    public function convertData($data, $id): array
    {
        return ['values' => $data];
    }


    /**
     * Returns an entity based on the input information
     *
     * This pulls information from the config array to pass to the entity. It
     * isn't validated against the database first. If the INSERT command had
     * failed, this wouldn't even be called.
     *
     * {@inheritDoc}
     * @see \vorgas\ZfaActions\ApiActions\AbstractApiAction::resultToContainer()
     */
    protected function resultToContainer($result)
    {
        // If the id is auto generated, then just return it
        $newId = $result->getGeneratedValue();
        if ($newId) return ['id' => $newId];
        
        // Return the entire result entity
        /* It is difficult to know which column is the primary entity identifier, so
            just return the entire entity */
        return;
    }
}
