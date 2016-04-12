/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class UserService {
  constructor () {
    this.users = []
    this.bannedUsers = []
  }

  getUsers () {
    return this.users
  }

  getBannedUsers () {
    return this.bannedUsers
  }

  addUser (username, name, color = null, affiliation = null, role = null) {
    const index = this.users.findIndex(u => u['username'] === username)

    if (index === -1) {
      this.users.push({username: username, name: name, color: color, affiliation: affiliation, role: role})
    } else {
      this.users[index]['affiliation'] = affiliation
      this.users[index]['role'] = role
    }
  }

  removeUser (username, statusCode = 0) {
    const index = this.users.findIndex(u => u['username'] === username)

    if (index > -1) {
      this.users.splice(index, 1)
    }
  }

  addBannedUser (username) {
    const index = this.bannedUsers.findIndex(u => u === username)

    if (index === -1) {
      this.bannedUsers.push(username)
    }
  }

  removeBannedUser (username) {
    const index = this.bannedUsers.findIndex(u => u === username)

    if (index > -1) {
      this.bannedUsers.splice(index, 1)
    }
  }

  hasUser (username) {
    return this.users.findIndex(u => u['username'] === username) > -1
  }

  getUserFullName (username) {
    const index = this.users.findIndex(u => u['username'] === username)

    return index > -1 ? this.users[index]['name'] : username
  }
}