import {PropTypes as T} from 'prop-types'

import {makeId} from '#/main/core/scaffolding/id'

import {ScoreRule} from '#/plugin/exo/scores/prop-types'
import {constants} from '#/plugin/exo/items/grid/constants'

function makeDefaultCell(x, y) {
  return {
    id: makeId(), // FIXME : uuid will not be unique if many grid items are created in the same time.
    data: '',
    coordinates: [x, y],
    background: '#fff',
    color: '#333',
    _multiple: false,
    choices: [],
    input: false
  }
}

const GridItem = {
  propsTypes: {
    item: T.shape({
      id: T.string.isRequired,
      penalty: T.number.isRequired,
      sumMode: T.string,
      score: T.shape(
        ScoreRule.propTypes
      ),
      cells: T.arrayOf(T.shape({
        id: T.string.isRequired,
        data: T.string.isRequired,
        coordinates: T.arrayOf(T.number).isRequired,
        background: T.string.isRequired,
        color: T.string.isRequired,
        choices: T.arrayOf(T.string),
        input: T.bool.isRequired
      })).isRequired,
      rows: T.number.isRequired,
      cols: T.number.isRequired,
      border:  T.shape({
        width: T.number.isRequired,
        color: T.string.isRequired
      }).isRequired,
      solutions: T.arrayOf(T.shape({
        cellId: T.string.isRequired,
        answers: T.arrayOf(T.shape({
          text: T.string,
          caseSensitive: T.bool,
          expected: T.bool,
          score: T.number,
          feedback: T.string
        })).isRequired
      })).isRequired,
      _errors: T.object,
      _popover: T.string
    }).isRequired
  },
  defaultProps: {
    random: false,
    penalty: 0,
    sumMode: constants.SUM_CELL,
    score: ScoreRule.defaultProps,
    cells: [
      makeDefaultCell(0,0),
      makeDefaultCell(0,1),
      makeDefaultCell(1,0),
      makeDefaultCell(1,1)
    ],
    rows: 2,
    cols: 2,
    border: {
      color: '#DDDDDD',
      width: 1
    },
    solutions: []
  }
}

export {
  GridItem
}
