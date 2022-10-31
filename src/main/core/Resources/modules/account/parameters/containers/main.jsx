import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {ParametersMain as ParametersMainComponent} from '#/main/core/account/parameters/components/main'
import {reducer, selectors} from '#/main/core/account/parameters/store'

const ParametersMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state)
    })
  )(ParametersMainComponent)
)

export {
  ParametersMain
}
