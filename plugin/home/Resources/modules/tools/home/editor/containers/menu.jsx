import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {EditorMenu as EditorMenuComponent} from '#/plugin/home/tools/home/editor/components/menu'
import {selectors} from '#/plugin/home/tools/home/editor/store'

const EditorMenu = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      tabs: selectors.editorTabs(state)
    })
  )(EditorMenuComponent)
)

export {
  EditorMenu
}
