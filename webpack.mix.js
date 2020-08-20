let mix = require("laravel-mix");

mix.setPublicPath("dist")
    .js("resources/js/app.js", "js")
    .sass("resources/sass/app.scss", "css");
