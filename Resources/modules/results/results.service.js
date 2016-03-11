export default class ResultsService {
  constructor ($http) {
    this.$http = $http;
  }

  getResults () {
    if (!window.resultMarks) {
      throw new Error(
        'Expected marks to be exposed in a window.resultMarks variable'
      );
    }

    return window.resultMarks

    //return [
    //  { name: 'John Doe', mark: '12' },
    //  { name: 'Lisa Boom', mark: '9' },
    //  { name: 'Mark Foo', mark: '14' }
    //]
  }

  getUsers () {
    if (!window.workspaceUsers) {
      throw new Error(
        'Expected users to be exposed in a window.workspaceUsers variable'
      );
    }

    //return window.workspaceUsers

    return [
      { id: 1, name: 'John Doe' },
      { id: 2, name: 'Lisa Boom' }
    ]
  }
}
