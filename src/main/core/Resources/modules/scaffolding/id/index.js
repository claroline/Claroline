import {v4 as uuid} from 'uuid'

function makeId() {
  return uuid()
}

export {
  makeId
}
