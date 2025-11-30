const path = require("path");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const WebpackRemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');

module.exports = {
  mode: "production",
  entry: {
    main: "./assets/src/js/main.js",
    calendar: "./assets/src/js/calendar.js",
    style: "./assets/src/scss/style.scss",
  },
  devtool: "source-map",
  output: {
    filename: "js/[contenthash].js",
    path: path.resolve(__dirname, "./assets/dist"),
    clean: true,
  },
  module: {
    rules: [
      {
        test: /.scss$/,
        use: [MiniCssExtractPlugin.loader, "css-loader", "sass-loader"],
      },
      {
        test: /\.(png|svg|jpg|jpeg|gif|webp)$/i,
        type: 'asset/resource',
        generator: {
          filename: 'img/[contenthash][ext]'
        },
      },
    ],
  },
  optimization: {
    minimizer: [
      // For webpack@5 you can use the `...` syntax to extend existing minimizers (i.e. `terser-webpack-plugin`), uncomment the next line
      `...`,
      new CssMinimizerPlugin(),
    ],
  },
  plugins: [
    new MiniCssExtractPlugin({ filename: "css/[contenthash].css"}),
    new WebpackRemoveEmptyScriptsPlugin(),
    new WebpackManifestPlugin({
      fileName: 'manifest.json',
      publicPath: '/assets/dist/',
    }),
  ]
}