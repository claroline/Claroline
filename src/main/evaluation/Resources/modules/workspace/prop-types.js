import {PropTypes as T} from 'prop-types'

import {User} from '#/main/community/prop-types'
import {Workspace} from '#/main/core/workspace/prop-types'

const WorkspaceEvaluation = {
  propTypes: {
    id: T.string.isRequired,
    date: T.string,
    status: T.string,
    duration: T.number,
    score: T.number,
    scoreMin: T.number,
    scoreMax: T.number,
    progression: T.number,
    user: T.shape(
      User.propTypes
    ),
    workspace: T.shape(
      Workspace.propTypes
    )
  },
  defaultProps: {

  }
}

export {
  WorkspaceEvaluation
}
