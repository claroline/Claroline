import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {actions as listActions} from '#/main/app/content/list/store'

import {DocumentationModal as DocumentationModalComponent} from '#/plugin/documentation/modals/documentation/components/modal'
import {actions, selectors, reducer} from '#/plugin/documentation/modals/documentation/store'

const DocumentationModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      current: selectors.current(state)
    }),
    (dispatch) => ({
      open(id) {
        return dispatch(actions.open(id))
      },
      loadList() {
        return dispatch(listActions.fetchData(selectors.LIST_NAME, ['apiv2_documentation_list']))
      },
      resetFilters(filters) {
        dispatch(listActions.resetFilters(selectors.LIST_NAME, filters))
      },
      reset() {
        dispatch(listActions.resetSelect(selectors.LIST_NAME))
        dispatch(listActions.invalidateData(selectors.LIST_NAME))
        dispatch(actions.load(null))
      }
    })
  )(DocumentationModalComponent)
)

export {
  DocumentationModal
}
