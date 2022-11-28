import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {GroupCreate as GroupCreateComponent} from '#/main/community/tools/community/group/components/create'

const GroupCreate = connect(
  state => ({
    path: toolSelectors.path(state)
  })
)(GroupCreateComponent)

export {
  GroupCreate
}
