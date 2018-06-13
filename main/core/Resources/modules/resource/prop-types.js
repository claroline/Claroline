import {PropTypes as T} from 'prop-types'

import {ResourceNode} from '#/main/core/resource/data/types/resource/prop-types'

const ResourceType = {
  propTypes: {
    name: T.string.isRequired,
    class: T.string.isRequired,
    actions: T.arrayOf(T.shape({
      name: T.string.isRequired,
      scope: T.arrayOf(T.oneOf(['object', 'collection'])),
      group: T.string,
      permission: T.string.isRequired
    }))
  },
  defaultProps: {
    actions: []
  }
}

const UserEvaluation = {
  propTypes: {
    id: T.number.isRequired,
    date: T.string,
    status: T.string.isRequired,
    duration: T.number,
    score: T.number,
    scoreMin: T.number,
    scoreMax: T.number,
    progression: T.number
  }
}

export {
  ResourceType,
  ResourceNode, // for retro compatibility
  UserEvaluation
}
