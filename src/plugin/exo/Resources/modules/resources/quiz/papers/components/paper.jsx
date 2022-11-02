import React, {Fragment} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import classes from 'classnames'

import Panel from 'react-bootstrap/lib/Panel'

import {withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {displayDate, displayDuration, getTimeDiff} from '#/main/app/intl/date'
import {hasPermission} from '#/main/app/security'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentTitle} from '#/main/app/content/components/title'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {UserMicro} from '#/main/core/user/components/micro'
import {displayUsername} from '#/main/community/utils'
import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box'
import {ScoreGauge} from '#/main/core/layout/gauge/components/score'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {ContentHtml} from '#/main/app/content/components/html'
import {isHtmlEmpty} from '#/main/app/data/types/html/validators'

import {calculateTotal} from '#/plugin/exo/items/score'
import {selectors as statSelectors} from '#/plugin/exo/resources/quiz/statistics/store'
import {getDefinition, isQuestionType} from '#/plugin/exo/items/item-types'
import {showScore} from '#/plugin/exo/resources/quiz/papers/restrictions'
import {getNumbering} from '#/plugin/exo/resources/quiz/utils'
import {Metadata as ItemMetadata} from '#/plugin/exo/items/components/metadata'
import {Paper as PaperTypes} from '#/plugin/exo/resources/quiz/papers/prop-types'
import {actions, selectors} from '#/plugin/exo/resources/quiz/papers/store'
import ScoreNone from '#/plugin/exo/scores/none'

// TODO : show used hints

function getAnswer(itemId, answers) {
  const answer = answers.find(answer => answer.questionId === itemId)

  return answer && answer.data ? answer.data : undefined
}

function getAnswerFeedback(itemId, answers) {
  const answer = answers.find(answer => answer.questionId === itemId)

  return answer && answer.feedback ? answer.feedback : null
}

function getAnswerScore(itemId, answers) {
  const answer = answers.find(answer => answer.questionId === itemId)

  return answer ? answer.score : undefined
}

const PaperStep = props => {
  const numbering = getNumbering(props.numberingType, props.index)

  return (
    <Fragment>
      {props.showTitle &&
        <ContentTitle
          className={classes({
            'h-first': 0 === props.index
          })}
          level={4}
          displayLevel={3}
          numbering={numbering}
          title={props.title || trans('step', {number: props.index + 1}, 'quiz')}
        />
      }

      {props.items
        .filter((item) => isQuestionType(item.type))
        .map((item, idxItem) =>
          <Panel key={item.id} className="quiz-item item-paper">
            {props.showScore && item.hasExpectedAnswers && ScoreNone.name !== get(item, 'score.type') && getAnswerScore(item.id, props.answers) !== undefined && getAnswerScore(item.id, props.answers) !== null &&
              <ScoreBox className="pull-right" score={getAnswerScore(item.id, props.answers)} scoreMax={calculateTotal(item)}/>
            }

            {props.showQuestionTitles && item.title &&
              <h4 className="item-title">{item.title}</h4>
            }

            <ItemMetadata item={item} numbering={getNumbering(props.questionNumberingType, props.index, idxItem)} />

            {React.createElement(getDefinition(item.type).paper, {
              item: item,
              answer: getAnswer(item.id, props.answers),
              feedback: getAnswerFeedback(item.id, props.answers),
              showScore: item.hasExpectedAnswers && ScoreNone.name !== get(item, 'score.type') && props.showScore,
              showExpected: props.showExpectedAnswers && item.hasExpectedAnswers,
              showStats: !!(props.showStatistics && props.stats && props.stats[item.id]),
              showYours: true,
              stats: props.showStatistics && props.stats && props.stats[item.id] ? props.stats[item.id] : {}
            })}

            {(item.feedback && !isHtmlEmpty(item.feedback)) &&
              <div className="item-feedback">
                <span className="fa fa-comment" />
                <ContentHtml>{item.feedback}</ContentHtml>
              </div>
            }
          </Panel>
        )
      }
    </Fragment>
  )
}

PaperStep.propTypes = {
  numberingType: T.string.isRequired,
  questionNumberingType: T.string.isRequired,
  showTitle: T.bool,
  showQuestionTitles: T.bool,
  index: T.number.isRequired,
  id: T.string.isRequired,
  title: T.string,
  items: T.arrayOf(T.shape({
    // TODO : prop types
  })),

  showScore: T.bool.isRequired,
  showExpectedAnswers: T.bool.isRequired,
  showStatistics: T.bool.isRequired,
  answers: T.array,
  stats: T.object
}

