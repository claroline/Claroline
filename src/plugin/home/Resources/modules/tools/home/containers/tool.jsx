import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/reducer'
import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {HomeTool as HomeToolComponent} from '#/plugin/home/tools/home/components/tool'
import {reducer, selectors} from '#/plugin/home/tools/home/store'
import {selectors as playerSelectors} from '#/plugin/home/tools/home/player/store'
import {actions as editorActions, selectors as editorSelectors} from '#/plugin/home/tools/home/editor/store'

const HomeTool = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        tabs: playerSelectors.tabs(state),
        editorTabs: editorSelectors.editorTabs(state),
        canEdit: hasPermission('edit', toolSelectors.toolData(state))
      }),
      (dispatch) => ({
        createTab(parent = null, tab, navigate) {
          dispatch(editorActions.createTab(parent, tab, navigate))
        }
      })
    )(HomeToolComponent)
  )
)

export {
  HomeTool
}
