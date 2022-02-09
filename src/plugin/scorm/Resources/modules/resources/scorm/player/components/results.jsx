import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {ListData} from '#/main/app/content/list/containers/data'

import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box'

import {Scorm as ScormType, Sco as ScoType} from '#/plugin/scorm/resources/scorm/prop-types'
import {constants} from '#/plugin/scorm/resources/scorm/constants'
import {selectors} from '#/plugin/scorm/resources/scorm/store'
import {flattenScos} from '#/plugin/scorm/resources/scorm/utils'

const ResultsComponent = props =>
  <Fragment>
    <ContentTitle
      title={trans('results')}
      actions={[
        {
          name: 'export-results',
          type: DOWNLOAD_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('export-results', {}, 'actions'),
          file: {
            url: ['apiv2_scormscotracking_export', {scorm: props.scorm.id}]
          },
          group: trans('transfer')
        }
      ]}
    />
    <ListData
      name={selectors.STORE_NAME+'.results'}
      fetch={{
        url: ['apiv2_scormscotracking_list', {scorm: props.scorm.id}],
        autoload: true
      }}
      definition={[
        {
          name: 'user',
          label: trans('user'),
          type: 'user',
          displayed: true
        }, {
          name: 'userEmail',
          label: trans('email'),
          type: 'email'
        }, {
          name: 'sco',
          label: trans('sco', {}, 'scorm'),
          displayed: 1 < props.scos.length,
          filterable: 1 < props.scos.length,
          sortable: 1 < props.scos.length,
          render: (rowData) => rowData.sco && rowData.sco.data.title ? rowData.sco.data.title : '-'
        }, {
          name: 'latestDate',
          type: 'date',
          label: trans('last_session_date', {}, 'scorm'),
          displayed: true,
          filterable: false,
          options: {
            time: true
          }
        }, {
          name: 'totalTime',
          type: 'string',
          label: trans('total_time'),
          displayed: true,
          filterable: false
        }, {
          name: 'scoreRaw',
          type: 'number',
          label: trans('best_score'),
          displayed: true,
          render: (rowData) => {
            let Score
            if ((rowData.scoreRaw || 0 === rowData.scoreRaw) && (rowData.scoreMax || 0 === rowData.scoreMax)) {
              Score = <ScoreBox size="sm" score={rowData.scoreRaw} scoreMax={rowData.scoreMax} />
            } else {
              Score = rowData.scoreRaw
            }

            return Score
          }
        }, {
          name: 'lessonStatus',
          type: 'string',
          label: trans('status'),
          displayed: true,
          filterable: false,
          calculated: (rowData) => trans(rowData.lessonStatus, {}, 'scorm')
        }, {
          name: 'lessonStatusSelect',
          alias: 'lessonStatus',
          type: 'choice',
          label: trans('status'),
          displayed: false,
          displayable: false,
          filterable: true,
          sortable: false,
          options: {
            choices: constants.SCORM_12 === props.scorm.version ?
              constants.LESSON_STATUS_LIST_12 :
              constants.LESSON_STATUS_LIST_2004
          }
        }, {
          name: 'views',
          label: trans('views'),
          type: 'number',
          displayed: true,
          filterable: false,
          sortable: false
        }, {
          name: 'attempts',
          label: trans('attempts'),
          type: 'number',
          displayed: true,
          filterable: false,
          sortable: false
        }, {
          name: 'progression',
          type: 'string',
          label: trans('progression'),
          displayed: true,
          filterable: false,
          sortable: constants.SCORM_2004 === props.scorm.version,
          calculated: (rowData) => rowData.progression + '%'
        }, {
          name: 'userDisabled',
          label: trans('user_disabled'),
          type: 'boolean',
          displayable: false,
          sortable: false,
          filterable: true
        }
      ]}
    />
  </Fragment>

ResultsComponent.propTypes = {
  scorm: T.shape(ScormType.propTypes),
  scos: T.arrayOf(T.shape(ScoType.propTypes)).isRequired
}

const Results = connect(
  (state) => ({
    scorm: selectors.scorm(state),
    scos: flattenScos(selectors.scos(state))
  })
)(ResultsComponent)

export {
  Results
}
