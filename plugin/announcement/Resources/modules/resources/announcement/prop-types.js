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
      visible: T.bool.isRequired,
      visibleFrom: T.string,
      visibleUntil: T.string
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
      visible: true,
      visibleFrom: null,
      visibleUntil: null
    },
    roles: []
  }
}

export {
  Announcement
}
