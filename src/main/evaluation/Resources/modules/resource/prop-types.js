import {PropTypes as T} from 'prop-types'
import {User} from '#/main/core/user/prop-types'
import {ResourceNode} from '#/main/core/resource/prop-types'

const ResourceUserEvaluation = {
  propTypes: {
    id: T.number.isRequired,
    date: T.string.isRequired,
    status: T.string.isRequired,
    duration: T.number,
    score: T.number,
    scoreMin: T.number,
    scoreMax: T.number,
    progression: T.number,
    progressionMin: T.number,
    progressionMax: T.number,
    resourceNode: T.shape(
      ResourceNode.propTypes
    ),
    user: T.shape(
      User.propTypes
    ),
    nbAttempts: T.number,
    nbOpenings: T.number,
    required: T.bool
  },
  defaultProps: {
    nbAttempts: 0,
    nbOpenings: 0,
    required: false
  }
}

export {
  ResourceUserEvaluation
}

