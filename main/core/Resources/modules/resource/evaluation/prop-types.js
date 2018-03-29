import {PropTypes as T} from 'prop-types'

const UserEvaluation = {
  propTypes: {
    id: T.number.isRequired,
    date: T.string,
    status: T.string.isRequired,
    duration: T.number,
    score: T.number,
    scoreMin: T.number,
    scoreMax: T.number
  }
}

export {
  UserEvaluation
}
