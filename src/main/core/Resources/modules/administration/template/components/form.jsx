import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {selectors} from '#/main/core/administration/template/store'
import {constants} from '#/main/core/administration/template/constants'
import {Template as TemplateTypes} from '#/main/core/data/types/template/prop-types'

const TemplateForm = (props) =>
  <FormData
    level={2}
    name={selectors.STORE_NAME + '.template'}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: props.path + (props.template && props.template.type ? `/${props.template.type.type}/${props.template.type.id}` : ''),
      exact: true
    }}
    save={{
      type: CALLBACK_BUTTON,
      callback: () => props.saveForm(get(props.template, 'type.id'), props.template.id, props.new)
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true,
            disabled: (template) => template.system,
            options: {
              unique: {
                name: 'name',
                check: ['apiv2_template_exist']
              }
            }
          }, {
            name: 'defineAsDefault',
            type: 'boolean',
            label: trans('define_as_default_for_type', {}, 'template'),
            calculated: (template) => template.defineAsDefault || (template.type && template.name === template.type.defaultTemplate)
          }
        ]
      }
    ].concat(props.locales.map(locale => ({
      title: trans(locale),
      defaultOpened: locale === props.defaultLocale,
      opened: locale === props.defaultLocale,
      fields: [
        {
          name: `contents.${locale}.title`,
          type: 'string',
          label: trans('title'),
          disabled: (template) => template.system
        }, {
          name: `contents.${locale}.content`,
          type: 'html',
          label: trans('content'),
          disabled: (template) => template.system
        }
      ]
    })))}
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
              <th>{trans('parameter')}</th>
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

export {
  TemplateForm
}
