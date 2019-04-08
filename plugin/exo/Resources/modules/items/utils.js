import {makeId} from '#/main/core/scaffolding/id'

function emptyAnswer() {
  return {
    id: makeId(),
    type: 'text/html',
    data: ''
  }
}

export {
  emptyAnswer
}
