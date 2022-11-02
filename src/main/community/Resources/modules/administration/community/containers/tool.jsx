import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {CommunityTool as CommunityToolComponent} from '#/main/community/administration/community/components/tool'

const CommunityTool = connect(
  (state) => ({
    isAdmin: securitySelectors.isAdmin(state)
  })
)(CommunityToolComponent)

export {
  CommunityTool
}
