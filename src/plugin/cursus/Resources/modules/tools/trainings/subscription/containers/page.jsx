import {connect} from 'react-redux'
import {selectors} from '#/plugin/cursus/tools/trainings/quota/store'
import {actions} from '#/plugin/cursus/tools/trainings/subscription/store/actions'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {SubscriptionPage as SubscriptionComponent} from '#/plugin/cursus/tools/trainings/subscription/components/page'

const SubscriptionPage = connect(
  (state) => ({
    	currentContext: toolSelectors.context(state),
    quota: selectors.quota(state)
  }),
  (dispatch) => ({
	 	setSubscriptionStatus(id, status) {
      dispatch(actions.setSubscriptionStatus(id, status))
	 	}
  })
)(SubscriptionComponent)

export {
  SubscriptionPage
}
