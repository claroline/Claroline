import React from 'react'

import quizSelect from './../../selectors'
import {selectors as paperSelect} from './../../papers/selectors'
import {getDefinition, isQuestionType} from './../../../items/item-types'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import Panel from 'react-bootstrap/lib/Panel'
import {Metadata as ItemMetadata} from './../../../items/components/metadata.jsx'
import {NUMBERING_NONE} from './../../../quiz/enums'
import {getNumbering} from './../../../utils/numbering'
import {tex} from '#/main/core/translation'
import {generateUrl} from '#/main/core/fos-js-router'

const Statistics = props =>
  <div>
    <div className="panel panel-heading">
      <a className="btn btn-primary" href={generateUrl('exercise_papers_export_json', {'exerciseId': props.quiz.id})}> {tex('json_export')} </a>
      {' '}
      <a className="btn btn-primary" href={generateUrl('exercise_papers_export_csv', {'exerciseId': props.quiz.id})}> {tex('csv_export')} </a>
    </div>
    <div>
      {Object.keys(props.steps).map((key, idx) =>
        <div key={idx} className="quiz-item item-statistics">
          <h3 className="step-title">
            {props.steps[key].title ? props.steps[key].title : tex('step') + ' ' + (idx + 1)}
          </h3>

          {props.steps[key].items.map((itemUid, idxItem) => {
            let item = props.items[itemUid]
            return isQuestionType(item.type) ?

                <Panel key={item.id}>
                {item.title &&
                  <h4 className="item-title">{item.title}</h4>
                }

                {React.createElement(
                  getDefinition(item.type).paper,
                  {
                    item,
                    showYours: false,
                    showExpected: false,
                    showStats: true,
                    showScore: false,
                    stats: getDefinition(item.type).generateStats(item, props.papers, props.allPapersStatistics)
                  }
                )}

                <ItemMetadata
                  item={item}
                  numbering={props.numbering !== NUMBERING_NONE ? (idx + 1) + '.' + getNumbering(props.numbering, idxItem): null}
                />

                </Panel>
               :
              ''
          })}
        </div>
      )}
    </div>
  </div>

Statistics.propTypes = {
  papers: T.object.isRequired,
  steps: T.object.isRequired,
  items: T.object.isRequired,
  quiz: T.object.isRequired
}

function mapStateToProps(state) {
  return {
    papers: paperSelect.papers(state),
    numbering: quizSelect.quizNumbering(state),
    steps: quizSelect.steps(state),
    items: quizSelect.items(state)
  }
}

const ConnectedStatistics = connect(mapStateToProps)(Statistics)

export {ConnectedStatistics as Statistics}
