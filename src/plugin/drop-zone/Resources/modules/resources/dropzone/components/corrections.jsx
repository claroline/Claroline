import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {DataTable} from '#/main/app/content/list/components/view/data-table'

import {DropzoneType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {MODAL_CORRECTION} from '#/plugin/drop-zone/resources/dropzone/correction/components/modal/correction-modal'

const getTitle = (dropzone, correction, index = 0) => {
  let title

  if (dropzone.display.correctorDisplayed) {
    if (dropzone.parameters.dropType === constants.DROP_TYPE_TEAM) {
      title = trans('correction_from', {name: correction.teamName}, 'dropzone')
    } else {
      title = trans('correction_from', {name: `${correction.user.firstName} ${correction.user.lastName}`}, 'dropzone')
    }
  } else {
    title = trans('correction_n', {number: index + 1}, 'dropzone')
  }

  return title
}

const Corrections = (props) => {

  return (
    <DataTable
      data={props.corrections}
      columns={[
        {
          name: 'correctionDenied',
          type: 'boolean',
          label: '',
          render: (row) => {
            if (row.correctionDenied) {
              return (
                <span className="fa fa-fw fa-exclamation-triangle" />
              )
            }

            return ''
          }
        }, {
          name: 'name',
          type: 'string',
          label: trans('correction'),
          calculated: (row) => getTitle(props.dropzone, row, props.corrections.findIndex(c => c.id === row.id)),
          displayed: true,
          primary: true
        }, {
          name: 'startDate',
          label: trans('start_date'),
          type: 'date',
          options: {time: true},
          displayed: true
        }, {
          name: 'endDate',
          label: trans('end_date'),
          type: 'date',
          options: {time: true},
          displayed: true
        }, {
          name: 'score',
          type: 'score',
          label: trans('score'),
          displayed: props.dropzone.display.showScore,
          calculated: (row) => ({
            current: row.score,
            total: props.dropzone.parameters.scoreMax
          })
        }
      ]}
      primaryAction={(row) => ({
        type: MODAL_BUTTON,
        modal: [MODAL_CORRECTION, {
          title: getTitle(props.dropzone, row, props.corrections.findIndex(c => c.id === row.id)),
          correction: row,
          dropzone: props.dropzone,
          showDenialBox: props.dropzone.parameters.correctionDenialEnabled,
          denyCorrection: (correctionId, comment) => props.denyCorrection(correctionId, comment)
        }]
      })}
      actions={props.actions}
    />
  )
}

Corrections.propTypes = {
  dropzone: T.shape(
    DropzoneType.propTypes
  ).isRequired,
  corrections: T.array,
  actions: T.func,

  denyCorrection: T.func
}

Corrections.defaultProps = {
  corrections: []
}

export {
  Corrections
}
