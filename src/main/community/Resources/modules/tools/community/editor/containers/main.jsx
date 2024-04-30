import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool'

import {CommunityEditor as CommunityEditorComponent} from '#/main/community/tools/community/editor/components/main'

const CommunityEditor = connect(
  (state) => ({
    path: toolSelectors.path(state),
    contextType: toolSelectors.contextType(state),
    contextId: toolSelectors.contextId(state)
  })
)(CommunityEditorComponent)

export {
  CommunityEditor
}
