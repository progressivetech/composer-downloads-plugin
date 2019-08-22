<?php
/**
 * Created by PhpStorm.
 * User: totten
 * Date: 8/21/19
 * Time: 6:31 PM
 */

namespace LastCall\ExtraFiles\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Package\Version\VersionParser;
use LastCall\ExtraFiles\Subpackage;


abstract class BaseHandler
{
    const FAKE_VERSION = 'dev-master';
    const DOT_DIR = '.composer-extra-files';

    /**
     * @var array
     *   File specification from composer.json, with defaults/substitutions applied.
     */
    protected $extraFile;

    /**
     * @var PackageInterface
     */
    protected $parent;

    /**
     * @var Subpackage
     */
    protected $subpackage;

    /**
     * BaseHandler constructor.
     * @param PackageInterface $parent
     * @param array $extraFile
     */
    public function __construct(PackageInterface $parent, $extraFile)
    {
        $this->parent = $parent;
        $this->extraFile = $extraFile;
    }

    public function getSubpackage() {
        if ($this->subpackage === NULL) {
            $this->subpackage = $this->createSubpackage();
        }
        return $this->subpackage;
    }

    /**
     * @return Subpackage
     */
    public function createSubpackage()
    {
        $versionParser = new VersionParser();
        $extraFile = $this->extraFile;
        $parent = $this->parent;

        $package = new Subpackage(
            $parent,
            $extraFile['id'],
            $extraFile['url'],
            NULL,
            $extraFile['path'],
            $parent instanceof RootPackageInterface ? $versionParser->normalize(self::FAKE_VERSION) : $parent->getVersion(),
            $parent instanceof RootPackageInterface ? self::FAKE_VERSION : $parent->getPrettyVersion()
        );

        return $package;
    }

    public function createTrackingData() {
        return [
            'name' => $this->getSubpackage()->getName(),
            'url' => $this->getSubpackage()->getDistUrl(),
        ];
    }

    /**
     * @param string $basePath
     * @return string
     */
    public function getTargetDir($basePath)
    {
        return $basePath . '/' . $this->getSubpackage()->getTargetDir();
    }

    /**
     * @param Composer $composer
     * @param IOInterface $io
     * @param $basePath
     */
    abstract public function download(Composer $composer, IOInterface $io, $basePath);

}