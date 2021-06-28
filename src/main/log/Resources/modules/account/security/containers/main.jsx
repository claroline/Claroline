import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {reducer, selectors} from '#/main/log/account/security/store'
import {SecurityMain as SecurityMainComponent}  from '#/main/log/account/security/components/main'

const SecurityMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state)
    })
  )(SecurityMainComponent)
)

export {
  SecurityMain
}
