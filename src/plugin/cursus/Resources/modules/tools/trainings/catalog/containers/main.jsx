import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {withReducer} from '#/main/app/store/reducer'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {reducer, selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CatalogMain as CatalogMainComponent} from '#/plugin/cursus/tools/trainings/catalog/components/main'
import {actions as courseActions, reducer as courseReducer, selectors as courseSelectors} from '#/plugin/cursus/course/store'

const CatalogMain = withReducer(courseSelectors.STORE_NAME, courseReducer)(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        path: toolSelectors.path(state),
        course: selectors.course(state),
        courses: selectors.courses(state),
        contextType: toolSelectors.contextType(state),
        canEdit: hasPermission('edit', toolSelectors.toolData(state))
      }),
      (dispatch) => ({
        open(slug) {
          dispatch(courseActions.open(slug))
        },
        openForm(slug, defaultProps) {
          dispatch(courseActions.openForm(slug, defaultProps))
        }
      })
    )(CatalogMainComponent)
  )
)

export {
  CatalogMain
}
