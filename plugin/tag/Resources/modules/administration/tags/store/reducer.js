import {combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'

export const reducer = {
  tags: makeListReducer('tags', {
    sortBy: {property: 'name', direction: 1}
  }),
  tag: combineReducers({
    form: makeFormReducer('tag.form'),
    objects: makeListReducer('tag.objects')
  })
}
