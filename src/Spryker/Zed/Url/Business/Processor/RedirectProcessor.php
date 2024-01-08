<?php

/**
 * Copyright 2024 Andrey Bobkov - https://github.com/a-bobkov
 * Use of this software requires acceptance of the License Agreement. See LICENSE file in this folder.
 */

namespace Spryker\Zed\Url\Business\Processor;

use Generated\Shared\Transfer\UrlRedirectTransfer;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class RedirectProcessor implements RedirectProcessorInterface
{
    use TransactionTrait;

    /**
     * @param \Spryker\Zed\Url\Business\Processor\UrlProcessorInterface $urlProcessor
     * @param \Spryker\Zed\Url\Business\Processor\UrlRedirectProcessorInterface $urlRedirectProcessor
     */
    public function __construct(
        private UrlProcessorInterface $urlProcessor,
        private UrlRedirectProcessorInterface $urlRedirectProcessor,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function createRedirect(UrlRedirectTransfer $urlRedirectTransfer): UrlRedirectTransfer
    {
        return $this->getTransactionHandler()->handleTransaction(
            fn (): UrlRedirectTransfer => $this->executeCreateRedirectTransaction($urlRedirectTransfer)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function updateRedirect(UrlRedirectTransfer $urlRedirectTransfer): UrlRedirectTransfer
    {
        return $this->getTransactionHandler()->handleTransaction(
            fn (): UrlRedirectTransfer => $this->executeUpdateRedirectTransaction($urlRedirectTransfer)
        );
    }

    /**
     * @param \Generated\Shared\Transfer\UrlRedirectTransfer $urlRedirectTransfer
     * *
     * * @return \Generated\Shared\Transfer\UrlRedirectTransfer
     */
    protected function executeCreateRedirectTransaction(
        UrlRedirectTransfer $urlRedirectTransfer
    ): UrlRedirectTransfer
    {
        $urlRedirectTransfer = $this->urlRedirectProcessor->createUrlRedirect($urlRedirectTransfer);

        $sourceUrlTransfer = $this->urlProcessor->createUrl($urlRedirectTransfer->getSource());

        $urlRedirectTransfer->setSource($sourceUrlTransfer);

        $this->urlRedirectProcessor->changeSavedUrlRedirectsToUrl(
            $urlRedirectTransfer->getSource()->getUrl(),
            $urlRedirectTransfer->getToUrl()
        );

        return $urlRedirectTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\UrlRedirectTransfer $urlRedirectTransfer
     * *
     * * @return \Generated\Shared\Transfer\UrlRedirectTransfer
     */
    protected function executeUpdateRedirectTransaction(
        UrlRedirectTransfer $urlRedirectTransfer
    ): UrlRedirectTransfer
    {
        $urlRedirectTransfer = $this->urlRedirectProcessor->updateUrlRedirect($urlRedirectTransfer);

        $sourceUrlTransfer = $this->urlProcessor->updateUrl($urlRedirectTransfer->getSource());

        $urlRedirectTransfer->setSource($sourceUrlTransfer);

        $this->urlRedirectProcessor->changeSavedUrlRedirectsToUrl(
            $urlRedirectTransfer->getSource()->getUrl(),
            $urlRedirectTransfer->getToUrl()
        );

        return $urlRedirectTransfer;
    }
}
