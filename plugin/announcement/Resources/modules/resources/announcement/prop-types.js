import {PropTypes as T} from 'prop-types'

const Announcement = {
  propTypes: {
    title: T.string,
    content: T.string.isRequired,
    meta: T.shape({
      author: T.string,
      notifyUsers: T.bool.isRequired
    }).isRequired,
    restrictions: T.shape({
      visible: T.bool.isRequired,
      visibleFrom: T.string,
      visibleUntil: T.string
    }).isRequired
  },
  defaultProps: {
    title: null,
    content: '',
    meta: {
      author: null,
      notifyUsers: false
    },
    restrictions: {
      visible: true,
      visibleFrom: null,
      visibleUntil: null
    }
  }
}

export {
  Announcement
}
