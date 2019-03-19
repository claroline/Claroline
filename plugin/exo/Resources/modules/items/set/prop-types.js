import {PropTypes as T} from 'prop-types'

import {makeId} from '#/main/core/scaffolding/id'

const SetItem = {
  propTypes: {
    sets: T.arrayOf(T.shape({
      id: T.string.isRequired,
      type: T.string.isRequired,
      data: T.string.isRequired,
      _deletable: T.bool
    })).isRequired,
    items: T.arrayOf(T.shape({
      id: T.string.isRequired,
      type: T.string.isRequired,
      data: T.string.isRequired,
      _deletable: T.bool
    })).isRequired,
    solutions: T.shape({
      associations: T.arrayOf(T.shape({
        itemId: T.string,
        setId: T.string,
        score: T.number,
        feedback: T.string,
        _itemData: T.string
      })).isRequired,
      odd: T.arrayOf(T.shape({
        itemId: T.string,
        score: T.number,
        feedback: T.string
      })).isRequired
    }),
    penalty: T.number.isRequired,
    random: T.bool.isRequired
  },
  defaultProps: {
    sets: [
      {
        id: makeId(),
        type: 'text/html',
        data: ''
      }
    ],
    items: [
      {
        id: makeId(),
        type: 'text/html',
        data: ''
      }
    ],
    solutions: {
      associations: [],
      odd: []
    },
    penalty: 0,
    random: false
  }
}

export {
  SetItem
}