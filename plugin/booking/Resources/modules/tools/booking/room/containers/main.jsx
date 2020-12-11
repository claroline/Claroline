import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {RoomMain as RoomMainComponent} from '#/plugin/booking/tools/booking/room/components/main'
import {actions} from '#/plugin/booking/tools/booking/room/store'

const RoomMain = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    open(id) {
      dispatch(actions.open(id))
    }
  })
)(RoomMainComponent)

export {
  RoomMain
}
