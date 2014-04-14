module.exports = {
  controllers: {dir: ['./app/Controller']},
  models: {dir: ['./app/Model']},
  lib: {dir: ['./app/Lib']},
  tests: {
    dir: ['./app/Test'],
    options: {
      ignore: 'Selenium'
    }
  },
  view: {
    dir: ['./app/View'],
    options: {
      ignore: 'Themed'
    }
  },
  plugins: {
    dir: ['./app/Plugin'],
    options: {
      ignore: 'Embedly,Geshi,FileUpload,Flattr,Install,Markitup,SaitoHelp/Vendor,Search,SimpleCaptcha,webroot'
    }
  },
  options: {
    standard: 'app/Test/phpcs-ruleset.xml',
    ignore: 'webroot',
    // suppress warnings
    warningSeverity: 8
  }
};
