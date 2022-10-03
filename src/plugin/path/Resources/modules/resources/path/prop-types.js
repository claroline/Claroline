import {PropTypes as T} from 'prop-types'

const Step = {
  propTypes: {
    id: T.string.isRequired,
    slug: T.string.isRequired,
    title: T.string,
    description: T.string,
    poster: T.string,
    display: T.shape({
      numbering: T.string
    }).isRequired,
    primaryResource: T.shape({
      id: T.string.isRequired,
      meta: T.shape({
        type: T.string.isRequired
      })
    }),
    evaluated: T.bool,
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
    meta: T.shape({
      description: T.string,
      endMessage: T.string
    }),
    display: T.shape({
      showOverview: T.bool,
      showEndPage: T.bool,
      numbering: T.oneOf(['none', 'numeric', 'literal', 'custom']),
      manualProgressionAllowed: T.bool,
      showScore: T.bool
    }).isRequired,
    score: T.shape({
      success: T.number,
      total: T.number
    }),
    opening: T.shape({
      secondaryResources: T.oneOf(['_self', '_blank'])
    }),
    steps: T.arrayOf(T.shape(
      Step.propTypes
    ))
  },
  defaultProps: {
    meta: {},
    display: {
      showOverview: false,
      showEndPage: false,
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
