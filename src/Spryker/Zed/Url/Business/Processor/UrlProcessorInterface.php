<?php

/**
 * Copyright 2024 Andrey Bobkov - https://github.com/a-bobkov
 * Use of this software requires acceptance of the License Agreement. See LICENSE file in this folder.
 */

namespace Spryker\Zed\Url\Business\Processor;

use Generated\Shared\Transfer\UrlTransfer;

interface UrlProcessorInterface
{
    /**
     * @param \Generated\Shared\Transfer\UrlTransfer $urlTransfer
     *
     * @return \Generated\Shared\Transfer\UrlTransfer
     */
    public function createUrl(
        UrlTransfer $urlTransfer,
    ): UrlTransfer;

    /**
     * @param \Generated\Shared\Transfer\UrlTransfer $urlTransfer
     *
     * @return \Generated\Shared\Transfer\UrlTransfer
     */
    public function updateUrl(
        UrlTransfer $urlTransfer,
    ): UrlTransfer;
}
