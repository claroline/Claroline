export default class ChatRoom {
  static get CLOSED () {
    return 2
  }

  static get OPEN () {
    return 1
  }

  static get TEXT () {
    return 0
  }

  static get AUDIO () {
    return 1
  }

  static get VIDEO () {
    return 2
  }
}
