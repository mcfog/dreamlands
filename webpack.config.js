const path = require('path');
const ExtractTextPlugin = require("extract-text-webpack-plugin");
const CopyWebpackPlugin = require('copy-webpack-plugin');

const extractSass = new ExtractTextPlugin({
    filename: "css/[name].css",
    disable: false
});

module.exports = {
    entry: [
        './frontend/css/style.scss',
        './frontend/js/main.js'
    ],
    output: {
        filename: 'js/[name].js',
        path: path.resolve(__dirname, "public/assets"),
        publicPath: '/assets'
    },
    module: {
        rules: [
            {
                test: /\.scss$/,
                loader: extractSass.extract({
                    use: [{
                        loader: "css-loader"
                    }, {
                        loader: "sass-loader"
                    }],
                    // use style-loader in development
                    fallback: "style-loader"
                })
            },
            {
                test: /\.dot\.html$/,
                loader: 'dot-loader'
            }
        ]
    },
    resolve: {
        modules: [path.resolve(__dirname, "frontend/js"), "node_modules"]
    },
    plugins: [
        extractSass,
        new CopyWebpackPlugin([{from: './frontend/image', 'to': 'image'}])
    ]
};
