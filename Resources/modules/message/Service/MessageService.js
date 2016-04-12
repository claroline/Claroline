/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class MessageService {
  constructor () {
    this.messages = []
  }

  getMessages () {
    return this.messages
  }

  addMessage (sender, message, color) {
    this.messages.push({sender: sender, message: message, color: color, type: 'message'})
  }

  addPresenceMessage (name, status) {
    this.messages.push({name: name, status: status, type: 'presence'})
  }

  addRawMessage (message) {
    this.messages.push({message: message, type: 'raw'})
  }
}