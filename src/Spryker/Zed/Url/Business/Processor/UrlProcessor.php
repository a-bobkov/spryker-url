<?php

/**
 * Copyright 2024 Andrey Bobkov - https://github.com/a-bobkov
 * Use of this software requires acceptance of the License Agreement. See LICENSE file in this folder.
 */

namespace Spryker\Zed\Url\Business\Processor;

use Generated\Shared\Transfer\UrlRedirectTransfer;
use Generated\Shared\Transfer\UrlTransfer;
use Orm\Zed\Url\Persistence\SpyUrl;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use Spryker\Zed\Url\Business\Url\UrlActivatorInterface;
use Spryker\Zed\Url\Business\Url\UrlReaderInterface;
use Spryker\Zed\Url\Persistence\UrlQueryContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class UrlProcessor implements UrlProcessorInterface
{
    use TransactionTrait;

    /**
     * @param \Spryker\Zed\Url\Persistence\UrlQueryContainerInterface $urlQueryContainer
     * @param \Spryker\Zed\Url\Business\Url\UrlReaderInterface $urlReader
     * @param \Spryker\Zed\Url\Business\Url\UrlActivatorInterface $urlActivator
     * @param \Spryker\Zed\Url\Business\Processor\UrlRedirectProcessorInterface $urlRedirectProcessor
     */
    public function __construct(
        private UrlQueryContainerInterface $urlQueryContainer,
        private UrlReaderInterface $urlReader,
        private UrlActivatorInterface $urlActivator,
        private UrlRedirectProcessorInterface $urlRedirectProcessor,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function createUrl(UrlTransfer $urlTransfer): UrlTransfer
    {
        return $this->getTransactionHandler()->handleTransaction(
            fn (): UrlTransfer => $this->executeUpsertUrlTransaction($urlTransfer)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function updateUrl(UrlTransfer $urlTransfer): UrlTransfer
    {
        return $this->getTransactionHandler()->handleTransaction(
            fn (): UrlTransfer => $this->executeUpsertUrlTransaction($urlTransfer)
        );
    }

    /**
     * @param \Generated\Shared\Transfer\UrlTransfer $upsertUrlTransfer
     *
     * @return \Generated\Shared\Transfer\UrlTransfer
     */
    protected function executeUpsertUrlTransaction(UrlTransfer $upsertUrlTransfer): UrlTransfer
    {
        $canonicalUrlTransfer = $this->findCanonicalUrl($upsertUrlTransfer);

        if ($canonicalUrlTransfer?->getUrl() === $upsertUrlTransfer->getUrl()) {
            return $canonicalUrlTransfer;
        }

        $existingUrlTransfer = $this->urlReader->findUrlCaseInsensitive($upsertUrlTransfer);

        $newCanonicalUrlTransfer = $existingUrlTransfer
            ? $this->convertRedirectToCanonicalUrl($existingUrlTransfer, $upsertUrlTransfer)
            : $this->createCanonicalUrl($upsertUrlTransfer);

        if ($canonicalUrlTransfer) {
            $this->convertCanonicalUrlToRedirect($canonicalUrlTransfer, $upsertUrlTransfer);

            $this->urlRedirectProcessor->changeSavedUrlRedirectsToUrl(
                $canonicalUrlTransfer->getUrl(),
                $upsertUrlTransfer->getUrl()
            );
        }

        return $newCanonicalUrlTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\UrlTransfer $urlTransfer
     *
     * @return \Generated\Shared\Transfer\UrlTransfer|null
     */
    protected function findCanonicalUrl(
        UrlTransfer $urlTransfer,
    ): ?UrlTransfer
    {
        $spyUrl = (new SpyUrl())->fromArray($urlTransfer->toArray());

        $urlEntity = $this->urlQueryContainer
            ->queryUrlsByResourceTypeAndIds(
                $spyUrl->getResourceType(),
                [$spyUrl->getResourceId()]
            )
            ->filterByFkLocale($spyUrl->getFkLocale())
            ->findOne();

        if ($urlEntity === null) {
            return null;
        }

        return (new UrlTransfer())
            ->fromArray($urlEntity->toArray(), true);
    }

    /**
     * @param \Generated\Shared\Transfer\UrlTransfer $existingUrlTransfer
     * @param \Generated\Shared\Transfer\UrlTransfer $upsertUrlTransfer
     *
     * @return \Generated\Shared\Transfer\UrlTransfer
     */
    protected function convertRedirectToCanonicalUrl(
        UrlTransfer $existingUrlTransfer,
        UrlTransfer $upsertUrlTransfer,
    ): UrlTransfer
    {
        $urlRedirectTransfer = (new UrlRedirectTransfer())
            ->setIdUrlRedirect($existingUrlTransfer->getFkResourceRedirect());

        $urlEntity = $this->urlQueryContainer
            ->queryUrlById($existingUrlTransfer->getIdUrl())
            ->findOne();

        $urlEntity
            ->fromArray($upsertUrlTransfer->toArray())
            ->setIdUrl($existingUrlTransfer->getIdUrl());

        $urlEntity->save();

        $existingUrlTransfer->fromArray($urlEntity->toArray());

        $this->urlActivator->activateUrl($existingUrlTransfer);

        $this->urlRedirectProcessor->deleteUrlRedirect($urlRedirectTransfer);

        return $existingUrlTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\UrlTransfer $urlTransfer
     *
     * @return \Generated\Shared\Transfer\UrlTransfer
     */
    protected function createCanonicalUrl(
        UrlTransfer $urlTransfer,
    ): UrlTransfer
    {
        $urlEntity = (new SpyUrl())
            ->fromArray($urlTransfer->modifiedToArray())
            ->setIdUrl(null);

        $urlEntity->save();

        $urlTransfer->fromArray($urlEntity->toArray());

        $this->urlActivator->activateUrl($urlTransfer);

        return $urlTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\UrlTransfer $existingUrlTransfer
     * @param \Generated\Shared\Transfer\UrlTransfer $upsertUrlTransfer
     *
     * @return void
     */
    protected function convertCanonicalUrlToRedirect(
        UrlTransfer $existingUrlTransfer,
        UrlTransfer $upsertUrlTransfer,
    ): void
    {
        $urlRedirectTransfer = (new UrlRedirectTransfer())
            ->setToUrl($upsertUrlTransfer->getUrl())
            ->setStatus(Response::HTTP_MOVED_PERMANENTLY);

        $urlRedirectTransfer = $this->urlRedirectProcessor
            ->createUrlRedirect($urlRedirectTransfer);

        $urlEntity = $this->urlQueryContainer
            ->queryUrlById($existingUrlTransfer->getIdUrl())
            ->findOne();

        $urlEntity
            ->setResource($urlEntity->getResourceType(), null)
            ->setFkResourceRedirect($urlRedirectTransfer->getIdUrlRedirect());

        $urlEntity->save();

        $existingUrlTransfer->fromArray($urlEntity->toArray(), true);

        $this->urlActivator->activateUrl($existingUrlTransfer);
    }
}
