import React from 'react'
import {PropTypes as T} from 'prop-types'

import {tex} from '#/main/core/translation'

import {PaperTabs} from '../components/paper-tabs.jsx'
import {SelectionText} from './utils/selection-text.jsx'
import {getReactAnswerSelections} from './utils/selection-answer.jsx'

export const SelectionPaper = (props) => {
  return (
    <PaperTabs
      item={props.item}
      showExpected={props.showExpected}
      showStats={props.showStats}
      showYours={props.showYours}
      id={props.item.id}
      yours={
        (<div>
          {props.item.mode === 'find' &&
            <div className="panel-body">
              <span className="btn btn-danger" style={{ cursor: 'default'}}>
                {tex('selection_missing_click')} <span className="badge">{props.item.penalty}</span>
              </span>
              {'\u00a0'}
              <span className="btn btn-primary" style={{ cursor: 'default'}}>
                {tex('try_used')} <span className="badge"> {props.answer ? props.answer.tries: 0} </span>
              </span>
            </div>
          }
          <SelectionText
            anchorPrefix="selection-element-yours"
            text={props.item.text}
            selections={getReactAnswerSelections(props.item, props.answer, true, false)}
          />
        </div>)
      }
      expected={
        <SelectionText
          anchorPrefix="selection-element-expected"
          text={props.item.text}
          selections={getReactAnswerSelections(props.item, props.answer, true, true)}
        />
      }
      stats={
        <div>No implementation</div>
      }
    />
  )
}

SelectionPaper.propTypes = {
  item: T.shape({
    text: T.string.isRequired,
    mode: T.string.isRequired,
    selections: T.arrayOf(T.shape({})),
    id: T.string.isRequired,
    title: T.string.isRequired,
    description: T.string.isRequired,
    solutions: T.arrayOf(T.shape({})),
    penalty: T.number
  }).isRequired,
  answer: T.oneOfType([
    T.shape({
      selections: T.arrayOf(
        T.string
      ),
      mode: T.string.isRequired
    }),
    T.shape({
      highlights: T.arrayOf(
        T.shape({
          selectionId: T.string.isRequired,
          colorId: T.string.isRequired
        })
      ),
      mode: T.string.isRequired
    }),
    T.shape({
      tries: T.number.isRequired,
      positions: T.arrayOf(
        T.number
      ),
      mode: T.string.isRequired
    })
  ]).isRequired,
  showExpected: T.bool.isRequired,
  showYours: T.bool.isRequired,
  showStats: T.bool.isRequired
}
