import {connect} from 'react-redux'

import {ShortcutEditor as ShortcutEditorComponent} from '#/plugin/link/resources/shortcut/components/editor'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

const ShortcutEditor = connect(
  (state) => ({
    path: resourceSelectors.path(state)
  })
)(ShortcutEditorComponent)

export {
  ShortcutEditor
}