import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ParametersMenu as ParametersMenuComponent} from '#/main/core/administration/parameters/components/menu'

const ParametersMenu = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(ParametersMenuComponent)

export {
  ParametersMenu
}
