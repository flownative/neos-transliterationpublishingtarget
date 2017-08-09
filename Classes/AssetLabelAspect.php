<?php
namespace Flownative\TransliterationPublishingTarget;

/*
 * This file is part of the Flownative.TransliterationPublishingTarget package.
 *
 * (c) Flownative GmbH - www.flownative.com
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Behat\Transliterator\Transliterator;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Media\Domain\Model\Asset;
use Neos\Neos\Service\TransliterationService;

/**
 * Adds the aspect of filename transliteration
 * @Flow\Scope("singleton")
 *
 * @Flow\Aspect
 */
class AssetLabelAspect
{
    /**
     * @Flow\Inject
     * @var TransliterationService
     */
    protected $transliterationService;

    /**
     * @Flow\InjectConfiguration(path="resource.targets.localWebDirectoryStaticResourcesTarget.targetOptions.transliterationLanguage", package="Neos.Flow")
     * @var string
     */
    protected $transliterationLanguage;

    /**
     * @param JoinPointInterface $joinPoint The current join point
     * @return string
     * @Flow\Around("method(Neos\Media\Domain\Model\Asset->getLabel())")
     */
    public function useTransliteratedFilenameAsLabelIfTitleIsEmpty(JoinPointInterface $joinPoint)
    {
        $result = $joinPoint->getAdviceChain()->proceed($joinPoint);

        /** @var $proxy Asset */
        $proxy = $joinPoint->getProxy();
        if (empty($proxy->getTitle()) && !empty($proxy->getResource()->getFilename())) {
            $pathInfo = pathinfo($proxy->getResource()->getFilename());
            $extension = (isset($pathInfo['extension']) ? '.' . strtolower($pathInfo['extension']) : '');

            return Transliterator::urlize($this->transliterationService->transliterate($pathInfo['filename'], $this->transliterationLanguage)) . $extension;
        }

        return $result;
    }

    /**
     * @param JoinPointInterface $joinPoint The current join point
     * @return string
     * @Flow\Around("method(Neos\Flow\ResourceManagement\PersistentResource->getFilename())")
     */
    public function transliterateFilenameOfResource(JoinPointInterface $joinPoint)
    {
        $result = $joinPoint->getAdviceChain()->proceed($joinPoint);

        $pathInfo = pathinfo($result);
        $extension = (isset($pathInfo['extension']) ? '.' . strtolower($pathInfo['extension']) : '');

        return Transliterator::urlize($this->transliterationService->transliterate($pathInfo['filename'], $this->transliterationLanguage)) . $extension;
    }
}
