import {connect} from 'react-redux'
import {actions, selectors} from '#/plugin/cursus/tools/trainings/subscription/store'
import {selectors as quotaSelectors} from '#/plugin/cursus/tools/trainings/quota/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {SubscriptionPage as SubscriptionComponent} from '#/plugin/cursus/tools/trainings/subscription/components/page'

const SubscriptionPage = connect(
  (state) => ({
    currentContext: toolSelectors.context(state),
    quota: quotaSelectors.quota(state),
    statistics: selectors.statistics(state),
    filters: selectors.filters(state)
  }),
  (dispatch) => ({
    getStatistics(id) {
      dispatch(actions.getStatistics(id))
    },
    setSubscriptionStatus(quotaId, subscriptionId, status, remark) {
      dispatch(actions.setSubscriptionStatus(quotaId, subscriptionId, status, remark))
    }
  })
)(SubscriptionComponent)

export {
  SubscriptionPage
}
