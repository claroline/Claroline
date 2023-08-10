import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {EditorMenu as EditorMenuComponent} from '#/plugin/flashcard/resources/flashcard/editor/components/menu'

const EditorMenu = withRouter(
  connect(
    (state) => ({
      path: resourceSelectors.path(state)
    })
  )(EditorMenuComponent)
)

export {
  EditorMenu
}
