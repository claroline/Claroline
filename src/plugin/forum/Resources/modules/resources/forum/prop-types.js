import {PropTypes as T} from 'prop-types'

const Forum = {
  propTypes: {
    id: T.string.isRequired,
    display: T.shape({
      description: T.string,
      showOverview: T.bool.isRequired
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

  }
}

export {
  Forum
}
