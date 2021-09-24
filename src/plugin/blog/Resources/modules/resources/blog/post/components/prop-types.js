import {PropTypes as T} from 'prop-types'

const PostType = {
  propTypes: {
    id: T.string,
    title: T.string,
    creationDate: T.string,
    author: T.Object,
    viewCounter: T.number,
    isPublished: T.bool,
    commentsNumber: T.number
  }
}

export {
  PostType
}
