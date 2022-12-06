import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {TeamCreate as TeamCreateComponent} from '#/main/community/tools/community/team/components/create'

const TeamCreate = connect(
  state => ({
    path: toolSelectors.path(state)
  })
)(TeamCreateComponent)

export {
  TeamCreate
}
