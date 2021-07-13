import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {MaterialList as MaterialListComponent} from '#/main/core/tools/locations/material/components/list'
import {selectors} from '#/main/core/tools/locations/material/store'

const MaterialList = connect(
  (state) => ({
    path: toolSelectors.path(state),
    editable: hasPermission('edit', toolSelectors.toolData(state))
  }),
  (dispatch) => ({
    invalidateList() {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
    }
  })
)(MaterialListComponent)

export {
  MaterialList
}
