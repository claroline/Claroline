import {PropTypes as T} from 'prop-types'

import {Role as RoleTypes} from '#/main/community/prop-types'

import {constants} from '#/main/core/data/types/connection-message/constants'

const Slide = {
  propTypes: {
    id: T.string,
    title: T.string,
    content: T.string,
    poster: T.string,
    order: T.number,
    message: T.shape({
      id: T.string
    }),
    shortcuts: T.arrayOf(T.shape({
      type: T.oneOf(['tool', 'action']).isRequired,
      name: T.string.isRequired
    }))
  }
}

const ConnectionMessage = {
  propTypes: {
    id: T.string,
    title: T.string,
    type: T.string,
    locked: T.bool,
    restrictions: T.shape({
      dates: T.arrayOf(T.string),
      roles: T.arrayOf(T.shape(
        RoleTypes.propTypes
      ))
    }),
    slides: T.arrayOf(T.shape(
      Slide.propTypes
    ))
  },
  defaultProps: {
    type: constants.MESSAGE_TYPE_ONCE,
    locked: false,
    restrictions: {
      dates: [null, null]
    },
    slides: [],
    roles: []
  }
}

export {
  Slide,
  ConnectionMessage
}
