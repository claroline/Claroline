import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'
import {
  SUBJECT_LOAD,
  SUBJECT_FORM_OPEN,
  SUBJECT_FORM_CLOSE,
  SUBJECT_EDIT,
  SUBJECT_STOP_EDIT
} from '#/plugin/forum/resources/forum/player/actions'


const reducer = combineReducers({
  form: makeFormReducer('subjects.form', {
    showSubjectForm: false,
    editingSubject: false
  }, {
    showSubjectForm: makeReducer(false, {
      [SUBJECT_FORM_OPEN]: () => true,
      [SUBJECT_FORM_CLOSE]: () => false
    }),
    editingSubject: makeReducer(false, {
      [SUBJECT_EDIT]: () => true,
      [SUBJECT_STOP_EDIT]: () => false
    })
  }),
  list: makeListReducer('subjects.list', {
    sortBy: {property: 'sticked', direction: -1}
  }),
  current: makeReducer({}, {
    [FORM_SUBMIT_SUCCESS+'/subjects.form']: (state, action) => action.updatedData,
    [SUBJECT_LOAD]: (state, action) => action.subject
  }),
  messages: makeListReducer('subjects.messages', {
    pageSize: 10,
    sortBy: {property: 'creationDate', direction : 1}
  })
})

export {
  reducer
}
