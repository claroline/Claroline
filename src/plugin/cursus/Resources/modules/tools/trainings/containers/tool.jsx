import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {TrainingsTool as TrainingsToolComponent} from '#/plugin/cursus/tools/trainings/components/tool'

const TrainingsTool = connect(
  (state) => ({
    authenticated: securitySelectors.isAuthenticated(state),
    canManageQuotas: hasPermission('manage_quotas', toolSelectors.toolData(state)),
    canValidateSubscriptions: hasPermission('validate_subscriptions', toolSelectors.toolData(state))
  })
)(TrainingsToolComponent)

export {
  TrainingsTool
}
