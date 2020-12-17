import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {actions, reducer, selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CatalogMain as CatalogMainComponent} from '#/plugin/cursus/tools/trainings/catalog/components/main'

const CatalogMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state, ownProps) => ({
      path: ownProps.path || toolSelectors.path(state)
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
)

export {
  CatalogMain
}
