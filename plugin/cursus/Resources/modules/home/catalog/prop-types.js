import {PropTypes as T} from 'prop-types'

import {Widget} from '#/main/core/widget/prop-types'

const WidgetsTab = {
  propTypes: {
    parameters: T.shape({
      widgets: T.arrayOf(T.shape(
        Widget.propTypes
      ))
    })
  },
  defaultProps: {
    parameters: {
      widgets: []
    }
  }
}

export {
  WidgetsTab
}
