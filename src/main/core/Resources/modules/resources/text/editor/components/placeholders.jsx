import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {FormSection, FormSections} from '#/main/app/content/form/components/sections'

const EditorPlaceholders = (props) =>
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

EditorPlaceholders.propTypes = {
  availablePlaceholders: T.arrayOf(T.string)
}

export {
  EditorPlaceholders
}
