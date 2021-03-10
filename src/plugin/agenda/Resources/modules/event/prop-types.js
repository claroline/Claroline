import {PropTypes as T} from 'prop-types'

import {constants} from '#/plugin/agenda/event/constants'

const Event = {
  propTypes: {
    id: T.string.isRequired,
    title: T.string,
    start: T.string,
    end: T.string,
    description: T.string,
    thumbnail: T.shape({
      url: T.string
    }),
    meta: T.shape({
      type: T.oneOf(
        Object.keys(constants.EVENT_TYPES)
      ).isRequired
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
      done: false,
      type: constants.EVENT_TYPE_EVENT
    },
    permissions: {}
  }
}

export {
  Event
}
