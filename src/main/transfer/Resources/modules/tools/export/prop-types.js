import {PropTypes as T} from 'prop-types'


const ExportFile = {
  propTypes: {
    id: T.string.isRequired,
    status: T.string.isRequired,
    action: T.string,
    meta: T.shape({
      createdAt: T.string,
      creator: T.object // a User object
    }),
    executionDate: T.string
  },
  defaultProps: {

  }
}

export {
  ExportFile
}
