import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {withReducer} from '#/main/app/store/reducer'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {reducer, selectors} from '#/plugin/cursus/tools/catalog/store'
import {actions as courseActions, reducer as courseReducer, selectors as courseSelectors} from '#/plugin/cursus/course/store'
import {CatalogTool as CatalogToolComponent} from '#/plugin/cursus/tools/catalog/components/tool'

const CatalogTool = withReducer(courseSelectors.STORE_NAME, courseReducer)(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        path: toolSelectors.path(state),
        course: courseSelectors.course(state),
        canEdit: hasPermission('edit', toolSelectors.toolData(state))
      }),
      (dispatch) => ({
        open(slug) {
          dispatch(courseActions.open(slug))
        }
      })
    )(CatalogToolComponent)
  )
)

export {
  CatalogTool
}
