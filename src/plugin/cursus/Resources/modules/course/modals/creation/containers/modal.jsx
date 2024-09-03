import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {actions as formActions} from '#/main/app/content/form'
import {selectors as toolSelectors}  from '#/main/core/tool/store'
import {actions as courseActions} from '#/plugin/cursus/course/store'
import {reducer, selectors} from '#/plugin/cursus/course/modals/creation/store'
import {CreationModal as BaseCreationModal} from '#/plugin/cursus/course/modals/creation/components/modal'

const CreationModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      contextType: toolSelectors.contextType(state)
    }),
    (dispatch) => ({
      openForm(slug, defaultProps, workspace = null) {
        dispatch(courseActions.openForm(slug, defaultProps, workspace))
      },
      reset() {
        dispatch(formActions.reset(selectors.STORE_NAME, {}, true))
      }
    })
  )(BaseCreationModal)
)

export {
  CreationModal
}
