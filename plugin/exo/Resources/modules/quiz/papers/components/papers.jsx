import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {getTimeDiff} from '#/main/app/intl/date'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box.jsx'

import quizSelect from '#/plugin/exo/quiz/selectors'
import {selectors as paperSelect} from '#/plugin/exo/quiz/papers/selectors'
import {utils} from '#/plugin/exo/quiz/papers/utils'

const Papers = props =>
  <Fragment>
    <h3 className="h2">
      {trans('results', {}, 'quiz')}
    </h3>

    <ListData
      name={`${quizSelect.STORE_NAME}.papers.list`}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        label: trans('open'),
        target: `/papers/${row.id}`
      })}
      fetch={{
        url: ['exercise_paper_list', {exerciseId: props.quiz.id}],
        autoload: true
      }}
      definition={[
        {
          name: 'user',
          label: trans('user'),
          displayed: true,
          render: (rowData) => rowData.user ? rowData.user.name : trans('anonymous')
        }, {
          name: 'number',
          label: '#',
          displayed: true,
          type: 'number'
        }, {
          name: 'startDate',
          alias: 'start',
          label: trans('start_date'),
          displayed: true,
          filterable: false,
          type: 'date',
          options: {
            time: true
          }
        }, {
          name: 'endDate',
          alias: 'end',
          label: trans('end_date'),
          displayed: true,
          filterable: false,
          type: 'date',
          options: {
            time: true
          }
        }, {
          name: 'duration',
          label: trans('duration'),
          displayed: true,
          filterable: false,
          sortable: false,
          render: (rowData) => {
            if (rowData.startDate && rowData.endDate) {
              const duration = getTimeDiff(rowData['startDate'], rowData['endDate'])

              return `${Math.round(duration / 60)}`
            } else {
              return '-'
            }
          }
        }, {
          name: 'finished',
          label: trans('finished'),
          displayed: true,
          type: 'boolean'
        }, {
          name: 'score',
          label: trans('score'),
          displayed: true,
          filterable: false,
          sortable: false,
          render: (rowData) => utils.showScore(props.admin, rowData.finished, paperSelect.showScoreAt(rowData), paperSelect.showCorrectionAt(rowData), paperSelect.correctionDate(rowData)) ?
            rowData.score || 0 === rowData.score ?
              <ScoreBox size="sm" score={rowData.score} scoreMax={paperSelect.paperScoreMax(rowData)} /> :
              '-'
            :
            trans('paper_score_not_available', {}, 'quiz')
        }
      ]}
    />
  </Fragment>

Papers.propTypes = {
  admin: T.bool.isRequired,
  quiz: T.object.isRequired
}

const ConnectedPapers = connect(
  (state) => ({
    quiz: quizSelect.quiz(state),
    admin: hasPermission('edit', resourceSelect.resourceNode(state)) || quizSelect.papersAdmin(state)
  })
)(Papers)

export {
  ConnectedPapers as Papers
}
