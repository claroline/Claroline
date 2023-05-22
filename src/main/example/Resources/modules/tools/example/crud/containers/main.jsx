import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {CrudMain as CrudMainComponent} from '#/main/example/tools/example/crud/components/main'
const CrudMain = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(CrudMainComponent)

export {
  CrudMain
}
