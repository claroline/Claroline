import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

const reducer = {
  templates: makeListReducer('templates', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/template']: () => true
    })
  }),
  template: makeFormReducer('template'),
  locales:makeReducer([]),
  defaultLocale:makeReducer(null)
}

export {
  reducer
}