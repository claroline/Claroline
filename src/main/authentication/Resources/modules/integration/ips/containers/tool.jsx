import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {reducer, selectors} from '#/main/authentication/integration/ips/store'
import {IpsTool as IpsToolComponent}  from '#/main/authentication/integration/ips/components/tool'

const IpsTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    }),
    (dispatch) => ({
      invalidateList() {
        dispatch(listActions.invalidateData(selectors.LIST_NAME))
      }
    })
  )(IpsToolComponent)
)

export {
  IpsTool
}
