import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {actions, selectors} from '#/main/core/administration/template/store'
import {TemplateList} from '#/main/core/administration/template/components/template-list'

const TemplatesComponent = (props) =>
  <ListData
    name="templates"
    fetch={{
      url: ['apiv2_lang_template_list', {lang: props.defaultLocale}],
      autoload: true
    }}
    primaryAction={TemplateList.open}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit'),
        scope: ['object'],
        target: 'form/' + rows[0].id
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
    definition={TemplateList.definition}
    card={TemplateList.card}
  />

TemplatesComponent.propTypes = {
  defaultLocale: T.string.isRequired,
  defineDefaultTemplate: T.func.isRequired
}

const Templates = connect(
  (state) => ({
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
