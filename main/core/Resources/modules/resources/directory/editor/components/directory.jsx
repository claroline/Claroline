import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {FormData} from '#/main/app/content/form/containers/data'
import {ConfigurationForm} from '#/main/app/content/list/configuration/components/form'
import {selectors} from '#/main/core/resources/directory/editor/store'

const DirectoryForm = (props) =>
  <FormData
    name={selectors.FORM_NAME}
    target={['apiv2_resource_directory_update', {id: props.directory.id}]}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: '/',
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [

        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'display.showSummary',
            type: 'boolean',
            label: trans('show_summary', {}, 'path'),
            linked: [
              {
                name: 'display.openSummary',
                type: 'boolean',
                label: trans('show_opened_summary', {}, 'path'),
                displayed: (directory) => directory.display.showSummary
              }
            ]
          }
        ]
      }
    ]}
  >
    <ConfigurationForm
      name={selectors.FORM_NAME}
    />
  </FormData>

DirectoryForm.propTypes = {
  directory: T.shape({
    id: T.string.isRequired
  })
}

const DirectoryEditor = connect(
  (state) => ({
    directory: selectors.directory(state)
  })
)(DirectoryForm)

export {
  DirectoryEditor
}
