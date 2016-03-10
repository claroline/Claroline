export default class ResultsService {
  constructor($http) {
    this.$http = $http;
  }

  fetch() {
    return [
      { name: 'John Doe', mark: '12' },
      { name: 'Lisa Boom', mark: '9' },
      { name: 'Mark Foo', mark: '14' }
    ]
  }
}
