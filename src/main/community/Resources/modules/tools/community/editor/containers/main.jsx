import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool'

import {CommunityEditor as CommunityEditorComponent} from '#/main/community/tools/community/editor/components/main'

const CommunityEditor = connect(
  (state) => ({
    contextType: toolSelectors.contextType(state)
  })
)(CommunityEditorComponent)

export {
  CommunityEditor
}
