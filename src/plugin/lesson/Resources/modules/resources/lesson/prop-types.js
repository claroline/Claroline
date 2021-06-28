import {PropTypes as T} from 'prop-types'

const Lesson = {
  propTypes: {
    id: T.string.isRequired,
    display: T.shape({
      description: T.string,
      showOverview: T.bool
    })
  },
  defaultProps: {}
}

const Chapter = {
  propTypes: {
    id: T.string.isRequired,
    slug: T.string.isRequired,
    title: T.string,
    poster: T.string,
    text: T.string,
    internalNote: T.string,
    parentSlug: T.string,
    previousSlug: T.string,
    nextSlug: T.string
  },
  defaultProps: {}
}

export {
  Lesson,
  Chapter
}
