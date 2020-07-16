import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {actions} from '#/plugin/cursus/tools/cursus/catalog/store'
import {CatalogMain as CatalogMainComponent} from '#/plugin/cursus/tools/cursus/catalog/components/main'

const CatalogMain = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    open(slug) {
      dispatch(actions.open(slug))
    },
    openForm(slug, defaultProps) {
      dispatch(actions.openForm(slug, defaultProps))
    }
  })
)(CatalogMainComponent)

export {
  CatalogMain
}
