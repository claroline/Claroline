import {PropTypes as T} from 'prop-types'

const ScoreType = {
  propTypes: {
    /**
     * The name of the score type.
     *
     * @var {string}
     */
    name: T.string.isRequired,

    meta: T.shape({
      /**
       * The display label of the score type
       *
       * @var {string}
       */
      label: T.string.isRequired,

      /**
       * A small description of the rules of the score type.
       *
       * @var {string}
       */
      description: T.string.isRequired
    }).isRequired,

    /**
     * The score type allows the user to define a score by available answer.
     *
     * @var {bool}
     */
    hasAnswerScores: T.bool.isRequired,

    /**
     * A function to return the list of configuration field of the score type.
     * NB. fields definition follows the FormData format.
     *
     * @var {function}
     */
    configure: T.func.isRequired,

    /**
     * A function to calculate the score of a quiz item based on the score type.
     *
     * @var {function}
     */
    calculate: T.func.isRequired,

    /**
     * A function to calculate the total score of a quiz item based on the score type.
     *
     * @var {function}
     */
    calculateTotal: T.func.isRequired
  },

  defaultProps: {

  }
}

const ScoreRule = {
  propTypes: {
    type: T.string.isRequired
    // others props depends on the type
  },
  defaultProps: {
    type: 'sum',

    // not really aesthetic (this is the default for "fixed" type)
    success: 1,
    failure: 0
  }
}

export {
  ScoreType,
  ScoreRule
}
