import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {reducer, selectors} from '#/main/authentication/account/tokens/store'
import {TokensMain as TokensMainComponent}  from '#/main/authentication/account/tokens/components/main'

const TokensMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state)
    }),
    (dispatch) => ({
      invalidateList() {
        dispatch(listActions.invalidateData(selectors.STORE_NAME))
      }
    })
  )(TokensMainComponent)
)

export {
  TokensMain
}
