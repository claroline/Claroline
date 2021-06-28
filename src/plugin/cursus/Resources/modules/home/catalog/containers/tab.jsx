import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {actions, reducer, selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CatalogTab as CatalogTabComponent} from '#/plugin/cursus/home/catalog/components/tab'

const CatalogTab = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    null,
    (dispatch) => ({
      open(slug) {
        dispatch(actions.open(slug))
      },
      openForm(slug, defaultProps) {
        dispatch(actions.openForm(slug, defaultProps))
      }
    })
  )(CatalogTabComponent)
)

export {
  CatalogTab
}
