import {PropTypes as T} from 'prop-types'

const Forum = {
  propTypes: {
    id: T.string.isRequired,
    display: T.shape({
      description: T.string,
      showOverview: T.bool.isRequired,
      messageOrder: T.oneOf(['ASC', 'DESC']),
      expandComments: T.bool
    }),
    restrictions: T.shape({}),
    meta: T.shape({
      users: T.number.isRequired,
      subjects: T.number.isRequired,
      messages: T.number.isRequired,
      tags: T.arrayOf(T.shape({
        id: T.string,
        name: T.string
      }))
    })
  },
  defaultProps: {
    display: {
      messageOrder: 'ASC',
      expandComments: false
    }
  }
}

export {
  Forum
}
