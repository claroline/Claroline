import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {TrainingsMenu as TrainingsMenuComponent} from '#/plugin/cursus/tools/trainings/components/menu'

const TrainingsMenu = connect(
  (state) => ({
    authenticated: securitySelectors.isAuthenticated(state),
    canEdit: hasPermission('edit', toolSelectors.toolData(state)),
    canRegister: hasPermission('register', toolSelectors.toolData(state)),
    canManageQuotas: hasPermission('manage_quotas', toolSelectors.toolData(state)),
    canValidateSubscriptions: hasPermission('validate_subscriptions', toolSelectors.toolData(state))
  })
)(TrainingsMenuComponent)

export {
  TrainingsMenu
}
