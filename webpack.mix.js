let mix = require("laravel-mix");

require("./mix");

mix
    .setPublicPath("dist")
    .js("resources/js/app.js", "js")
    .vue({ version: 3 })
    .sass("resources/sass/app.scss", "css")
    .nova("nova-workflow");
