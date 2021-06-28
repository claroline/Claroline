import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {reducer, selectors} from '#/main/log/account/functional/store'
import {FunctionalMain as FunctionalMainComponent}  from '#/main/log/account/functional/components/main'

const FunctionalMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state)
    })
  )(FunctionalMainComponent)
)

export {
  FunctionalMain
}
