import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {EditorPage} from '#/main/app/editor'
import {selectors as editorSelectors} from '#/main/core/resource/editor'

import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'

const DropzoneEditorParameters = () => {
  const dropzone = useSelector(editorSelectors.resource)

  return (
    <EditorPage
      title={trans('parameters')}
      dataPart="resource"
      definition={[
        {
          title: trans('planning', {}, 'dropzone'),
          primary: true,
          fields: [
            {
              name: 'planning.type',
              type: 'choice',
              label: trans('type'),
              help: dropzone.planning && constants.PLANNING_TYPE_MANUAL === dropzone.planning.type ?
                trans('planning_manual_help', {}, 'dropzone') :
                trans('planning_auto_help', {}, 'dropzone'),
              required: true,
              options: {
                choices: constants.PLANNING_TYPES,
                condensed: true,
                noEmpty: true
              },
              linked: [
                {
                  name: 'planning.state',
                  type: 'choice',
                  label: trans('choose_current_state', {}, 'dropzone'),
                  displayed: dropzone.planning && constants.PLANNING_TYPE_MANUAL === dropzone.planning.type,
                  required: true,
                  options: {
                    noEmpty: true,
                    condensed: true,
                    choices: dropzone.parameters && dropzone.parameters.reviewType ?
                      constants.PLANNING_STATES[dropzone.parameters.reviewType] :
                      {}
                  }
                }, {
                  name: 'parameters.autoCloseDropsAtDropEndDate',
                  type: 'boolean',
                  label: trans('auto_close_drops_at_drop_end_date', {}, 'dropzone'),
                  displayed: dropzone.planning && constants.PLANNING_TYPE_MANUAL !== dropzone.planning.type
                }, {
                  name: 'planning.drop',
                  type: 'date-range',
                  label: trans('drop_range', {}, 'dropzone'),
                  displayed: dropzone.planning && constants.PLANNING_TYPE_MANUAL !== dropzone.planning.type,
                  required: true,
                  options: {
                    time: true
                  }
                }, {
                  name: 'planning.review',
                  type: 'date-range',
                  label: trans('review_range', {}, 'dropzone'),
                  displayed: dropzone.planning &&
                    constants.PLANNING_TYPE_MANUAL !== dropzone.planning.type &&
                    dropzone.parameters &&
                    constants.REVIEW_TYPE_PEER === dropzone.parameters.reviewType,
                  required: true,
                  options: {
                    time: true
                  }
                }
              ]
            }
          ]
        }, {
          title: trans('access_restrictions'),
          primary: true,
          hideTitle: true,
          fields: [
            {
              name: 'restrictions.lockDrops',
              type: 'boolean',
              label: trans('lock_drops', {}, 'dropzone'),
              help: trans('lock_drops_help', {}, 'dropzone')
            }
          ]
        }
      ]}
    />
  )
}

export {
  DropzoneEditorParameters
}
