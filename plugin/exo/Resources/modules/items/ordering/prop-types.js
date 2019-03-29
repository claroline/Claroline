import {PropTypes as T} from 'prop-types'

import {makeId} from '#/main/core/scaffolding/id'

import {constants} from '#/plugin/exo/items/ordering/constants'

const firstChoiceId = makeId()
const secondChoiceId = makeId()

const OrderingItem = {
  propTypes: {
    items: T.arrayOf(T.shape({
      id: T.string.isRequired,
      type: T.string.isRequired,
      data: T.string.isRequired,
      _score: T.number,
      _position: T.number,
      _feedback: T.string,
      _deletable: T.bool
    })).isRequired,
    solutions: T.arrayOf(T.shape({
      itemId: T.string.isRequired,
      score: T.number,
      position: T.number,
      feedback: T.string
    })),
    mode: T.string.isRequired,
    direction: T.string.isRequired,
    penalty: T.number.isRequired
  },
  defaultProps: {
    items: [
      {
        id: firstChoiceId,
        type: 'text/html',
        data: '',
        _score: 1,
        _position: 1,
        _feedback: ''
      },
      {
        id: secondChoiceId,
        type: 'text/html',
        data: '',
        _score: 1,
        _position: 2,
        _feedback: ''
      }
    ],
    solutions: [
      {
        itemId: firstChoiceId,
        score: 1,
        position: 1,
        feedback: ''
      },
      {
        itemId: secondChoiceId,
        score: 1,
        position: 2,
        feedback: ''
      }
    ],
    mode: constants.MODE_INSIDE,
    direction: constants.DIRECTION_VERTICAL,
    penalty: 0
  }
}

export {
  OrderingItem
}
