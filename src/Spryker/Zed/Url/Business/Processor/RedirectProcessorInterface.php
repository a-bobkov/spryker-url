<?php

/**
 * Copyright 2024 Andrey Bobkov - https://github.com/a-bobkov
 * Use of this software requires acceptance of the License Agreement. See LICENSE file in this folder.
 */

namespace Spryker\Zed\Url\Business\Processor;

use Generated\Shared\Transfer\UrlRedirectTransfer;

interface RedirectProcessorInterface
{
    /**
     * @param \Generated\Shared\Transfer\UrlRedirectTransfer $urlTransfer
     *
     * @return \Generated\Shared\Transfer\UrlRedirectTransfer
     */
    public function createRedirect(
        UrlRedirectTransfer $urlRedirectTransfer
    ): UrlRedirectTransfer;

    /**
     * @param \Generated\Shared\Transfer\UrlRedirectTransfer $urlTransfer
     *
     * @return \Generated\Shared\Transfer\UrlRedirectTransfer
     */
    public function updateRedirect(
        UrlRedirectTransfer $urlRedirectTransfer
    ): UrlRedirectTransfer;
}
