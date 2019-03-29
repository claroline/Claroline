import {PropTypes as T} from 'prop-types'

import {makeId} from '#/main/core/scaffolding/id'

const PairItem = {
  propTypes: {
    items: T.arrayOf(T.shape({
      id: T.string.isRequired,
      type: T.string.isRequired,
      data: T.string.isRequired,
      _deletable: T.bool
    })).isRequired,
    solutions: T.arrayOf(T.shape({
      itemIds: T.arrayOf(T.oneOfType([T.number, T.string])).isRequired,
      score: T.number,
      feedback: T.string,
      ordered: T.bool,
      _deletable: T.bool
    })),
    penalty: T.number.isRequired,
    random: T.bool.isRequired,
    rows: T.number.isRequired
  },
  defaultProps: {
    items: [
      {
        id: makeId(),
        type: 'text/html',
        data: '',
        _deletable: false
      },
      {
        id: makeId(),
        type: 'text/html',
        data: '',
        _deletable: false
      }
    ],
    solutions: [
      {
        itemIds: [-1, -1],
        score: 1,
        feedback: '',
        ordered: false,
        _deletable: false
      }
    ],
    penalty: 0,
    random: false,
    rows: 1
  }
}

export {
  PairItem
}
