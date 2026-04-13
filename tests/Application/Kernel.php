<?php

declare(strict_types=1);

namespace Tests\MangoSylius\OrderCommentsPlugin\Application;

use Composer\InstalledVersions;
use PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log';
    }

    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir() . '/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->addResource(new FileResource($this->getProjectDir() . '/config/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir() . '/config';

        // Common configs
        $loader->load($confDir . '/{packages}/*' . self::CONFIG_EXTS, 'glob');

        // Version-specific configs — loaded only when the installed Sylius / Symfony
        // version matches the subdirectory name (e.g. packages/sylius/1.9,
        // packages/symfony/4). Lets us ship per-version overrides without breaking
        // other versions.
        foreach ($this->getVersionSpecificConfigDirs($confDir) as $dir) {
            $loader->load($dir . '/*' . self::CONFIG_EXTS, 'glob');
        }

        // Environment-specific configs
        $loader->load($confDir . '/{packages}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}_' . $this->environment . self::CONFIG_EXTS, 'glob');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = $this->getProjectDir() . '/config';

        $routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS, '/', 'glob');

        // Version-specific routes (e.g. routes/sylius/1.9, routes/symfony/5)
        foreach ($this->getVersionSpecificRouteDirs($confDir) as $dir) {
            $routes->import($dir . '/*' . self::CONFIG_EXTS, '/', 'glob');
        }

        $routes->import($confDir . '/{routes}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}' . self::CONFIG_EXTS, '/', 'glob');
    }

    protected function getContainerBaseClass(): string
    {
        if ($this->isTestEnvironment()) {
            return MockerContainer::class;
        }

        return parent::getContainerBaseClass();
    }

    private function isTestEnvironment(): bool
    {
        return 0 === strpos($this->getEnvironment(), 'test');
    }

    /**
     * @return iterable<string>
     */
    private function getVersionSpecificConfigDirs(string $confDir): iterable
    {
        yield from $this->getVersionSpecificDirs($confDir . '/packages');
    }

    /**
     * @return iterable<string>
     */
    private function getVersionSpecificRouteDirs(string $confDir): iterable
    {
        yield from $this->getVersionSpecificDirs($confDir . '/routes');
    }

    /**
     * @return iterable<string>
     */
    private function getVersionSpecificDirs(string $baseDir): iterable
    {
        $candidates = [];

        $syliusVersion = $this->detectPackageMajorMinor('sylius/sylius');
        if ($syliusVersion !== null) {
            $candidates[] = $baseDir . '/sylius/' . $syliusVersion;
        }

        $symfonyMajor = $this->detectPackageMajor('symfony/framework-bundle');
        if ($symfonyMajor !== null) {
            $candidates[] = $baseDir . '/symfony/' . $symfonyMajor;
        }

        foreach ($candidates as $dir) {
            if (is_dir($dir)) {
                yield $dir;
            }
        }
    }

    private function detectPackageMajorMinor(string $package): ?string
    {
        $version = $this->getPackageVersion($package);
        if ($version !== null && preg_match('/^v?(\d+\.\d+)/', $version, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    private function detectPackageMajor(string $package): ?string
    {
        $version = $this->getPackageVersion($package);
        if ($version !== null && preg_match('/^v?(\d+)/', $version, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    private function getPackageVersion(string $package): ?string
    {
        if (!class_exists(InstalledVersions::class)) {
            return null;
        }

        if (!InstalledVersions::isInstalled($package)) {
            return null;
        }

        return InstalledVersions::getPrettyVersion($package);
    }
}
