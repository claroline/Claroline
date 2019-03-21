import {PropTypes as T} from 'prop-types'
import {makeId} from '#/plugin/exo/utils/utils'

export const ClozeItem = {
  propsTypes: {
    id: T.string.isRequired,
    text: T.string.isRequired,
    _text: T.string.isRequired,
    _errors: T.object,
    _popover: T.bool,
    _holeId: T.string,
    holes: T.arrayOf(T.shape({
      id: T.string.isRequired
    })).isRequired,
    solutions: T.arrayOf(T.shape({
      holeId: T.string.isRequired
    })).isRequired
  },
  defaultProps: {
    solutions: [],
    holes: []
  }
}
