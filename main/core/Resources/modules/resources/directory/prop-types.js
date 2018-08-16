import {PropTypes as T} from 'prop-types'

const Directory = {
  propTypes: {
    display: T.shape({
      showSummary: T.bool
    }),
    list: T.shape({
      columns: T.shape({
        default: T.arrayOf(T.string),
        available: T.arrayOf(T.string)
      }),
      display: T.shape({
        default: T.string,
        available: T.arrayOf(T.string)
      }),
      filters: T.shape({

      }),
      pagination: T.shape({

      }),
      sorting: T.shape({

      })
    })
  },
  defaultProps: {
    display: {
      showSummary: true
    },
    list: {

    }
  }
}

export {
  Directory
}
