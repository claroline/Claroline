import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/administration/template/store'
import {constants} from '#/main/core/administration/template/constants'
import {Template as TemplateTypes} from '#/main/core/data/types/template/prop-types'

const generateSections = (defaultLocale, locales) => {
  const sections = [
    {
      title: trans('general'),
      primary: true,
      fields: [
        {
          name: 'name',
          type: 'string',
          label: trans('name'),
          required: true
        }, {
          name: 'type',
          type: 'template_type',
          label: trans('type'),
          required: true
        }, {
          name: 'title',
          type: 'string',
          label: trans('title'),
          required: true
        }, {
          name: 'content',
          type: 'html',
          label: trans('content'),
          required: true
        }, {
          name: 'defineAsDefault',
          type: 'boolean',
          label: trans('define_as_default_for_type', {}, 'template')
        }
      ]
    }
  ]
  locales.filter(locale => locale !== defaultLocale).forEach(locale => {
    const section = {
      title: trans(locale),
      fields: [
        {
          name: `localized.${locale}.title`,
          type: 'string',
          label: trans('title')
        }, {
          name: `localized.${locale}.content`,
          type: 'html',
          label: trans('content')
        }
      ]
    }
    sections.push(section)
  })

  return sections
}

const TemplateForm = (props) =>
  <FormData
    level={2}
    name={selectors.STORE_NAME + '.template'}
    target={(template, isNew) => isNew ?
      ['apiv2_template_create'] :
      ['apiv2_template_update', {id: template.id}]
    }
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={generateSections(props.defaultLocale, props.locales)}
  >
    <FormSections level={3}>
      <FormSection
        icon="fa fa-fw fa-exchange-alt"
        title={trans('parameters')}
      >
        <div className="alert alert-info">
          {trans('placeholders_info', {}, 'template')}
        </div>
        <table className="table table-bordered table-striped table-hover">
          <thead>
            <tr>
              <th>{trans('parameter', {}, 'template')}</th>
              <th>{trans('description')}</th>
            </tr>
          </thead>
          <tbody>
            {constants.DEFAULT_PLACEHOLDERS.map((placeholder, idx) =>
              <tr key={`default-placeholder-${idx}`}>
                <td>{`%${placeholder}%`}</td>
                <td>{trans(`${placeholder}_desc`, {}, 'template')}</td>
              </tr>
            )}
            {props.template.type && props.template.type.placeholders && props.template.type.placeholders.map((placeholder, idx) =>
              <tr key={`custom-placeholder-${idx}`}>
                <td>{`%${placeholder}%`}</td>
                <td>{trans(`${placeholder}_desc`, {}, 'template')}</td>
              </tr>
            )}
          </tbody>
        </table>
      </FormSection>
    </FormSections>
  </FormData>

TemplateForm.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  template: T.shape(
    TemplateTypes.propTypes
  ).isRequired,
  defaultLocale: T.string,
  locales: T.arrayOf(T.string)
}

const Template = connect(
  state => ({
    path: toolSelectors.path(state),
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME + '.template')),
    template: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.template')),
    defaultLocale: selectors.defaultLocale(state),
    locales: selectors.locales(state)
  })
)(TemplateForm)

export {
  Template
}
