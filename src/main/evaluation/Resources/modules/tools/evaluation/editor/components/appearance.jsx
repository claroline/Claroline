import React from 'react'
import {ToolEditorAppearance} from '#/main/core/tool/editor'
import {trans} from '#/main/app/intl'

const EvaluationToolAppearance = () =>
  <ToolEditorAppearance
    definition={[{
      title: trans('Templates'),
      subtitle: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer condimentum scelerisque lorem, non finibus ligula pretium et.'),
      primary: true,
      fields: [{
        name: 'evaluation.participationTemplate',
        label: trans('workspace_participation_certificate', {}, 'template'),
        help: trans('workspace_participation_certificate_desc', {}, 'template'),
        type: 'template',
        options: {
          picker: {
            filters: [{
              property: 'typeName',
              value: 'email_announcement',
              locked: true
            }]
          }
        }
      }, {
        name: 'evaluation.successTemplate',
        label: trans('workspace_success_certificate', {}, 'template'),
        help: trans('workspace_success_certificate_desc', {}, 'template'),
        type: 'template',
        options: {
          picker: {
            filters: [{
              property: 'typeName',
              value: 'pdf_announcement',
              locked: true
            }]
          }
        }
      }]
    }]}
  />

export {
  EvaluationToolAppearance
}
