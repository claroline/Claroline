import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import has from 'lodash/has'

import {trans} from '#/main/app/intl/translation'

import {AnswerStats} from '#/plugin/exo/components/answer-stats'
import {utils} from '#/plugin/exo/items/grid/utils/utils'

const GridStats = (props) =>
  <div className="grid-paper">
    <div className="grid-body">
      <table className="grid-table grid-stats-table">
        <tbody>
          {[...Array(props.item.rows)].map((x, i) =>
            <tr key={`grid-row-${i}`}>
              {[...Array(props.item.cols)].map((x, j) => {
                const cell = utils.getCellByCoordinates(j, i, props.item.cells)

                if(!cell.input) {
                  return(
                    <td key={`grid-row-${i}-col-${j}`} style={{border: `${props.item.border.width}px solid ${props.item.border.color}`}}>
                      <div className="grid-cell">
                        <div className="cell-body">{cell.data}</div>
                      </div>
                    </td>
                  )
                } else {
                  return (
                    <td key={`grid-row-${i}-col-${j}`} style={{border: `${props.item.border.width}px solid ${props.item.border.color}`}}>
                      {utils.getCellSolutionAnswers(cell.id, props.item.solutions).map((answer, i) => {
                        const key = utils.getKey(cell.id, answer.text, props.item.solutions)

                        return (
                          <div
                            key={`expected-answer-${cell.id}-${i}`}
                            className={classes('answer-item', {'selected-answer': props.item.hasExpectedAnswers})}
                          >
                            <div>{answer.text}</div>

                            <AnswerStats stats={{
                              value: has(props.stats, ['cells', cell.id, key]) ?
                                props.stats.cells[cell.id][key] :
                                0,
                              total: props.stats.total
                            }} />
                          </div>
                        )
                      })}
                      {utils.getCellSolutionAnswers(cell.id, props.item.solutions, false).map((answer, i) => {
                        const key = utils.getKey(cell.id, answer.text, props.item.solutions)

                        return (
                          <div
                            key={`incorrect-answer-${cell.id}-${i}`}
                            className='answer-item stats-answer'
                          >
                            <div>{answer.text}</div>

                            <AnswerStats stats={{
                              value: has(props.stats, ['cells', cell.id, key]) ?
                                props.stats.cells[cell.id][key] :
                                0,
                              total: props.stats.total
                            }} />
                          </div>
                        )
                      })}
                      {has(props.stats, ['cells', cell.id, '_others']) &&
                        <div
                          key={`others-answer-${cell.id}-${i}`}
                          className='answer-item stats-answer'
                        >
                          <div>{trans('other_answers', {}, 'quiz')}</div>

                          <AnswerStats stats={{
                            value: props.stats.cells[cell.id]['_others'],
                            total: props.stats.total
                          }} />
                        </div>
                      }
                      {has(props.stats, ['cells', cell.id, '_unanswered']) &&
                        <div
                          key={`unanswered-answer-${cell.id}-${i}`}
                          className='answer-item unanswered-item'
                        >
                          <div>{trans('unanswered', {}, 'quiz')}</div>

                          <AnswerStats stats={{
                            value: props.stats.cells[cell.id]['_unanswered'],
                            total: props.stats.total
                          }} />
                        </div>
                      }
                    </td>
                  )
                }
              })}
            </tr>
          )}
        </tbody>
      </table>
    </div>
    <div className='answer-item unanswered-item'>
      <div>{trans('unanswered', {}, 'quiz')}</div>

      <AnswerStats stats={{
        value: props.stats.unanswered ? props.stats.unanswered : 0,
        total: props.stats.total
      }} />
    </div>
  </div>

GridStats.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    penalty: T.number.isRequired,
    sumMode: T.string.isRequired,
    score: T.object.isRequired,
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
    solutions: T.arrayOf(T.object).isRequired,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  stats: T.shape({
    cells: T.object,
    unanswered: T.number,
    total: T.number
  })
}

export {
  GridStats
}
