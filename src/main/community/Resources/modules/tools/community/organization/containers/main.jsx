import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {OrganizationMain as OrganizationMainComponent} from '#/main/community/tools/community/organization/components/main'
import {actions, selectors} from '#/main/community/tools/community/organization/store'

const OrganizationMain = connect(
  state => ({
    path: toolSelectors.path(state),
    organizations: selectors.flattenedOrganizations(state)
  }),
  dispatch => ({
    open(id) {
      dispatch(actions.open(id))
    },
    new(parent = null) {
      dispatch(actions.new({
        parent: parent
      }))
    }
  })
)(OrganizationMainComponent)

export {
  OrganizationMain
}
