import {PropTypes as T} from 'prop-types'

const Event = {
  propTypes: {
    id: T.string.isRequired,
    name: T.string,
    start: T.string,
    end: T.string,
    description: T.string,
    thumbnail: T.string,
    meta: T.shape({
      type: T.string.isRequired
    }),
    display: T.shape({
      color: T.string
    }),
    permissions: T.shape({
      edit: T.bool
    }),
    workspace: T.shape({

    })
  },
  defaultProps: {
    meta: {
      done: false
    },
    permissions: {}
  }
}

export {
  Event
}
