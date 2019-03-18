import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

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
    calculate: T.func.isRequired
  },

  defaultProps: {

  }
}

export {
  ScoreType
}
