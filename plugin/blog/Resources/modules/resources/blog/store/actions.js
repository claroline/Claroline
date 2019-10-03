import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const SWITCH_MODE = 'SWITCH_MODE'

export const actions = {}

actions.switchMode = makeActionCreator(SWITCH_MODE, 'mode')

actions.downloadBlogPdf = (blogId) => ({
  [API_REQUEST]: {
    url: ['icap_blog_pdf', {blogId: blogId}],
    request: {
      method: 'GET'
    }
  }
})
