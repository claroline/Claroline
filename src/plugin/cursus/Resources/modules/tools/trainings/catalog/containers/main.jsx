import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {withReducer} from '#/main/app/store/reducer'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions as courseActions} from '#/plugin/cursus/course/store'
import {actions, reducer, selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CatalogMain as CatalogMainComponent} from '#/plugin/cursus/tools/trainings/catalog/components/main'

const CatalogMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      course: selectors.course(state),
      canEdit: hasPermission('edit', toolSelectors.toolData(state))
    }),
    (dispatch) => ({
      open(slug) {
        dispatch(courseActions.open(slug))
      },
      openForm(slug, defaultProps) {
        dispatch(actions.openForm(slug, defaultProps))
      }
    })
  )(CatalogMainComponent)
)

export {
  CatalogMain
}
