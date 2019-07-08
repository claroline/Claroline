import {PropTypes as T} from 'prop-types'

const Step = {
  propTypes: {
    id: T.string.isRequired,
    title: T.string,
    description: T.string,
    poster: T.shape({
      url: T.string
    }),
    display: T.shape({
      numbering: T.string,
      height: T.number
    }).isRequired,
    primaryResource: T.shape({
      autoId: T.number.isRequired,
      meta: T.shape({
        type: T.string.isRequired
      })
    }),
    showResourceHeader: T.bool,
    secondaryResources: T.arrayOf(T.shape({
      // minimal resource
    })),
    userProgression: T.shape({
      status: T.string
    })
  },
  defaultProps: {
    description: null,
    display: {},
    showResourceHeader: false,
    secondaryResources: [],
    children: []
  }
}

const Path = {
  propTypes: {
    id: T.string.isRequired,
    display: T.shape({
      showOverview: T.bool,
      numbering: T.oneOf(['none', 'numeric', 'literal', 'custom']),
      manualProgressionAllowed: T.bool
    }).isRequired,
    opening: T.shape({
      secondaryResources: T.oneOf(['_self', '_blank'])
    }),
    steps: T.arrayOf(T.shape(
      Step.propTypes
    ))
  },
  defaultProps: {
    display: {
      showOverview: false,
      numbering: 'none',
      manualProgressionAllowed: false
    },
    opening: {
      secondaryResources: '_self'
    },
    steps: []
  }
}

export {
  Step,
  Path
}
