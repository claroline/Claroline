import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl/translation'
import {getTimeDiff} from '#/main/app/intl/date'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {displayUsername} from '#/main/core/user/utils'
import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {actions as papersActions, selectors as paperSelectors} from '#/plugin/exo/resources/quiz/papers/store'
import {PaperCard} from '#/plugin/exo/resources/quiz/papers/components/card'

const Papers = props =>
  <Fragment>
    <h3 className="h2">
      {trans('results', {}, 'quiz')}
      <small style={{display: 'block', marginTop: '5px'}}>{trans('all_attempts', {}, 'quiz')}</small>
    </h3>

    <ListData
      name={paperSelectors.LIST_NAME}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        label: trans('open', {}, 'actions'),
        target: `/papers/${row.id}`
      })}
      fetch={{
        url: ['exercise_paper_list', {exerciseId: props.quizId}],
        autoload: true
      }}
      definition={[
        {
          name: 'number',
          label: '#',
          displayed: true,
          type: 'string',
          calculated: (rowData) => trans('attempt', {number: rowData.number}, 'quiz')
        }, {
          name: 'user',
          label: trans('user'),
          displayed: true,
          type: 'user'
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
          type: 'time',
          displayed: true,
          filterable: false,
          sortable: false,
          calculated: (rowData) => {
            if (rowData.startDate && rowData.endDate) {
              return getTimeDiff(rowData.startDate, rowData.endDate)
            }

            return undefined
          }
        }, {
          name: 'finished',
          label: trans('finished'),
          displayed: true,
          type: 'boolean'
        }, {
          name: 'score',
          label: trans('score'),
          displayed: props.hasScore,
          displayable: props.hasScore,
          filterable: false,
          sortable: true,
          render: (rowData) => {
            if (rowData.total) {
              return <ScoreBox size="sm" className="pull-right" score={rowData.score} scoreMax={rowData.total} />
            }

            return '-'
          }
        }
      ]}

      actions={(rows) => [
        {
          name: 'delete',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash',
          label: trans('delete', {}, 'actions'),
          displayed: props.admin,
          dangerous: true,
          confirm: {
            title: trans('deletion'),
            subtitle: 1 === rows.length ?
              trans('user_attempt', {
                number: get(rows[0], 'number', '?'),
                userName: displayUsername(get(rows[0], 'user'))
              }, 'quiz')
              :
              transChoice('count_elements', rows.length, {count: rows.length}),
            message: transChoice('papers_delete_message', rows.length, {count: rows.length})
          },
          callback: () => props.delete(props.quizId, rows)
        }
      ]}

      card={PaperCard}
    />
  </Fragment>

Papers.propTypes = {
  quizId: T.string.isRequired,
  admin: T.bool.isRequired,
  hasScore: T.bool.isRequired,
  delete: T.func.isRequired
}

const ConnectedPapers = connect(
  (state) => ({
    admin: hasPermission('edit', resourceSelectors.resourceNode(state)) || hasPermission('manage_papers', resourceSelectors.resourceNode(state)),
    quizId: paperSelectors.quizId(state),
    hasScore: paperSelectors.quizHasScore(state)
  }),
  (dispatch) => ({
    delete(quizId, papers) {
      dispatch(papersActions.deletePapers(quizId, papers))
    }
  })
)(Papers)

export {
  ConnectedPapers as Papers
}
