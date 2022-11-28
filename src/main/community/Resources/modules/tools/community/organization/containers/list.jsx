import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {OrganizationList as OrganizationListComponent} from '#/main/community/tools/community/organization/components/list'
import {selectors} from '#/main/community/tools/community/organization/store'

const OrganizationList = connect(
  (state) => ({
    path: toolSelectors.path(state),
    canCreate: selectors.canCreate(state)
  })
)(OrganizationListComponent)

export {
  OrganizationList
}