import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {OrganizationCreate as OrganizationCreateComponent} from '#/main/community/tools/community/organization/components/create'

const OrganizationCreate = connect(
  state => ({
    path: toolSelectors.path(state)
  })
)(OrganizationCreateComponent)

export {
  OrganizationCreate
}
