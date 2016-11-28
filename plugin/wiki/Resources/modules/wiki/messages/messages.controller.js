export default class MessagesController {
  constructor(messages) {
    this.messages = messages
    this.dismissTimeOut = 5000
  }

  closeMessage(index) {
    this.messages.splice(index, 1)
  }

}

MessagesController.$inject = [
  'Messages'
]