const PaperComponent = props =>
  <div className="paper">
    <ContentTitle
      level={3}
      displayLevel={2}
      title={trans('results', {}, 'quiz')}
      subtitle={props.paper ?
        trans('attempt', {number: get(props.paper, 'number', '?')}, 'quiz')
        :
        trans('attempt_loading', {}, 'quiz')
      }
      actions={[
        /*{
         name: 'about',
         type: MODAL_BUTTON,
         icon: 'fa fa-fw fa-info',
         label: trans('show-info', {}, 'actions'),
         modal: []
         },*/ {
          name: 'delete',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash-o',
          label: trans('delete', {}, 'actions'),
          displayed: props.admin,
          callback: () => props.delete(props.quizId, props.paper).then(() => {
            props.history.push(`${props.path}/papers`)
          }),
          confirm: {
            title: trans('deletion'),
            subtitle: trans('user_attempt', {
              number: get(props.paper, 'number', '?'),
              userName: displayUsername(get(props.paper, 'user'))
            }, 'quiz'),
            message: trans('remove_paper_confirm_message', {}, 'quiz')
          },
          dangerous: true,
          group: trans('management')
        }
      ]}
    />

    <div className="row">
      <div className="col-md-4">
        <div className="panel panel-default">
          <div className="panel-heading">
            <UserMicro
              className="content-creator"
              {...get(props.paper, 'user', {})}
              link={true}
            />
          </div>

          <div className="panel-body text-center">
            <ScoreGauge
              type="user"
              value={get(props.paper, 'score')}
              total={get(props.paper, 'total')}
              width={140}
              height={140}
              displayValue={value => undefined === value || null === value ? '?' : value+''}
            />
          </div>

          <ul className="list-group list-group-values">
            <li className="list-group-item">
              {trans('start_date')}
              <span className="value">{get(props.paper, 'startDate') ? displayDate(props.paper.startDate, false, true) : '-'}</span>
            </li>

            <li className="list-group-item">
              {trans('end_date')}
              <span className="value">{get(props.paper, 'endDate') ? displayDate(props.paper.endDate, false, true) : '-'}</span>
            </li>

            <li className="list-group-item">
              {trans('duration')}
              <span className="value">{get(props.paper, 'endDate') ? displayDuration(getTimeDiff(props.paper.startDate, props.paper.endDate)) : '-'}</span>
            </li>
          </ul>
        </div>
      </div>

      <div className="col-md-8">
        {!props.paper &&
          <ContentLoader />
        }

        {props.paper && props.paper.structure.steps
          .filter(step => step.items && 0 < step.items.length)
          .map((step, index) =>
            <PaperStep
              key={step.id}
              showTitle={props.showTitles}
              showQuestionTitles={props.showQuestionTitles}
              numberingType={props.numberingType}
              questionNumberingType={props.questionNumberingType}
              index={index}
              id={step.id}
              title={step.title}
              items={step.items}
              answers={props.paper.answers}
              stats={props.stats}
              showScore={props.showScore}
              showExpectedAnswers={props.showExpectedAnswers}
              showStatistics={props.showStatistics}
            />
          )
        }
      </div>
    </div>
  </div>

PaperComponent.propTypes = {
  path: T.string.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  quizId: T.string.isRequired,
  admin: T.bool.isRequired,
  paper: T.shape(
    PaperTypes.propTypes
  ),
  showTitles: T.bool,
  showQuestionTitles: T.bool,
  numberingType: T.string,
  questionNumberingType: T.string,
  showScore: T.bool.isRequired,
  showExpectedAnswers: T.bool.isRequired,
  showStatistics: T.bool.isRequired,
  stats: T.object,
  delete: T.func.isRequired
}

const Paper = withRouter(
  connect(
    (state) => {
      const admin = hasPermission('edit', resourceSelect.resourceNode(state)) || hasPermission('manage_papers', resourceSelect.resourceNode(state))
      const paper = selectors.currentPaper(state)

      return ({
        path: resourceSelect.path(state),
        quizId: selectors.quizId(state),
        admin: admin,
        paper: paper,
        showScore: paper ? showScore(paper, admin) : false,
        showTitles: selectors.showTitles(state),
        showQuestionTitles: selectors.showQuestionTitles(state),
        numberingType: selectors.currentNumbering(state),
        questionNumberingType: selectors.currentQuestionNumbering(state),
        showExpectedAnswers: selectors.showExpectedAnswers(state),
        showStatistics: selectors.showStatistics(state),
        stats: statSelectors.statistics(state)
      })
    },
    (dispatch) => ({
      delete(quizId, paper) {
        return dispatch(actions.deletePapers(quizId, [paper]))
      }
    })
  )(PaperComponent)
)

export {
  Paper
}
