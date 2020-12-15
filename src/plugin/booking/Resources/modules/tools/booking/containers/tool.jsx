import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {BookingTool as BookingToolComponent} from '#/plugin/booking/tools/booking/components/tool'

const BookingTool = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(BookingToolComponent)

export {
  BookingTool
}
