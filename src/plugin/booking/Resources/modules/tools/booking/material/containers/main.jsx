import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {MaterialMain as MaterialMainComponent} from '#/plugin/booking/tools/booking/material/components/main'
import {actions} from '#/plugin/booking/tools/booking/material/store'

const MaterialMain = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    open(id) {
      dispatch(actions.open(id))
    }
  })
)(MaterialMainComponent)

export {
  MaterialMain
}
