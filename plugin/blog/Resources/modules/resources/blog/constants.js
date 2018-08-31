import {trans} from '#/main/core/translation'

const POST_EDIT_FORM_NAME = 'post_edit'
const OPTIONS_EDIT_FORM_NAME = 'blog.data.options'
const PAGE_SIZE_1 = '1'
const PAGE_SIZE_5 = '5'
const PAGE_SIZE_10 = '10'
const PAGE_SIZE_20 = '20'
  
const TAGCLOUD_TYPE_CLASSIC = '0'
const TAGCLOUD_TYPE_3D = '1'
const TAGCLOUD_TYPE_CLASSIC_NUM = '2'
const TAGCLOUD_TYPE_LIST = '3'
  
const COMMENT_MODERATION_MODE_NONE = '0'
const COMMENT_MODERATION_MODE_PRIOR_ONCE = '1'
const COMMENT_MODERATION_MODE_ALL = '2'
  
const LIST_POSTS = 'list_posts'
const CREATE_POST = 'create_post'
const EDIT_POST = 'edit_post'
const EDIT_OPTIONS = 'edit_options'
      
const PAGE_SIZE = {
  [PAGE_SIZE_1]: 1,
  [PAGE_SIZE_5]: 5,
  [PAGE_SIZE_10]: 10,
  [PAGE_SIZE_20]: 20
}

const TAGCLOUD_TYPE = {
  [TAGCLOUD_TYPE_CLASSIC]: trans('classic', {}, 'icap_blog'),
  [TAGCLOUD_TYPE_CLASSIC_NUM]: trans('advanced', {}, 'icap_blog'),
  [TAGCLOUD_TYPE_3D]: trans('3D', {}, 'icap_blog'),
  [TAGCLOUD_TYPE_LIST]: trans('vertical_list', {}, 'icap_blog')
}

const COMMENT_MODERATION_MODE = {
  [COMMENT_MODERATION_MODE_NONE]: trans('comment_moderation_mode_none', {}, 'icap_blog'),
  [COMMENT_MODERATION_MODE_PRIOR_ONCE]: trans('comment_moderation_mode_prior_once', {}, 'icap_blog'),
  [COMMENT_MODERATION_MODE_ALL]: trans('comment_moderation_mode_all', {}, 'icap_blog')
}

const MODES = {
  [LIST_POSTS]: 'list_posts',
  [CREATE_POST]: 'create_post',
  [EDIT_POST]: 'edit_post',
  [EDIT_OPTIONS]: 'edit_options'
}

export const constants = {
  PAGE_SIZE,
  TAGCLOUD_TYPE,
  TAGCLOUD_TYPE_CLASSIC_NUM,
  TAGCLOUD_TYPE_LIST,
  COMMENT_MODERATION_MODE,
  COMMENT_MODERATION_MODE_NONE,
  MODES,
  LIST_POSTS,
  CREATE_POST,
  EDIT_POST,
  EDIT_OPTIONS,
  POST_EDIT_FORM_NAME,
  OPTIONS_EDIT_FORM_NAME
}
