import {connect} from 'react-redux'
import {selectors} from '#/plugin/cursus/tools/trainings/quota/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {SubscriptionPage as SubscriptionComponent} from '#/plugin/cursus/tools/trainings/quota/subscription/components/page'

const SubscriptionPage = connect(
	(state) => ({
    	currentContext: toolSelectors.context(state),
		quota: selectors.quota(state)
	})
)(SubscriptionComponent)

export {
  SubscriptionPage
}
