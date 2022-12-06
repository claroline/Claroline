import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {TeamList as TeamListComponent} from '#/main/community/tools/community/team/components/list'

const TeamList = connect(
  state => ({
    path: toolSelectors.path(state),
    contextData: toolSelectors.contextData(state),
    canCreate: hasPermission('edit', toolSelectors.toolData(state))
  })
)(TeamListComponent)

export {
  TeamList
}
