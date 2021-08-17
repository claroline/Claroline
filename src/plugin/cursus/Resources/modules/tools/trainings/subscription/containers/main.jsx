import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'
import {selectors, actions, reducer} from '#/plugin/cursus/tools/trainings/quota/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {SubscriptionMain as SubscriptionComponent} from '#/plugin/cursus/tools/trainings/subscription/components/main'

const SubscriptionMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    }),
    (dispatch) => ({
      open(id) {
        dispatch(actions.open(id))
      }
    })
  )(SubscriptionComponent)
)
		
export {
  SubscriptionMain
}
		