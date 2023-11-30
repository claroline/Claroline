import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {reducer, selectors} from '#/plugin/flashcard/resources/flashcard/editor/modals/card/store'
import {CardModal as CardModalComponent} from '#/plugin/flashcard/resources/flashcard/editor/modals/card/components/modal'

const CardModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      isNew: formSelectors.isNew(formSelectors.form(state, selectors.STORE_NAME)),
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)),
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      reset(formData, isNew = false) {
        dispatch(formActions.reset(selectors.STORE_NAME, formData, isNew))
      },
      update(prop, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME, prop, value))
      }
    })
  )(CardModalComponent)
)

export {
  CardModal
}
