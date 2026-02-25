import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl'

import {selectors} from '#/main/evaluation/sequence/editor/store'
import {EditorOverview} from '#/main/app/editor/components/overview'
import {actions as formActions} from '#/main/app/content/form/store'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const SequenceEditorOverview = () => {
  const workspace = useSelector(selectors.workspace)
  const sequence = useSelector(selectors.data)
  const totalEstimatedDuration = useSelector(selectors.totalEstimatedDuration)
  const dispatch = useDispatch()

  return (
    <EditorOverview
      meta={{
        id: get(sequence, 'id'),
        updatedAt: get(sequence, 'meta.updatedAt')
      }}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'poster',
              label: trans('poster'),
              type: 'poster',
              hideLabel: true
            }, {
              name: 'name',
              label: trans('name'),
              type: 'string',
              required: true
            }, {
              name: 'code',
              label: trans('code'),
              type: 'string',
              required: true
            }, {
              name: 'meta.published',
              type: 'boolean',
              label: trans('publish_sequence', {}, 'evaluation'),
              help: trans('publish_sequence_help', {}, 'evaluation')
            }
          ]
        }, {
          title: trans('further_information'),
          description: trans('further_information_help'),
          primary: true,
          fields: [
            {
              name: 'meta.description',
              label: trans('description_short'),
              help: trans('description_short_help', {}, 'resource'),
              type: 'string',
              recommended: true,
              options: {
                long: true,
                minRows: 2
              }
            }, {
              name: 'estimatedDuration',
              label: trans('estimated_duration'),
              help: trans('estimated_duration_help'),
              type: 'number',
              options: {
                unit: trans('minutes')
              },
              additional: (
                <div className="bg-body-tertiary rounded-2 mt-3 px-3 py-2 d-flex flex-row align-items-center gap-3">
                  {transChoice(
                    'estimated_resource_duration',
                    totalEstimatedDuration,
                    { duration: totalEstimatedDuration },
                    'sequence'
                  )}
                  <Button className="btn btn-primary ms-auto" size="sm" type={CALLBACK_BUTTON} callback={() =>  dispatch(
                    formActions.updateProp(
                      selectors.STORE_NAME,
                      'estimatedDuration',
                      totalEstimatedDuration
                    )
                  )}>{trans('use_calculated_duration', {}, 'sequence')}</Button>
                </div>
              )
            }, {
              name: 'meta.descriptionHtml',
              label: trans('description_long'),
              type: 'html',
              help: trans('description_long_help', {}, 'resource'),
              options: {
                workspace: workspace
              }
            }, {
              name: 'tags',
              label: trans('tags'),
              type: 'tag'
            }
          ]
        }
      ]}
    />
  )
}

export {
  SequenceEditorOverview
}
