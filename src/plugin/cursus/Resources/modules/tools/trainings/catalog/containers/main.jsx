import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {actions as courseActions} from '#/plugin/cursus/course/store'
import {CatalogMain as CatalogMainComponent} from '#/plugin/cursus/tools/trainings/catalog/components/main'

const CatalogMain = connect(
  (state) => ({
    path: toolSelectors.path(state),
    canEdit: hasPermission('edit', toolSelectors.tool(state))
  }),
  (dispatch) => ({
    open(slug) {
      dispatch(courseActions.open(slug))
    }
  })
)(CatalogMainComponent)

export {
  CatalogMain
}
