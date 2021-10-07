import {PropTypes as T} from 'prop-types'

const PostType = {
  propTypes: {
    id: T.string,
    title: T.string,
    meta: T.shape({
      author: T.string,
      creator: T.object
    }),
    creationDate: T.string,
    viewCounter: T.number,
    isPublished: T.bool,
    commentsNumber: T.number
  }
}

export {
  PostType
}
