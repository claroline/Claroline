import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EvaluationMenu as EvaluationMenuComponent} from '#/main/evaluation/tools/evaluation/components/menu'

const EvaluationMenu = connect(
  (state) => ({
    canEdit: hasPermission('edit', toolSelectors.toolData(state)),
    contextType: toolSelectors.contextType(state),
    permissions: toolSelectors.permissions(state)
  })
)(EvaluationMenuComponent)

export {
  EvaluationMenu
}
