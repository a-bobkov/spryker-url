<?php

/**
 * Copyright 2024 Andrey Bobkov - https://github.com/a-bobkov
 * Use of this software requires acceptance of the License Agreement. See LICENSE file in this folder.
 */

namespace Spryker\Zed\Url\Business\Processor;

use Generated\Shared\Transfer\UrlRedirectTransfer;

interface UrlRedirectProcessorInterface
{
    /**
     * @param \Generated\Shared\Transfer\UrlRedirectTransfer $urlTransfer
     *
     * @return \Generated\Shared\Transfer\UrlRedirectTransfer
     */
    public function createUrlRedirect(
        UrlRedirectTransfer $urlRedirectTransfer,
    ): UrlRedirectTransfer;

    /**
     * @param \Generated\Shared\Transfer\UrlRedirectTransfer $urlTransfer
     *
     * @return \Generated\Shared\Transfer\UrlRedirectTransfer
     */
    public function updateUrlRedirect(
        UrlRedirectTransfer $urlRedirectTransfer,
    ): UrlRedirectTransfer;

    /**
     * @param \Generated\Shared\Transfer\UrlRedirectTransfer $urlTransfer
     *
     * @return void
     */
    public function deleteUrlRedirect(
        UrlRedirectTransfer $urlRedirectTransfer,
    ): void;

    /**
     * @param string $currentToUrl
     * @param string $newToUrl
     *
     * @return void
     */
    public function changeSavedUrlRedirectsToUrl(
        string $currentToUrl,
        string $newToUrl,
    ): void;
}
