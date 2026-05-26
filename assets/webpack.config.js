const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'development');
}

Encore
    .setOutputPath('../mytheme/public/build/')
    .setPublicPath('/wp-content/themes/mytheme/public/build')
    .setManifestKeyPrefix('build/')

    .addEntry('admin-login', './admin/login.tsx')
    .addEntry('admin', './admin/router.tsx')
    .addEntry('admin-orderList', './admin/orderList.tsx')
    .addEntry('admin-cookadmin', './admin/menu_table.tsx')

    .copyFiles({ from: './styles/images', to: 'images/[path][name].[ext]' })

    .enableTypeScriptLoader()
    .enableReactPreset()

    .enableLessLoader((options) => {
        options.lessOptions = { javascriptEnabled: true };
    })
    .enableSassLoader()

    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()

module.exports = Encore.getWebpackConfig();
