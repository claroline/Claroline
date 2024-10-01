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
  const isNew = useSelector(state => formSelectors.isNew(formSelectors.form(state, selectors.FORM_NAME)))
  const explanation = useSelector(state => selectors.exportExplanation(state))

  let entity = typeof formData.action !== 'undefined' ? formData.action.substring(0, formData.action.indexOf('_')) : formData.type
  let action = typeof formData.action !== 'undefined' ? formData.action.substring(formData.action.indexOf('_') + 1) : formData.action
  if(typeof formData.type !== 'undefined' && formData.type !== entity) {
    entity = formData.type
    action = ''
  }

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
              type: 'choice',
              label: trans('type'),
              disabled: !isNew,
              calculated: () => entity,
              options: {
                noEmpty: false,
                condensed: true,
                choices: Object.keys(explanation).sort().reduce((o, key) => Object.assign(o, {
                  [key]: trans(key, {}, 'transfer')
                }), {})
              }
            }, {
              name: 'action',
              type: 'choice',
              label: trans('action'),
              disabled: !isNew,
              displayed: !!entity,
              calculated: () => entity + '_' + action,
              options: {
                noEmpty: false,
                condensed: true,
                choices: Object.keys(get(explanation, entity, [])).reduce((o, key) => Object.assign(o, {
                  [entity + '_' + key]: trans(key, {}, 'transfer')
                }), {})
              }
            }, {
              name: 'name',
              type: 'string',
              label: trans('name'),
              disabled: false
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
