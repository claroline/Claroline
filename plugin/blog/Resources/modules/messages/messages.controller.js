let _$location = new WeakMap()
let _$rootScope = new WeakMap()

export default class MessagesController {
  constructor(messages, $location, $rootScope) {
    this.messages = messages
    _$location.set(this, $location)
    _$rootScope.set(this, $rootScope)

    this.init()
  }

  closeMessage(index) {
    // The message indicates posts are currently filtered. Removing the message removes the filter too
    if ('filter' in this.messages[index] && this.messages[index].filter) {
      _$location.get(this).url('/')
    }
    
    this.messages.splice(index, 1)
  }

  init() {
    _$rootScope.get(this).$on('$routeChangeSuccess', () => {

      // Filter out already displayed messages
      this.messages.forEach((element, index) => {
        if (!element.keep) {
          this.messages.splice(index, 1)
        } else {
          element.keep = false
        }
      })

    })
  }

}

MessagesController.$inject = [
  'Messages',
  '$location',
  '$rootScope'
]
