import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {reducer, selectors} from '#/plugin/cursus/tools/catalog/store'
import {CatalogTool as CatalogToolComponent} from '#/plugin/cursus/tools/catalog/components/tool'
import {actions as courseActions, reducer as courseReducer, selectors as courseSelectors} from '#/plugin/cursus/course/store'

const CatalogTool = withReducer(selectors.STORE_NAME, reducer)(
  withReducer(courseSelectors.STORE_NAME, courseReducer)(
    connect(
      (state) => ({
        path: toolSelectors.path(state),
        course: selectors.course(state)
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
