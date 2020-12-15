import {PropTypes as T} from 'prop-types'

const Announcement = {
  propTypes: {
    title: T.string,
    content: T.string.isRequired,
    meta: T.shape({
      author: T.string,
      notifyUsers: T.number.isRequired,
      notificationDate: T.string
    }).isRequired,
    restrictions: T.shape({
      hidden: T.bool.isRequired,
      dates: T.arrayOf(T.string)
    }).isRequired,
    roles: T.array
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
