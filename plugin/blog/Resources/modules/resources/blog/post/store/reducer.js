import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'
import {
  INIT_DATALIST,
  POST_LOAD, 
  POST_DELETE,
  POST_RESET, 
  POST_UPDATE_PUBLICATION
} from '#/plugin/blog/resources/blog/post/store/actions'

const reducer = {
  posts: makeListReducer('posts', {
    sortBy: {    
      property: 'publicationDate',
      direction: -1
    }
  },{
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/post_edit']: () => true,
      [FORM_SUBMIT_SUCCESS+'/blog.data.options']: () => true,
      [POST_UPDATE_PUBLICATION]: () => true,
      [INIT_DATALIST]: () => true,
      [POST_DELETE]: () => true
    })
  },{
    selectable: false
  }),
  post: makeReducer({}, {
    [POST_LOAD]: (state, action) => action.post,
    [POST_UPDATE_PUBLICATION]: (state, action) => action.post,
    [POST_RESET]: () => ({})
  }),
  post_edit: makeFormReducer('post_edit')
}

export {
  reducer
}