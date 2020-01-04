const wplib = [
  'blocks',
  'components',
  'date',
  'editor',
  'element',
  'i18n',
  'utils',
  'data',
];

const webpackConfig = {
  entry: './assets/js/block.js',
  output: {
    path: path.resolve(__dirname, 'build'),
    filename: 'formaloo.build.js',
    library: ['wp', '[name]'],
    libraryTarget: 'window',
  },
  externals: wplib.reduce((externals, lib) => {
    externals[`wp.${lib}`] = {
      window: ['wp', lib],
    };

    return externals;
  }, {
    'react': 'React',
    'react-dom': 'ReactDOM',
  }),
}