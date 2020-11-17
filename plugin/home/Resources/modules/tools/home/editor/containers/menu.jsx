import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {hasPermission} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EditorMenu as EditorMenuComponent} from '#/plugin/home/tools/home/editor/components/menu'
import {actions, selectors} from '#/plugin/home/tools/home/editor/store'
import {selectors as homeSelectors} from '#/plugin/home/tools/home/store'

const EditorMenu = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      canEdit: hasPermission('edit', toolSelectors.toolData(state)),
      currentContext: toolSelectors.context(state),
      currentUser: securitySelectors.currentUser(state),
      tabs: selectors.editorTabs(state),
      administration: homeSelectors.administration(state)
    }),
    (dispatch) => ({
      createTab(index, tab, navigate) {
        dispatch(actions.createTab(index, tab, navigate))
      }
    })
  )(EditorMenuComponent)
)

export {
  EditorMenu
}
