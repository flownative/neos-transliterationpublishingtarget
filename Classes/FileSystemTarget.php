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
use Neos\Flow\ResourceManagement\ResourceMetaDataInterface;
use Neos\Neos\Service\TransliterationService;

/**
 * A target which supports transliteration for filenames.
 */
class FileSystemTarget extends \Neos\Flow\ResourceManagement\Target\FileSystemTarget
{
    /**
     * @Flow\Inject
     * @var TransliterationService
     */
    protected $transliterationService;

    /**
     * @var string
     */
    protected $transliterationLanguage;

    /**
     * Determines and returns the relative path and filename for the given Storage Object or PersistentResource. If the given
     * object represents a persistent resource, its own relative publication path will be empty. If the given object
     * represents a static resources, it will contain a relative path.
     *
     * No matter which kind of resource, persistent or static, this function will return a sub directory structure
     * if no relative publication path was defined in the given object.
     *
     * @param ResourceMetaDataInterface $object PersistentResource or Storage Object
     * @return string The relative path and filename, for example "c/8/2/8/c828d0f88ce197be1aff7cc2e5e86b1244241ac6/MyPicture.jpg" (if subdivideHashPathSegment is on) or "c828d0f88ce197be1aff7cc2e5e86b1244241ac6/MyPicture.jpg" (if it's off)
     */
    protected function getRelativePublicationPathAndFilename(ResourceMetaDataInterface $object)
    {
        $pathInfo = pathinfo($object->getFilename());
        $extension = (isset($pathInfo['extension']) ? '.' . strtolower($pathInfo['extension']) : '');
        $filename = Transliterator::urlize($this->transliterationService->transliterate($pathInfo['filename'], $this->transliterationLanguage)) . $extension;

        if ($object->getRelativePublicationPath() !== '') {
            $pathAndFilename = $object->getRelativePublicationPath() . $filename;
        } else {
            if ($this->subdivideHashPathSegment) {
                $sha1Hash = $object->getSha1();
                $pathAndFilename = $sha1Hash[0] . '/' . $sha1Hash[1] . '/' . $sha1Hash[2] . '/' . $sha1Hash[3] . '/' . $sha1Hash . '/' . $filename;
            } else {
                $pathAndFilename = $object->getSha1() . '/' . $filename;
            }
        }
        return $pathAndFilename;
    }

    /**
     * Set an option value and return if it was set.
     *
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    protected function setOption($key, $value)
    {
        if ($key === 'transliterationLanguage') {
            $this->transliterationLanguage = (string)$value;
            return true;
        }

        return parent::setOption($key, $value);
    }
}
