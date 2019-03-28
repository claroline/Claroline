import {PropTypes as T} from 'prop-types'

const ClozeItem = {
  propsTypes: {
    id: T.string.isRequired,
    text: T.string.isRequired,
    holes: T.arrayOf(T.shape({
      id: T.string.isRequired
      // TODO : add missing props
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

export {
  ClozeItem
}