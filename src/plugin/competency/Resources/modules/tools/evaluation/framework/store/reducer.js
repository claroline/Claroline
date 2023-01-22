import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {selectors} from '#/plugin/competency/tools/evaluation/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(selectors.STORE_NAME + '.frameworks.list', {
    sortBy: {property: 'name', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.frameworks.form']: () => true,
      [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.frameworks.import']: () => true
    })
  }),
  form: makeFormReducer(selectors.STORE_NAME + '.frameworks.form', {}, {}),
  import: makeFormReducer(selectors.STORE_NAME + '.frameworks.import', {}, {}),
  current: makeListReducer(selectors.STORE_NAME + '.frameworks.current', {}, {}),
  competency: makeFormReducer(selectors.STORE_NAME + '.frameworks.competency', {}, {
    abilities: combineReducers({
      list: makeListReducer(selectors.STORE_NAME + '.frameworks.competency.abilities.list', {}, {
        invalidated: makeReducer(false, {
          [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.frameworks.competency_ability']: () => true
        })
      }),
      picker: makeListReducer(selectors.STORE_NAME + '.frameworks.competency.abilities.picker')
    })
  }),
  competency_ability: makeFormReducer(selectors.STORE_NAME + '.frameworks.competency_ability', {}, {})
})

export {
  reducer
}