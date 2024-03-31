import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

import {TagList as TagListComponent} from '#/plugin/tag/tools/tags/components/list'

const TagList = connect(
  (state) => ({
    path: toolSelectors.path(state),
    canCreate: hasPermission('create', toolSelectors.toolData(state))
  })
)(TagListComponent)

export {
  TagList
}
