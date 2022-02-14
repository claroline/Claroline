import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {KeywordModal as KeywordModalComponent} from '#/plugin/claco-form/modals/keyword/components/modal'
import {reducer, selectors} from '#/plugin/claco-form/modals/keyword/store'
import {makeId} from '#/main/core/scaffolding/id'

const KeywordModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      isNew: formSelectors.isNew(formSelectors.form(state, selectors.STORE_NAME)),
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME)),
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      loadKeyword(clacoFormId, keyword = null) {
        dispatch(formActions.reset(selectors.STORE_NAME, keyword || {
          id: makeId(),
          name: '',
          clacoForm: {id: clacoFormId}
        }, !!keyword))
      },
      saveKeyword(keyword, isNew = false, callback) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, isNew ? ['apiv2_clacoformkeyword_create'] : ['apiv2_clacoformkeyword_update', {id: keyword.id}])).then(() => {
          if (callback) {
            callback(keyword)
          }
        })
      }
    })
  )(KeywordModalComponent)
)

export {
  KeywordModal
}