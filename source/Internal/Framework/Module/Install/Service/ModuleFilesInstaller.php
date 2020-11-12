<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Framework\Module\Install\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\MetaData\Dao\ModuleConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\DataObject\OxidEshopPackage;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

class ModuleFilesInstaller implements ModuleFilesInstallerInterface
{
    /** @var BasicContextInterface $context */
    private $context;

    /** @var Filesystem $fileSystemService */
    private $fileSystemService;

    /**
     * @var ModuleConfigurationDaoInterface
     */
    private $moduleConfigurationDao;

    public function __construct(
        BasicContextInterface $context,
        Filesystem $fileSystemService,
        ModuleConfigurationDaoInterface $moduleConfigurationData
    ) {
        $this->context = $context;
        $this->fileSystemService = $fileSystemService;
        $this->moduleConfigurationDao = $moduleConfigurationData;
    }

    /**
     * @param OxidEshopPackage $package
     */
    public function install(OxidEshopPackage $package): void
    {
        $this->fileSystemService->symlink(
            Path::join($package->getPackagePath(), 'assets'),
            $this->getModuleAssetsPath($package),
            true
        );
    }

    /**
     * @param OxidEshopPackage $package
     */
    public function uninstall(OxidEshopPackage $package): void
    {
        $this->fileSystemService->remove($this->getModuleAssetsPath($package));
    }

    /**
     * @param OxidEshopPackage $package
     * @return bool
     */
    public function isInstalled(OxidEshopPackage $package): bool
    {
        return is_link($this->getModuleAssetsPath($package));
    }

    private function getModuleAssetsPath(OxidEshopPackage $package): string
    {
        return Path::join(
            $this->context->getOutPath(),
            'modules',
            $this->getModuleId($package),
            'assets'
        );
    }

    private function getModuleId(OxidEshopPackage $package): string
    {
        return $this
            ->moduleConfigurationDao
            ->get($package->getPackagePath())
            ->getId();
    }
}
