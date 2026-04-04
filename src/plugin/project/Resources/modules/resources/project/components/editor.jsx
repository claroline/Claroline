import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {ResourceEditor} from '#/main/core/resource/editor'

import {selectors} from '#/plugin/project/resources/project/store'

const ProjectEditorInstruction = () =>
  <EditorPage
    title={trans('instruction', {}, 'resource')}
    dataPart="resource"
    definition={[
      {
        title: trans('instruction', {}, 'resource'),
        primary: true,
        hideTitle: true,
        fields: [
          {
            name: 'instruction',
            label: trans('instruction', {}, 'resource'),
            type: 'html',
            required: true
          }, {
            name: 'expectedFormat',
            label: trans('expected_format', {}, 'resource'),
            type: 'choice',
            required: true,
            options: {
              condensed: true,
              choices: {
                file: trans('expected_format_file', {}, 'resource'),
                text: trans('expected_format_text', {}, 'resource'),
                url: trans('expected_format_url', {}, 'resource'),
                media: trans('expected_format_media', {}, 'resource'),
                none: trans('expected_format_none', {}, 'resource')
              }
            }
          }, {
            name: 'estimatedDuration',
            label: trans('estimated_duration', {}, 'resource'),
            type: 'number',
            options: {
              min: 0,
              unit: trans('minutes')
            }
          }, {
            name: 'templateFile',
            label: trans('template_file', {}, 'resource'),
            type: 'file'
          }
        ]
      }, {
        title: trans('planning', {}, 'resource'),
        icon: 'fa fa-fw fa-calendar',
        fields: [
          {
            name: 'dropStartDate',
            label: trans('drop_start_date', {}, 'resource'),
            type: 'date',
            options: {
              time: true
            }
          }, {
            name: 'dropEndDate',
            label: trans('drop_end_date', {}, 'resource'),
            type: 'date',
            options: {
              time: true
            }
          }
        ]
      }, {
        title: trans('allowed_formats', {}, 'resource'),
        icon: 'fa fa-fw fa-paperclip',
        fields: [
          {
            name: 'allowFileUpload',
            label: trans('allow_file_upload', {}, 'resource'),
            type: 'boolean'
          }, {
            name: 'allowRichText',
            label: trans('allow_rich_text', {}, 'resource'),
            type: 'boolean'
          }, {
            name: 'allowUrl',
            label: trans('allow_url', {}, 'resource'),
            type: 'boolean'
          }, {
            name: 'allowMedia',
            label: trans('allow_media', {}, 'resource'),
            type: 'boolean'
          }
        ]
      }, {
        title: trans('evaluation', {}, 'resource'),
        icon: 'fa fa-fw fa-award',
        fields: [
          {
            name: 'evaluationType',
            label: trans('evaluation_type', {}, 'resource'),
            type: 'choice',
            required: true,
            options: {
              condensed: true,
              choices: {
                raw_score: trans('evaluation_type_raw_score', {}, 'resource'),
                simple_grid: trans('evaluation_type_simple_grid', {}, 'resource')
              }
            }
          }
        ]
      }, {
        title: trans('advanced_options', {}, 'resource'),
        icon: 'fa fa-fw fa-cog',
        fields: [
          {
            name: 'groupMode',
            label: trans('group_mode', {}, 'resource'),
            type: 'boolean',
            help: trans('group_mode_help', {}, 'resource')
          }, {
            name: 'iterativeMode',
            label: trans('iterative_mode', {}, 'resource'),
            type: 'boolean',
            help: trans('iterative_mode_help', {}, 'resource')
          }
        ]
      }
    ]}
  />

const ProjectEditor = () => {
  const project = useSelector(selectors.project)

  return (
    <ResourceEditor
      additionalData={() => ({
        resource: project
      })}
      pages={[
        {
          name: 'instruction',
          title: trans('instruction', {}, 'resource'),
          component: ProjectEditorInstruction
        }
      ]}
    />
  )
}

export {
  ProjectEditor
}
