/**
 * Enable ES6 and React compilation through Babel.
 */
module.exports = () => ({
  test: /\.(js|jsx)$/,
  exclude: /public\/packages|node_modules/,
  use: {
    loader: 'babel-loader',
    options: {
      presets: [
        // parse ES6
        ["@babel/preset-env", {
          corejs: "3",
          useBuiltIns: "usage"
        }],
        // parse React JSX
        "@babel/preset-react"
      ],
      plugins: [
        // enable async import() required for webpack dynamic loading
        "@babel/plugin-syntax-dynamic-import"
      ]
    }
  }
})
