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

  addUser (username, name, color = null , affiliation = null , role = null) {
    const index = this.users.findIndex(u => u['username'] === username)
    let added = false

    if (index === -1) {
      this.users.push({username: username, name: name, color: color, affiliation: affiliation, role: role})
      added = true
    } else {
      this.users[index]['affiliation'] = affiliation
      this.users[index]['role'] = role
    }

    return added
  }

  removeUser (username) {
    const index = this.users.findIndex(u => u['username'] === username)

    if (index > -1) {
      this.users.splice(index, 1)
    }
  }

  addBannedUser (username, name, color = null) {
    const index = this.bannedUsers.findIndex(u => u['username'] === username)

    if (index === -1) {
      this.bannedUsers.push({username: username, name: name, color: color})
    }
  }

  removeBannedUser (username) {
    const index = this.bannedUsers.findIndex(u => u['username'] === username)

    if (index > -1) {
      this.bannedUsers.splice(index, 1)
    }
  }

  hasUser (username) {
    return this.users.findIndex(u => u['username'] === username) > -1
  }

  hasBannedUser (username) {
    return this.bannedUsers.findIndex(u => u['username'] === username) > -1
  }

  getUserFullName (username) {
    const index = this.users.findIndex(u => u['username'] === username)

    return index > -1 ? this.users[index]['name'] : username
  }

  getBannedUserFullName (username) {
    const index = this.bannedUsers.findIndex(u => u['username'] === username)

    return index > -1 ? this.bannedUsers[index]['name'] : username
  }

  getUserDatas (username) {
    const index = this.users.findIndex(u => u['username'] === username)

    return index > -1 ? this.users[index] : null
  }

  getBannedUserDatas (username) {
    const index = this.bannedUsers.findIndex(u => u['username'] === username)

    return index > -1 ? this.bannedUsers[index] : null
  }

  getUserIndex (username) {
    return this.users.findIndex(u => u['username'] === username)
  }
}
