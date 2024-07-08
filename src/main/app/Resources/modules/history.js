import {now} from '#/main/app/intl'
import isEmpty from 'lodash/isEmpty'

const HISTORY_SIZE = 5

function getRecent(sort = false) {
  const recent = window.localStorage.getItem('recent')

  if (isEmpty(recent)) {
    return {}
  }

  if (sort) {

  }

  return JSON.parse(recent) || {}
}

function addRecent(id, type, target, name, description, thumbnail) {
  const recent = getRecent()

  // push new element in recent list or update the opening date (will make it appear at the top of the list in ui)
  recent[id] = {
    id: id,
    type: type,
    name: name,
    target: target,
    description: description,
    thumbnail: thumbnail,
    date: now()
  }

  window.localStorage.setItem('recent', JSON.stringify(recent))

  return recent
}

function removeRecent(id) {
  const recent = getRecent()

  delete recent[id]

  window.localStorage.setItem('recent', JSON.stringify(recent))

  return recent
}

export {
  getRecent,
  addRecent,
  removeRecent
}
