import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {RoomMain as RoomMainComponent} from '#/main/core/tools/locations/room/components/main'
import {actions} from '#/main/core/tools/locations/room/store'

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
