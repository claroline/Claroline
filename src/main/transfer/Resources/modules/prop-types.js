import {PropTypes as T} from 'prop-types'

const ImportFile = {
  propTypes: {
    id: T.string.isRequired,
    status: T.string.isRequired,
    action: T.string,
    meta: T.shape({
      createdAt: T.string,
      creator: T.object // a User object
    }),
    executionDate: T.string,
    file: T.object // a PublicFile object
  },
  defaultProps: {

  }
}

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
  ImportFile,
  ExportFile
}
