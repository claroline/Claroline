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
    showResourceHeader: T.bool,
    secondaryResources: T.arrayOf(T.shape({
      // minimal resource
    }))
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
    )),
    overview: T.shape({
      display: T.bool,
      message: T.string,
      resource: T.shape({
        id: T.string.isRequired,
        meta: T.shape({
          type: T.string.isRequired
        })
      })
    }),
    end: T.shape({
      display: T.bool,
      message: T.string,
      navigation: T.bool
    })
  },
  defaultProps: {
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
