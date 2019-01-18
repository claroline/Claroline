import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {HomeTool as HomeToolComponent} from '#/main/core/tools/home/components/tool'
import {actions, selectors} from '#/main/core/tools/home/store'
import {selectors as editorSelectors} from '#/main/core/tools/home/editor/store'

const HomeTool = withRouter(
  connect(
    (state) => ({
      editable: selectors.editable(state),
      sortedTabs: selectors.sortedTabs(state),
      editorTabs: editorSelectors.editorTabs(state),
      currentTab: selectors.currentTab(state)
    }),
    (dispatch) => ({
      setCurrentTab(tab){
        dispatch(actions.setCurrentTab(tab))
      }
    })
  )(HomeToolComponent)
)

export {
  HomeTool
}
