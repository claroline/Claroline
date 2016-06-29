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
    this.oldMessages = []
  }

  getMessages () {
    return this.messages
  }

  getOldMessages () {
    return this.oldMessages
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

  emptyMessages () {
    this.messages.splice(0, this.messages.length)
  }

  addOldMessage (name, content, color, type, creationDate) {
    this.oldMessages.push({name: name, content: content, color: color, type: type, creationDate: creationDate})
  }

  emptyOldMessages () {
    this.oldMessages.splice(0, this.oldMessages.length)
  }
}