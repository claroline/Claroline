import React, {useEffect} from 'react'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl'

import {selectors} from '#/main/evaluation/sequence/editor/store'
import {EditorOverview} from '#/main/app/editor/components/overview'
import {actions as formActions} from '#/main/app/content/form/store'

const SequenceEditorOverview = () => {
  const workspace = useSelector(selectors.workspace)
  const sequence = useSelector(selectors.data)
  const totalEstimatedDuration = useSelector(selectors.totalEstimatedDuration)
  const dispatch = useDispatch()

  useEffect(() => {
    const onClick = (e) => {
      const target = e.target
      const btn = target && target.closest ? target.closest('[data-fill-estimated-duration]') : null
      if (!btn) return

      e.preventDefault()

      dispatch(
        formActions.updateProp(
          selectors.STORE_NAME,
          'estimatedDuration',
          totalEstimatedDuration
        )
      )
    }
    document.addEventListener('click', onClick)
    return () => document.removeEventListener('click', onClick)
  }, [dispatch, totalEstimatedDuration])


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
              help:
                trans('estimated_duration_help') +
                '<br/>' +
                transChoice(
                  'estimated_resource_duration',
                  totalEstimatedDuration,
                  { duration: totalEstimatedDuration },
                  'sequence'
                ) +
                ' <button type="button" class="btn btn-subtle-primary btn-sm" data-fill-estimated-duration="1">' +
                trans('use_calculated_duration', {}, 'sequence') +
                '</button>',
              type: 'number',
              options: {
                unit: trans('minutes')
              }
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
