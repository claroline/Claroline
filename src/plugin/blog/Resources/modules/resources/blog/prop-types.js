import {PropTypes as T} from 'prop-types'

const BlogOptions = {
  propTypes: {
    authorizeComment: T.bool,
    authorizeAnonymousComment: T.bool,
    postPerPage: T.number,
    autoPublishPost: T.bool,
    displayPostViewCounter: T.bool,
    tagCloud: T.string,
    commentModerationMode: T.string,
    listWidgetBlog: T.array,
    tagTopMode: T.bool
  }
}

export {
  BlogOptions
}
