import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import Panel from 'react-bootstrap/lib/Panel'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON} from '#/main/app/buttons'
import {selectors as resourceSelect} from '#/main/core/resource/store'

import quizSelect from '#/plugin/exo/quiz/selectors'
import {getDefinition, isQuestionType} from '#/plugin/exo/items/item-types'
import {Metadata as ItemMetadata} from '#/plugin/exo/items/components/metadata'
import {constants} from '#/plugin/exo/resources/quiz/constants'
import {getNumbering} from '#/plugin/exo/utils/numbering'

const Statistics = props =>
  <div className="quiz-statistics">
    {props.quiz.steps.map((step, idx) =>
      <div key={idx} className="quiz-item item-paper">
        <h3 className="h4">
          {step.title ? step.title : trans('step', {}, 'quiz') + ' ' + (idx + 1)}
        </h3>
        {step.items.map((item, idxItem) => {
          return isQuestionType(item.type) && props.stats && props.stats[item.id] &&
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
                  stats: props.stats && props.stats[item.id] ? props.stats[item.id] : {}
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
  quiz: T.object.isRequired,
  stats: T.object
}

const ConnectedStatistics = connect(
  (state) => ({
    workspaceId: resourceSelect.workspaceId(state),
    quiz: quizSelect.quiz(state),
    numbering: quizSelect.quizNumbering(state),
    stats: quizSelect.statistics(state)
  })
)(Statistics)

export {
  ConnectedStatistics as Statistics
}
