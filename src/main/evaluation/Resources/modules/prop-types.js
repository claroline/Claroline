import {PropTypes as T} from 'prop-types'

import {User} from '#/main/community/user/prop-types'

const Certificate = {
  propTypes: {
    id: T.string.isRequired,
    obtentionDate: T.string.isRequired,
    issueDate: T.string.isRequired,
    language: T.string.isRequired,
    permissions: T.shape({
      open: T.bool,
      administrate: T.bool
    })
  }
}

const UserEvaluation = {
  propTypes: {
    id: T.string.isRequired,
    meta: T.shape({
      archived: T.bool
    }),
    lastActivityAt: T.string,
    startedAt: T.string,
    endedAt: T.string,
    status: T.string.isRequired,
    user: T.shape(
      User.propTypes
    ),
    progression: T.number,
    rawScore: T.shape({
      current: T.number,
      total: T.number
    }),
    displayScore: T.shape({
      current: T.number,
      total: T.number
    }),
    duration: T.number,
    estimatedDuration: T.number
  }
}

export {
  UserEvaluation,
  Certificate
}
