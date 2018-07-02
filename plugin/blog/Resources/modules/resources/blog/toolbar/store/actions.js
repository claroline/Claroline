import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {API_REQUEST} from '#/main/app/api'

export const LOAD_TAGS = 'LOAD_TAGS'
export const ADD_TAGS = 'ADD_TAG'
export const ADD_AUTHOR = 'ADD_AUTHOR'
export const LOAD_AUTHORS = 'LOAD_AUTHORS'

export const actions = {}

actions.addTags = makeActionCreator(ADD_TAGS, 'originalTags', 'tags')
actions.addAuthor = makeActionCreator(ADD_AUTHOR, 'author')
actions.loadTags = makeActionCreator(LOAD_TAGS, 'tags')
actions.loadAuthors = makeActionCreator(LOAD_AUTHORS, 'authors')

actions.getTags = (blogId) => (dispatch) => {
  dispatch({[API_REQUEST]: {
    url:['apiv2_blog_tags', {blogId}],
    request: {
      method: 'GET'
    },
    success: (response, dispatch) => dispatch(actions.loadTags(response))
  }})
}

actions.getRedactors = (blogId) => (dispatch) => {
  dispatch({[API_REQUEST]: {
    url:['apiv2_blog_post_authors', {blogId}],
    request: {
      method: 'GET'
    },
    success: (response, dispatch) => dispatch(actions.loadTags(response))
  }})
}