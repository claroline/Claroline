import {PropTypes as T} from 'prop-types'

import {User} from '#/main/core/user/prop-types'
import {ResourceNode} from '#/main/core/resource/prop-types'

const ResourceUserEvaluation = {
  propTypes: {
    id: T.number.isRequired,
    userName: T.string.isRequired,
    date: T.string.isRequired,
    status: T.string,
    duration: T.number,
    score: T.number,
    scoreMin: T.number,
    scoreMax: T.number,
    customScore: T.string,
    user: T.shape(
      User.propTypes
    ),
    resourceNode: T.shape(
      ResourceNode.propTypes
    )
  }
}

export {
  ResourceUserEvaluation
}
