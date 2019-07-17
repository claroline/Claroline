import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {selectors} from '#/plugin/tag/administration/tags/store/selectors'

export const reducer = combineReducers({
  tags: makeListReducer(selectors.STORE_NAME + '.tags', {
    sortBy: {property: 'name', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.tag.form']: () => true
    })
  }),
  tag: combineReducers({
    form: makeFormReducer(selectors.STORE_NAME + '.tag.form'),
    objects: makeListReducer(selectors.STORE_NAME + '.tag.objects')
  })
})
