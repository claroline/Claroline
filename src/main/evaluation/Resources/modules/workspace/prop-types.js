import {PropTypes as T} from 'prop-types'

import {User} from '#/main/community/prop-types'
import {Workspace} from '#/main/core/workspace/prop-types'

const WorkspaceEvaluation = {
  propTypes: {
    id: T.string.isRequired,
    meta: T.shape({
      archived: T.bool
    }),
    lastActivityAt: T.string,
    startedAt: T.string,
    endedAt: T.string,
    status: T.string,
    duration: T.number,
    displayScore: T.shape({
      current: T.number,
      total: T.number.isRequired
    }),
    rawScore: T.shape({
      current: T.number,
      total: T.number.isRequired
    }),
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
