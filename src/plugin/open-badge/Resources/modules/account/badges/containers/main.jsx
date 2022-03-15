import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {BadgesMain as BadgesMainComponent} from '#/plugin/open-badge/account/badges/components/main'
import {reducer, selectors} from '#/plugin/open-badge/account/badges/store'

const BadgesMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state)
    })
  )(BadgesMainComponent)
)
export {
  BadgesMain
}
