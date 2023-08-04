import {PropTypes as T} from 'prop-types'

const Field = {
  propTypes: {
    id: T.string,
    type: T.string.isRequired,
    name: T.string,
    label: T.string,
    help: T.string,
    required: T.bool,
    options: T.object, // options depend on the type of the field
    display: T.shape({
      order: T.number,
      condition: T.shape({
        field: T.string,
        comparator: T.string,
        value: T.string
      })
    }),
    restrictions: T.shape({
      confidentiality: T.oneOf(['none', 'owner', 'manager']),
      locked: T.bool,
      lockedEditionOnly: T.bool
    })
  },
  defaultProps: {
    required: false,
    options: {},
    display: {},
    restrictions: {
      confidentiality: 'none',
      locked: false,
      lockedEditionOnly: false
    }
  }
}

export {
  Field
}
