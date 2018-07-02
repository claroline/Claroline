import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {actions as formActions} from '#/main/core/data/form/actions'
import {constants} from '#/plugin/blog/resources/blog/constants.js'
import {actions as blogActions} from '#/plugin/blog/resources/blog/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const BLOG_OPTIONS_LOAD = 'BLOG_OPTIONS_LOAD'
export const BLOG_OPTIONS_WIDGET_VISIBILITY = 'BLOG_OPTIONS_WIDGET_VISIBILITY'
export const BLOG_OPTIONS_WIDGET_UP = 'BLOG_OPTIONS_WIDGET_UP'
export const BLOG_OPTIONS_WIDGET_DOWN = 'BLOG_OPTIONS_WIDGET_DOWN'

export const actions = {}
  
actions.switchWidgetVisibility = makeActionCreator(BLOG_OPTIONS_WIDGET_VISIBILITY, 'id', 'name')
actions.widgetUp = makeActionCreator(BLOG_OPTIONS_WIDGET_UP, 'id', 'name')
actions.widgetDown = makeActionCreator(BLOG_OPTIONS_WIDGET_DOWN, 'id', 'name')

actions.editBlogOptions = (formName, blogId) => (dispatch) => {
  dispatch({[API_REQUEST]: {
    url:['apiv2_blog_options', {blogId}],
    request: {
      method: 'GET'
    },
    success: (response, dispatch) => {
      dispatch(formActions.resetForm(formName, response, false))
      dispatch(blogActions.switchMode(constants.EDIT_OPTIONS))
    }
  }})
}