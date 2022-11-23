import {PropTypes as T} from 'prop-types'

import {constants} from '#/main/core/administration/parameters/constants'

const IconSet = {
  propTypes: {
    id: T.string,
    name: T.string,
    type: T.string,
    default: T.bool,
    restrictions: T.shape({
      locked: T.bool
    })
  },
  defaultProps: {
    type: constants.ICON_SET_TYPE_RESOURCE,
    restrictions: {
      locked: false
    }
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
  IconSet,
  IconItem
}
