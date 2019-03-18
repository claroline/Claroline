import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {
  SCORE_SUM
} from '#/plugin/exo/quiz/enums'

import {ScoreType} from '#/plugin/exo/scores/prop-types'

/**
 * Definition of Quiz items.
 * It basically works like ItemDefinition in the PHP API.
 *
 * @type {object}
 */
const ItemType = {
  propTypes: {
    name: T.string.isRequired,
    type: T.string.isRequired,
    tags: T.arrayOf(T.string),
    answerable: T.bool.isRequired,

    components: T.shape({
      player: T.element.isRequired,
      editor: T.element.isRequired,
      paper: T.element.isRequired,
      statistics: T.element.isRequired
    }),

    create: T.func,
    validate: T.func
  },

  defaultProps: {
    tags: []
  }
}

const AnswerableItemType = implementPropTypes({}, ItemType, {
  // Returns the list of score types supported by the item type (arrayOf(ScoreType.propTypes))
  supportScores: T.func.isRequired,
  validateAnswer: T.func,
  correctAnswer: T.func,
  expectAnswer: T.func,
  getStatistics: T.func
}, {

})

const Item = {
  propTypes: {
    id: T.string.isRequired,
    content: T.string.isRequired,
    description: T.string,
    meta: T.shape({

    }),
    score: T.shape({
      type: T.string.isRequired
    })
  },

  defaultProps: {
    title: '',
    description: '',
    meta: {
      protectQuestion: false,
      mandatory: false
    },
    rights: {
      edit: true
    },
    hints: [],
    feedback: '',
    objects: [],
    score: {
      type: SCORE_SUM,
      success: 1,
      failure: 0
    },
    tags: []
  }
}

const ItemEditor = {
  propTypes: {
    formName: T.string.isRequired,
    path: T.string,
    disabled: T.bool,
    item: T.shape(
      Item.propTypes
    ).isRequired,
    update: T.func.isRequired
  },

  defaultProps: {
    disabled: false
  }
}

export {
  ItemType,
  AnswerableItemType,
  Item,
  ItemEditor
}
