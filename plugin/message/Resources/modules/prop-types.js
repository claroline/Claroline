import {PropTypes as T} from 'prop-types'

import {User} from '#/main/core/user/prop-types'

const Message = {
  propTypes: {
    id: T.string,
    content: T.string,
    object: T.string,
    to: T.string,
    from: T.shape(User.propTypes),
    meta: T.shape({
      date: T.string.isRequired,
      read: T.bool.isRequired,
      removed: T.bool.isRequired,
      sent: T.bool.isRequired
    })
  },
  defaultProps: {
    meta: {
      removed : false
    }
  }
}

const ContactCategory = {
  propTypes: {
    id: T.number.isRequired,
    name: T.string.isRequired,
    order: T.number,
    user: T.shape(
      User.propTypes
    ).isRequired
  }
}

const Contact = {
  propTypes: {
    id: T.number.isRequired,
    user: T.shape(
      User.propTypes
    ).isRequired,
    data: T.shape(
      User.propTypes
    ).isRequired,
    categories: T.arrayOf(T.shape(
      ContactCategory.propTypes
    ))
  }
}

export {
  Message,
  Contact,
  ContactCategory
}
