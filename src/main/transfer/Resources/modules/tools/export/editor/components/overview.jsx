import React from 'react'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl'
import {useSelector} from 'react-redux'
import {EditorPage} from '#/main/app/editor'
import {selectors} from '#/main/transfer/tools/export/store'
import {selectors as formSelectors} from '#/main/app/content/form'

const ExportEditorOverview = () => {
  const formData = useSelector(state => formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)))
  const explanation = useSelector(state => selectors.exportExplanation(state))

  const entity = formData.action.substring(0, formData.action.indexOf('_'))
  const action = formData.action.substring(formData.action.indexOf('_') + 1)

  const additionalFields = explanation ? get(explanation, entity+'.'+action+'.fields', []): []
  const filters = additionalFields.map(field => merge({}, field, {
    name: 'extra.'+field.name,
    linked: field.linked ? field.linked.map(linked => merge({}, linked, {name: 'extra.'+linked.name})) : []
  }))

  return (
    <EditorPage
      title={trans('overview')}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'type',
              type: 'string',
              label: trans('type'),
              hideLabel: true,
              disabled: true,
              calculated: (formData) => trans(formData.action.substring(0, formData.action.indexOf('_')))
            },{
              name: 'action',
              type: 'choice',
              label: trans('action'),
              disabled: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: Object.keys(get(explanation, entity, [])).reduce((o, key) => Object.assign(o, {
                  [entity + '_' + key]: trans(key, {}, 'transfer')
                }), {})
              }
            }, {
              name: 'name',
              type: 'string',
              label: trans('name')
            }
          ].concat(filters)
        }
      ]}
    />
  )
}

export {
  ExportEditorOverview
}
