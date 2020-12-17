import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {selectors} from '#/main/core/resources/text/editor/store'
import {Text as TextTypes} from '#/main/core/resources/text/prop-types'

const EditorComponent = (props) =>
  <FormData
    name={selectors.FORM_NAME}
    target={['apiv2_resource_text_update', {id: props.text.id}]}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    lock={{
      id: props.text.id,
      className: 'Claroline\\CoreBundle\\Entity\\Resource\\Text',
      autoUnlock: true
    }}
    sections={[
      {
        title: trans('general', {}, 'platform'),
        primary: true,
        fields: [
          {
            name: 'raw',
            type: 'html',
            label: trans('text'),
            hideLabel: true,
            required: true,
            options: {
              workspace: props.workspace,
              minRows: 3
            }
          }
        ]
      }
    ]}
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
            {props.availablePlaceholders.map((placeholder) =>
              <tr key={placeholder}>
                <td>{`%${placeholder}%`}</td>
                <td>{trans(`${placeholder}_desc`, {}, 'template')}</td>
              </tr>
            )}
          </tbody>
        </table>
      </FormSection>
    </FormSections>
  </FormData>

EditorComponent.propTypes = {
  path: T.string.isRequired,
  workspace: T.object,
  text: T.shape(
    TextTypes.propTypes
  ).isRequired,
  availablePlaceholders: T.arrayOf(T.string)
}

const Editor = connect(
  state => ({
    path: resourceSelectors.path(state),
    workspace: resourceSelectors.workspace(state),
    text: selectors.text(state),
    availablePlaceholders: selectors.availablePlaceholders(state)
  })
)(EditorComponent)

export {
  Editor
}
