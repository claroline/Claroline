
export default class BaseCriterion {
  constructor($log, $q, $http) {
    this.$log = $log
    this.$q = $q
    this.$http = $http
  }

  test(dataToTest) {
    this.$log.error('A criterion must implement the `test(dataToTest)` method.')
  }
}