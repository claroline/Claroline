import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {DataListContainer} from '#/main/core/data/list/containers/data-list'
import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box.jsx'

import {Scorm as ScormType, Sco as ScoType} from '#/plugin/scorm/resources/scorm/prop-types'
import {constants} from '#/plugin/scorm/resources/scorm/constants'
import {select} from '#/plugin/scorm/resources/scorm/selectors'
import {flattenScos} from '#/plugin/scorm/resources/scorm/utils'

const ResultsComponent = props =>
  <DataListContainer
    name="results"
    fetch={{
      url: ['apiv2_scormscotracking_list', {scorm: props.scorm.id}],
      autoload: true
    }}
    definition={[
      {
        name: 'user',
        label: trans('user'),
        displayed: true,
        render: (rowData) => rowData.user ? rowData.user.name : trans('anonymous')
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
        filterable: false
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
        render: (rowData) => (rowData.scoreRaw || 0 === rowData.scoreRaw) && (rowData.scoreMax || 0 === rowData.scoreMax) ?
          <ScoreBox size="sm" score={rowData.scoreRaw} scoreMax={rowData.scoreMax} /> :
          rowData.scoreRaw
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
        filterable: true,
        sortable: false,
        options: {
          choices: constants.SCORM_12 === props.scorm.version ?
            constants.LESSON_STATUS_LIST_12 :
            constants.LESSON_STATUS_LIST_2004
        }
      }, {
        name: 'completionStatus',
        type: 'string',
        label: trans('completion_status', {}, 'scorm'),
        displayed: constants.SCORM_2004 === props.scorm.version,
        filterable: false,
        sortable: constants.SCORM_2004 === props.scorm.version,
        calculated: (rowData) => trans(rowData.completionStatus, {}, 'scorm')
      }, {
        name: 'completionStatusSelect',
        alias: 'completionStatus',
        type: 'choice',
        label: trans('completion_status', {}, 'scorm'),
        displayed: false,
        filterable: constants.SCORM_2004 === props.scorm.version,
        sortable: false,
        options: {
          choices: constants.COMPLETION_STATUS_LIST_2004
        }
      }
    ]}
    actions={() => []}
  />

ResultsComponent.propTypes = {
  scorm: T.shape(ScormType.propTypes),
  scos: T.arrayOf(T.shape(ScoType.propTypes)).isRequired
}

const Results = connect(
  (state) => ({
    scorm: select.scorm(state),
    scos: flattenScos(select.scos(state))
  })
)(ResultsComponent)

export {
  Results
}
