import {PropTypes as T} from 'prop-types'

import {
  Role as RoleType,
  User as UserType
} from '#/main/core/user/prop-types'

import {constants} from '#/main/core/administration/parameters/main/constants'

const Slide = {
  propTypes: {
    id: T.string,
    title: T.string,
    content: T.string,
    picture: T.shape({
      url: T.string,
      mimeType: T.string
    }),
    order: T.number,
    message: T.shape({
      id: T.string
    })
  }
}

const ConnectionMessage = {
  propTypes: {
    id: T.string,
    title: T.string,
    type: T.string,
    locked: T.bool,
    restrictions: T.shape({
      dates: T.arrayOf(T.string)
    }),
    slides: T.arrayOf(T.shape(Slide.propTypes)),
    roles: T.arrayOf(T.shape(RoleType.propTypes)),
    users: T.arrayOf(T.shape(UserType.propTypes))
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