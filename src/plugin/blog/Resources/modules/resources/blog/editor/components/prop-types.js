import {PropTypes as T} from 'prop-types'

const BlogOptionsType = {
  propTypes: {
    authorizeComment: T.bool,
    authorizeAnonymousComment: T.bool,
    postPerPage: T.number,
    autoPublishPost: T.bool,
    displayTitle: T.bool,
    bannerActivate: T.bool,
    displayPostViewCounter: T.bool,
    tagCloud: T.string,
    commentModerationMode: T.string,
    listWidgetBlog: T.array,
    tagTopMode: T.bool
  }
}

export {
  BlogOptionsType
}
