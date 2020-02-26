import {PropTypes as T} from 'prop-types'

import {constants} from '#/main/core/tool/constants'

const Tool = {
  propTypes: {
    icon: T.string.isRequired,
    name: T.string.isRequired,
    context: T.shape({
      type: T.oneOf([
        constants.TOOL_DESKTOP,
        constants.TOOL_WORKSPACE,
        constants.TOOL_ADMINISTRATION
      ]),
      data: T.object
    }),
    display: T.shape({
      showIcon: T.bool
    })
  },
  defaultProps: {

  }
}

export {
  Tool
}
