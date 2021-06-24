console.log('in file cypress/plugins/index.js')

module.exports = (on) => {
  console.log('loading cypress-terminal-report')
  require('cypress-terminal-report/src/installLogsPrinter')(on);
};
