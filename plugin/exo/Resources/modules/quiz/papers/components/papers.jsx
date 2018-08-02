import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {url} from '#/main/app/api'
import {tex, trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/core/resource/permissions'
import {getTimeDiff} from '#/main/core/scaffolding/date'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box.jsx'

import quizSelect from '#/plugin/exo/quiz/selectors'
import {selectors as paperSelect} from '#/plugin/exo/quiz/papers/selectors'
import {utils} from '#/plugin/exo/quiz/papers/utils'

const Papers = props =>
  <div className="papers-list">
    <div className="panel panel-heading">
      <a className="btn btn-primary" href={url(['exercise_papers_export_json', {'exerciseId': props.quiz.id}])}> {tex('json_export')} </a>
      {' '}
      <a className="btn btn-primary" href={url(['exercise_papers_export_csv', {'exerciseId': props.quiz.id}])}> {tex('csv_export')} </a>
    </div>
    <ListData
      name="papers.list"
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        label: trans('open'),
        target: `/papers/${row.id}`
      })}
      fetch={{
        url: ['apiv2_exopaper_list', {exercise: props.quiz.id}],
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
          type: 'datetime'
        }, {
          name: 'endDate',
          alias: 'end',
          label: trans('end_date'),
          displayed: true,
          filterable: false,
          type: 'datetime'
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
            tex('paper_score_not_available')
        }
      ]}
    />
  </div>

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
