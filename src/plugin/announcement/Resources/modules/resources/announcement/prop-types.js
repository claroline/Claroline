import {PropTypes as T} from 'prop-types'

const Announcement = {
  propTypes: {
    id: T.string,
    title: T.string,
    content: T.string.isRequired,
    poster: T.string,
    meta: T.shape({
      author: T.string,
      notifyUsers: T.number.isRequired,
      notificationDate: T.string
    }).isRequired,
    restrictions: T.shape({
      hidden: T.bool.isRequired,
      dates: T.arrayOf(T.string)
    }).isRequired,
    roles: T.array,
    tags: T.array
  },
  defaultProps: {
    title: null,
    content: '',
    meta: {
      author: null,
      notifyUsers: 0,
      notificationDate: null
    },
    restrictions: {
      hidden: false,
      dates: []
    },
    roles: []
  }
}

export {
  Announcement
}
