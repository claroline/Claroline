import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {MaterialMain as MaterialMainComponent} from '#/main/core/tools/locations/material/components/main'
import {actions} from '#/main/core/tools/locations/material/store'

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
