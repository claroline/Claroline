import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {makeId} from '#/main/core/scaffolding/id'

import {
  actions as formActions,
  selectors as formSelectors
} from '#/main/app/content/form/store'

import {CategoryFormModal as CategoryFormModalComponent} from '#/plugin/claco-form/modals/category/components/form'
import {reducer, selectors} from '#/plugin/claco-form/modals/category/store'

const CategoryFormModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME)),
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      updateProp(prop, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME, prop, value))
      },
      loadCategory(category = null) {
        dispatch(formActions.reset(selectors.STORE_NAME, category || {
          id: makeId(),
          name: '',
          managers: [],
          details: {
            color: '',
            notify_addition: true,
            notify_edition: true,
            notify_removal: true,
            notify_pending_comment: true
          },
          fieldsValues: []
        }, !!category))
      }
    })
  )(CategoryFormModalComponent)
)

export {
  CategoryFormModal
}
