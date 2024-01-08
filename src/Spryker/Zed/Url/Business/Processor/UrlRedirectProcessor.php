<?php

/**
 * Copyright 2024 Andrey Bobkov - https://github.com/a-bobkov
 * Use of this software requires acceptance of the License Agreement. See LICENSE file in this folder.
 */

namespace Spryker\Zed\Url\Business\Processor;

use Generated\Shared\Transfer\UrlRedirectTransfer;
use Orm\Zed\Url\Persistence\SpyUrlRedirect;
use Spryker\Zed\Url\Business\Redirect\UrlRedirectActivatorInterface;
use Spryker\Zed\Url\Persistence\UrlQueryContainerInterface;

class UrlRedirectProcessor implements UrlRedirectProcessorInterface
{
    /**
     * @param \Spryker\Zed\Url\Persistence\UrlQueryContainerInterface $urlQueryContainer
     * @param \Spryker\Zed\Url\Business\Redirect\UrlRedirectActivatorInterface $urlRedirectActivator
     */
    public function __construct(
        private UrlQueryContainerInterface $urlQueryContainer,
        private UrlRedirectActivatorInterface $urlRedirectActivator,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function createUrlRedirect(
        UrlRedirectTransfer $urlRedirectTransfer
    ): UrlRedirectTransfer
    {
        $urlRedirectTransfer = $this->avoidUrlRedirectChain($urlRedirectTransfer);

        $urlRedirectEntity = (new SpyUrlRedirect())
            ->fromArray($urlRedirectTransfer->toArray());

        $urlRedirectEntity->save();

        $urlRedirectTransfer->fromArray($urlRedirectEntity->toArray(), true);

        $urlRedirectTransfer->getSource()?->setFkResourceRedirect($urlRedirectTransfer->getIdUrlRedirect());

        $this->urlRedirectActivator->activateUrlRedirect($urlRedirectTransfer);

        return $urlRedirectTransfer;
    }

    /**
     * {@inheritDoc}
     */
    public function updateUrlRedirect(
        UrlRedirectTransfer $urlRedirectTransfer
    ): UrlRedirectTransfer
    {
        $urlRedirectTransfer = $this->avoidUrlRedirectChain($urlRedirectTransfer);

        $urlRedirectEntity = $this->urlQueryContainer
            ->queryRedirectById($urlRedirectTransfer->getIdUrlRedirect())
            ->findOne();

        $urlRedirectEntity->fromArray($urlRedirectTransfer->modifiedToArray());

        $urlRedirectEntity->save();

        $urlRedirectTransfer->fromArray($urlRedirectEntity->toArray(), true);

        $urlRedirectTransfer->getSource()?->setFkResourceRedirect($urlRedirectTransfer->getIdUrlRedirect());

        $this->urlRedirectActivator->activateUrlRedirect($urlRedirectTransfer);

        return $urlRedirectTransfer;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUrlRedirect(
        UrlRedirectTransfer $urlRedirectTransfer
    ): void
    {
        $redirectUrlEntity = $this->urlQueryContainer
            ->queryRedirectById($urlRedirectTransfer->getIdUrlRedirect())
            ->findOne();

        if ($redirectUrlEntity) {
            $redirectUrlEntity->delete();
        }

        $this->urlRedirectActivator->deactivateUrlRedirect($urlRedirectTransfer);
    }

    /**
     * {@inheritDoc}
     */
    public function changeSavedUrlRedirectsToUrl(
        string $currentToUrl,
        string $newToUrl
    ): void
    {
        $urlRedirectEntities = $this->urlQueryContainer
            ->queryRedirects()
            ->findByToUrl($currentToUrl);

        foreach ($urlRedirectEntities as $urlRedirectEntity) {
            $urlRedirectEntity->setToUrl($newToUrl);

            $urlRedirectEntity->save();

            $urlRedirectTransfer = (new UrlRedirectTransfer())
                ->fromArray($urlRedirectEntity->toArray(), true);

            $this->urlRedirectActivator->activateUrlRedirect($urlRedirectTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\UrlRedirectTransfer $urlRedirectTransfer
     *
     * @return \Generated\Shared\Transfer\UrlRedirectTransfer
     */
    protected function avoidUrlRedirectChain(
        UrlRedirectTransfer $urlRedirectTransfer
    ): UrlRedirectTransfer
    {
        $targetUrlRedirectEntity = $this->urlQueryContainer
            ->queryUrlRedirectBySourceUrl($urlRedirectTransfer->getToUrl())
            ->findOne();

        if ($targetUrlRedirectEntity) {
            $urlRedirectTransfer->setToUrl($targetUrlRedirectEntity->getToUrl());
        }

        return $urlRedirectTransfer;
    }
}
