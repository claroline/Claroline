import {PropTypes as T} from 'prop-types'

import {WidgetContainer} from '#/main/core/widget/prop-types'

const WidgetsTab = {
  propTypes: {
    parameters: T.shape({
      widgets: T.arrayOf(T.shape(
        WidgetContainer.propTypes
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
