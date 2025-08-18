import React, {Fragment} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {displayDate, displayDuration, getTimeDiff} from '#/main/app/intl/date'
import {hasPermission} from '#/main/app/security'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentTitle} from '#/main/app/content/components/title'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {UserMicro} from '#/main/core/user/components/micro'
import {displayUsername} from '#/main/community/utils'
import {ScoreGauge} from '#/main/core/layout/gauge/components/score'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {selectors as statSelectors} from '#/plugin/exo/resources/quiz/statistics/store'
import {isQuestionType} from '#/plugin/exo/items/item-types'
import {showScore} from '#/plugin/exo/resources/quiz/papers/restrictions'
import {getNumbering} from '#/plugin/exo/resources/quiz/utils'
import {Paper as PaperTypes} from '#/plugin/exo/resources/quiz/papers/prop-types'
import {actions, selectors} from '#/plugin/exo/resources/quiz/papers/store'
import {ResourcePage} from '#/main/core/resource'
import {PageContent} from '#/main/app/page'
import {Item as ItemTypes} from '#/plugin/exo/items/prop-types'
import {ItemResult} from '#/plugin/exo/items/components/result'

function getAnswer(itemId, answers) {
  return answers.find(answer => answer.questionId === itemId)
}

function getStats(itemId, stats = {}) {
  return stats[itemId] ? stats[itemId] : {}
}

const PaperStep = props => {
  const numbering = getNumbering(props.numberingType, props.index)

  return (
    <Fragment>
      {props.showTitle &&
        <ContentTitle
          level={4}
          displayLevel={3}
          numbering={numbering}
          title={props.title || trans('step', {number: props.index + 1}, 'quiz')}
        />
      }

      {props.items
        .filter((item) => isQuestionType(item.type))
        .map((item, idxItem) =>
          <ItemResult
            key={item.id}
            item={item}
            numbering={getNumbering(props.questionNumberingType, props.index, idxItem)}
            userAnswer={getAnswer(item.id, props.answers)}
            stats={getStats(item.id, props.stats)}
            showTitle={props.showQuestionTitles}
            showScore={props.showScore}
            showExpectedAnswers={props.showExpectedAnswers}
            showStatistics={props.showStatistics}
          />
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
  items: T.arrayOf(T.shape(
    ItemTypes.propTypes
  )),
  showScore: T.bool.isRequired,
  showExpectedAnswers: T.bool.isRequired,
  showStatistics: T.bool.isRequired,
  answers: T.array,
  stats: T.object
}

const PaperComponent = props =>
  <ResourcePage>
    <PageContent className="paper container pt-4 pb-5">
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
          {
            name: 'delete',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-trash',
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
          <div className="card mb-3">
            <div className="card-header">
              <UserMicro
                className="content-creator"
                {...get(props.paper, 'user', {})}
                link={true}
              />
            </div>

            <div className="card-body text-center">
              <ScoreGauge
                type="user"
                value={get(props.paper, 'score')}
                total={get(props.paper, 'total')}
                width={140}
                height={140}
                displayValue={value => undefined === value || null === value ? '?' : value+''}
              />
            </div>

            <ul className="list-group list-group-flush list-group-values">
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
    </PageContent>
  </ResourcePage>

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
      const admin = hasPermission('edit', resourceSelect.resourceNode(state)) || hasPermission('follow', resourceSelect.resourceNode(state))
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
