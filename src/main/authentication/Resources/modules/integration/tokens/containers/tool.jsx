import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {reducer, selectors} from '#/main/authentication/integration/tokens/store'
import {ApiToken as ApiTokenComponent}  from '#/main/authentication/integration/tokens/components/tool'

const ApiToken = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    }),
    (dispatch) => ({
      invalidateList() {
        dispatch(listActions.invalidateData(selectors.STORE_NAME))
      }
    })
  )(ApiTokenComponent)
)

export {
  ApiToken
}
