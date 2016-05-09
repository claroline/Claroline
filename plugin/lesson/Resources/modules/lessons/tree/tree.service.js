let _restService = new WeakMap()

export default class TreeService {
  
  constructor(restService) {
    _restService.set(this, restService)
    this.data = {}
  }

  refresh(lesson) {
    _restService.get(this).getTree(lesson)
    .then(
      response => {
        // Store the response in the object itself
        this.data = response
      }
    )
  }
}

TreeService.$inject = [
  'restService'
]