const path = require('path');

module.exports = {
  entry: {
    main: './src/react/index.js'
  },
  output: {
    path: path.resolve(__dirname, 'public/js'),
    filename: '[name].bundle.js',
    clean: true
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              '@babel/preset-env',
              '@babel/preset-react'
            ]
          }
        }
      }
    ]
  },
  resolve: {
    extensions: ['.js', '.jsx'],
    alias: {
      '@': path.resolve(__dirname, './src/react'),
      '@/components': path.resolve(__dirname, './src/react/components'),
      '@/lib': path.resolve(__dirname, './src/react/lib'),
      '@/utils': path.resolve(__dirname, './src/react/lib/utils')
    }
  },
  devtool: 'source-map'
};