import {PropTypes as T} from 'prop-types'

const Directory = {
  propTypes: {
    display: T.oneOf([]), // todo list displays
    availableDisplays: T.arrayOf(
      T.oneOf([])
    ),
    filters: T.arrayOf(T.shape({

    }))
  },
  defaultProps: {

  }
}

export {
  Directory
}
