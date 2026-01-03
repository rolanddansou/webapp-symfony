const Encore = require('@symfony/webpack-encore');
const path = require("path");

// Manually configure the runtime environment if not already configured yet by the "encore" command.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .addAliases({
        '@': path.resolve('assets/js'),
    })
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')

    /*
     * ENTRY CONFIG
     */
    // Main app entry (keep existing Asset Mapper functionality)
    .addEntry('app', './assets/js/app.js')

    // Admin dashboard entry (NEW)
    .addEntry('admin', './assets/admin/admin.js')

    // Enable single runtime chunk
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // Enable .vue file support
    .enableVueLoader(() => {}, {
        version: 3,
        runtimeCompilerBuild: false
    })

    // Enable Sass/SCSS support
    .enableSassLoader()

    // Enable PostCSS
    .enablePostCssLoader()
;

module.exports = Encore.getWebpackConfig();
