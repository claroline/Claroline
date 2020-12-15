import {PropTypes as T} from 'prop-types'

const Message = {
  propTypes: {
    id: T.string,
    title: T.string,
    content: T.string,
    workspace: T.shape({
      uuid: T.string.isRequired
    })
  }
}

const Notification = {
  propTypes: {
    id: T.string,
    parameters: T.shape({
      action: T.string,
      interval: T.number,
      byMail: T.bool,
      byMessage: T.bool
    }),
    workspace: T.shape({
      uuid: T.string.isRequired
    }),
    message: T.shape(Message.propTypes),
    roles: T.arrayOf(T.shape({
      id: T.string.isRequired,
      name: T.string.isRequired,
      translationKey: T.string.isRequired
    }))
  }
}

export {
  Message,
  Notification
}