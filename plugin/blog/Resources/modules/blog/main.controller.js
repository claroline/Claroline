let _$location = new WeakMap()

export default class MainController {
  constructor(blogService, $location) {

    this.blog = blogService
    _$location.set(this, $location)

  }

  goToHome() {
    _$location.get(this).url('/')
  }

  newPost() {
    _$location.get(this).url('/post/new')
  }

  displayOptions() {
    _$location.get(this).url('/configure')
  }
}

MainController.$inject = [
  'blogService',
  '$location'
]