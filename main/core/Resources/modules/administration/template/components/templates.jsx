import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions, selectors} from '#/main/core/administration/template/store'
import {TemplateCard} from '#/main/core/administration/template/data/components/template-card'

const TemplatesComponent = (props) =>
  <ListData
    name={selectors.STORE_NAME + '.templates'}
    fetch={{
      url: ['apiv2_lang_template_list', {lang: props.defaultLocale}],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/form/${row.id}`,
      label: trans('edit', {}, 'actions')
    })}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit'),
        scope: ['object'],
        target: `${props.path}/form/${rows[0].id}`
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-check-square',
        label: trans('define_as_default_for_type', {}, 'template'),
        scope: ['object'],
        callback: () => props.defineDefaultTemplate(rows[0].id)
      }
    ]}
    delete={{
      url: ['apiv2_template_full_delete_bulk']
    }}
    definition={[
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'type',
        type: 'template_type',
        label: trans('type'),
        displayed: true,
        filterable: true
      }, {
        name: 'title',
        type: 'string',
        label: trans('title'),
        displayed: true
      }, {
        name: 'content',
        type: 'html',
        label: trans('content'),
        displayed: true
      }, {
        name: 'default',
        type: 'boolean',
        label: trans('default'),
        displayed: true,
        filterable: false,
        sortable: false,
        calculated: (row) => row.name === row.type.defaultTemplate
      }
    ]}
    card={TemplateCard}
  />

TemplatesComponent.propTypes = {
  path: T.string.isRequired,
  defaultLocale: T.string.isRequired,
  defineDefaultTemplate: T.func.isRequired
}

const Templates = connect(
  (state) => ({
    path: toolSelectors.path(state),
    defaultLocale: selectors.defaultLocale(state)
  }),
  (dispatch) => ({
    defineDefaultTemplate(templateId) {
      dispatch(actions.defineDefaultTemplate(templateId))
    }
  })
)(TemplatesComponent)

export {
  Templates
}
