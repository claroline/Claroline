import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {
  actions as listActions,
  select as listSelect
} from '#/main/app/content/list/store'

import {reducer, selectors} from '#/plugin/cursus/modals/courses/store'
import {CoursesModal as CoursesModalComponent} from '#/plugin/cursus/modals/courses/components/modal'

const CoursesModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      selected: listSelect.selectedFull(listSelect.list(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      reset() {
        dispatch(listActions.resetSelect(selectors.STORE_NAME))
        dispatch(listActions.invalidateData(selectors.STORE_NAME))
      }
    })
  )(CoursesModalComponent)
)

export {
  CoursesModal
}