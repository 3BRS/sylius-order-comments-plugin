const path = require('path');
const Encore = require('@symfony/webpack-encore');

const syliusUiPath = path.resolve(__dirname, '../../vendor/sylius/sylius/src/Sylius/Bundle/UiBundle/Resources/private/js');
const syliusUiResourcesPath = path.resolve(__dirname, '../../vendor/sylius/sylius/src/Sylius/Bundle/UiBundle/Resources/private');

// Admin config
Encore
    .setOutputPath('public/build/admin')
    .setPublicPath('/build/admin')
    .addEntry('admin-entry', path.resolve(__dirname, '../../vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/Resources/private/entry.js'))
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableSassLoader()
    .addAliases({
        'sylius/ui': syliusUiPath,
        'sylius/ui-resources': syliusUiResourcesPath,
    })
;

const adminConfig = Encore.getWebpackConfig();
adminConfig.name = 'admin';
adminConfig.externals = Object.assign({}, adminConfig.externals, { window: 'window', document: 'document' });

Encore.reset();

// Shop config
Encore
    .setOutputPath('public/build/shop')
    .setPublicPath('/build/shop')
    .addEntry('shop-entry', path.resolve(__dirname, '../../vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/Resources/private/entry.js'))
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableSassLoader()
    .addAliases({
        'sylius/ui': syliusUiPath,
        'sylius/ui-resources': syliusUiResourcesPath,
    })
;

const shopConfig = Encore.getWebpackConfig();
shopConfig.name = 'shop';
shopConfig.externals = Object.assign({}, shopConfig.externals, { window: 'window', document: 'document' });

module.exports = [adminConfig, shopConfig];
