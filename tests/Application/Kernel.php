<?php

declare(strict_types=1);

namespace Tests\MangoSylius\OrderCommentsPlugin\Application;

use Composer\InstalledVersions;
use PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Webmozart\Assert\Assert;

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

    protected function getContainerClass()
    {
        return 'testContainer';
    }

    protected function getContainerLoader(ContainerInterface $container): LoaderInterface
    {
        /** @var ContainerBuilder $container */
        Assert::isInstanceOf($container, ContainerBuilder::class);

        $locator = new FileLocator($this, $this->getRootDir() . '/Resources');
        $resolver = new LoaderResolver([
            new XmlFileLoader($container, $locator),
            new YamlFileLoader($container, $locator),
            new IniFileLoader($container, $locator),
            new PhpFileLoader($container, $locator),
            new GlobFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
            new ClosureLoader($container),
        ]);

        return new DelegatingLoader($resolver);
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
        $candidates = [];

        $syliusVersion = $this->detectPackageMajorMinor('sylius/sylius');
        if ($syliusVersion !== null) {
            $candidates[] = $confDir . '/packages/sylius/' . $syliusVersion;
        }

        $symfonyMajor = $this->detectPackageMajor('symfony/framework-bundle');
        if ($symfonyMajor !== null) {
            $candidates[] = $confDir . '/packages/symfony/' . $symfonyMajor;
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
