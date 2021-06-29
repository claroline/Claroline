import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {RoomList as RoomListComponent} from '#/main/core/tools/locations/room/components/list'
import {selectors} from '#/main/core/tools/locations/room/store'

const RoomList = connect(
  (state) => ({
    path: toolSelectors.path(state),
    editable: hasPermission('edit', toolSelectors.toolData(state))
  }),
  (dispatch) => ({
    invalidateList() {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
    }
  })
)(RoomListComponent)

export {
  RoomList
}
