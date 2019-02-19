import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import Panel from 'react-bootstrap/lib/Panel'

import {trans, tex} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON} from '#/main/app/buttons'
import {selectors as resourceSelect} from '#/main/core/resource/store'

import quizSelect from './../../selectors'
import {selectors as paperSelect} from './../../papers/selectors'
import {getDefinition, isQuestionType} from './../../../items/item-types'
import {Metadata as ItemMetadata} from './../../../items/components/metadata'
import {constants} from '#/plugin/exo/resources/quiz/constants'
import {getNumbering} from './../../../utils/numbering'

const Statistics = props =>
  <div className="quiz-statistics">
    {Object.keys(props.steps).map((key, idx) =>
      <div key={idx} className="quiz-item item-paper">
        <h3 className="h4">
          {props.steps[key].title ? props.steps[key].title : tex('step') + ' ' + (idx + 1)}
        </h3>

        {props.steps[key].items.map((itemUid, idxItem) => {
          let item = props.items[itemUid]
          return isQuestionType(item.type) &&
            <Panel key={item.id}>
              {item.title &&
                <h4 className="item-title">{item.title}</h4>
              }

              <ItemMetadata
                item={item}
                numbering={props.numbering !== constants.NUMBERING_NONE ? (idx + 1) + '.' + getNumbering(props.numbering, idxItem): null}
              />

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
            </Panel>
        })}
      </div>
    )}

    {props.workspaceId &&
      <Button
        type={URL_BUTTON}
        className="btn btn-block btn-emphasis"
        icon="fa fa-fw fa-home"
        label={trans('return-home', {}, 'actions')}
        target={['claro_workspace_open', {workspaceId: props.workspaceId}]}
      />
    }
  </div>

Statistics.propTypes = {
  workspaceId: T.number,
  numbering: T.string,
  allPapersStatistics: T.bool,
  papers: T.object.isRequired,
  steps: T.object.isRequired,
  items: T.object.isRequired,
  quiz: T.object.isRequired
}

const ConnectedStatistics = connect(
  (state) => ({
    workspaceId: resourceSelect.workspaceId(state),
    quiz: quizSelect.quiz(state),
    papers: paperSelect.papers(state),
    numbering: quizSelect.quizNumbering(state),
    steps: quizSelect.steps(state),
    items: quizSelect.items(state)
  })
)(Statistics)

export {
  ConnectedStatistics as Statistics
}
