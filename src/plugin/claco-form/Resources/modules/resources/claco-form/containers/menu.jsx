import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

import {ClacoFormMenu as ClacoFormMenuComponent} from '#/plugin/claco-form/resources/claco-form/components/menu'

const ClacoFormMenu = withRouter(
  connect(
    (state) => ({
      editable: hasPermission('edit', resourceSelectors.resourceNode(state))
    })
  )(ClacoFormMenuComponent)
)

export {
  ClacoFormMenu
}
