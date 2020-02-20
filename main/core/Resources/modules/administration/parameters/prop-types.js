import {PropTypes as T} from 'prop-types'

import {Role as RoleTypes} from '#/main/core/user/prop-types'

import {constants} from '#/main/core/administration/parameters/constants'

const Slide = {
  propTypes: {
    id: T.string,
    title: T.string,
    content: T.string,
    poster: T.shape({
      url: T.string,
      mimeType: T.string
    }),
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

const IconSet = {
  propTypes: {
    id: T.string,
    name: T.string,
    type: T.string,
    default: T.bool,
    active: T.bool,
    editable: T.bool
  },
  defaultProps: {
    type: constants.ICON_SET_TYPE_RESOURCE,
    editable: true
  }
}

const IconItem = {
  propTypes: {
    id: T.string,
    iconSet: T.shape(IconSet.propTypes),
    mimeType: T.string,
    relativeUrl: T.string,
    name: T.string,
    class: T.string
  }
}

export {
  Slide,
  ConnectionMessage,
  IconSet,
  IconItem
}