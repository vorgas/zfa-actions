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
 * Handles a GET request on a resource entity
 */
class ApiFetchOne extends AbstractApiAction
{
    /**
     * Prepares the config object for retrieving an entity
     *
     * For some reason, the Apigility ServiceResource does not include a way
     * to move query options into an entity request. So this extracts them
     * directly from the php $_GET object. This means there is no filtering or
     * processing on them!
     *
     * {@inheritDoc}
     * @see \vorgas\ZfaApiActions\ApiActions\AbstractApiAction::convertData()
     */
    public function convertData($data, $id): array
    {
        if (is_null($id)) $id = $data;
        return ['id' => $id, 'params' => $_GET];
    }

    /**
     * Just get the first line from the result set and move into the container
     *
     * {@inheritDoc}
     * @see \vorgas\ZfaApiActions\ApiActions\AbstractApiAction::resultToContainer()
     */
    protected function resultToContainer($result)
    {
        $data = $result->next();
        if (! $data) return false;

        $this->container->exchangeArray($data);
        return $this->container;
    }
}

