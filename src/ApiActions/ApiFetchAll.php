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


/**
 * Handles a GET method on a resource collection
 */
class ApiFetchAll extends AbstractApiAction
{
    /**
     * Prepares the query object information
     *
     * Without a way to trap sql errors in the DbSelect object, it is necessary
     * to check first for valid sql. So a limit of 1 is sent to the config
     * object, so just one row is processed. If it passes, then the entire
     * Sql object is passed over to the DbSelect paginator.
     *
     * {@inheritDoc}
     * @see \vorgas\ZfaApiActions\ApiActions\AbstractApiAction::convertData()
     */
    public function convertData($data, $id): array
    {
        return ['params' => $data, 'limit' => 1];
    }


    /**
     * Returns a paginator database adapter for handling large data sets
     *
     * {@inheritDoc}
     * @see \vorgas\ZfaApiActions\ApiActions\AbstractApiAction::resultToContainer()
     */
    protected function resultToContainer($result)
    {
        return $this->container;
    }
}

