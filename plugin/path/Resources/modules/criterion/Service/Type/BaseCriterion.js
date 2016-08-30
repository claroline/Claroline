
export default class BaseCriterion {
  constructor($log, $q, $http, Translator, url) {
    this.$log = $log
    this.$q = $q
    this.$http = $http
    this.Translator = Translator
    this.UrlGenerator = url
  }

  test() {
    this.$log.error('A criterion must implement the `test(dataToTest)` method.')
  }
}