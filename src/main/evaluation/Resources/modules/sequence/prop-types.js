import {PropTypes as T} from 'prop-types'

import {User} from '#/main/community/user/prop-types'

const Step = {
  propTypes: {
    id: T.string.isRequired,
    slug: T.string.isRequired,
    title: T.string,
    description: T.string,
    poster: T.string,
    objective: T.string,
    estimatedDuration: T.number,
    primaryResource: T.shape({
      id: T.string.isRequired,
      meta: T.shape({
        type: T.string.isRequired
      })
    }),
    secondaryResources: T.arrayOf(T.shape({
      // minimal resource
    }))
  }
}

const Sequence = {
  propTypes: {
    id: T.string.isRequired,
    name: T.string,
    poster: T.string,
    objective: T.string,
    meta: T.shape({
      published: T.bool,
      description: T.string,
      descriptionHtml: T.string
    }),
    display: T.shape({
      numbering: T.oneOf(['none', 'numeric', 'literal']),
      showScore: T.bool
    }),
    score: T.shape({
      success: T.number,
      total: T.number
    }),
    steps: T.arrayOf(T.shape(
      Step.propTypes
    )),
    overview: T.shape({
      // message: T.string,
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
    meta: {
      published: false
    },
    display: {
      numbering: 'none'
    },
    steps: []
  }
}

const SequenceEvaluation = {
  propTypes: {
    id: T.string.isRequired,
    meta: {
      archived: T.bool
    },
    lastActivityAt: T.string,
    startedAt: T.string,
    endedAt: T.string,
    status: T.string.isRequired,
    duration: T.number,
    score: T.number,
    scoreMin: T.number,
    scoreMax: T.number,
    progression: T.number,
    certified: T.bool,
    sequence: T.shape(
      Sequence.propTypes
    ),
    user: T.shape(
      User.propTypes
    ),
    estimatedDuration: T.number
  }
}

export {
  Sequence,
  Step,
  SequenceEvaluation
}
