import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {FormData} from '#/main/app/content/form/containers/data'
import {ListForm} from '#/main/app/content/list/parameters/containers/form'
import {selectors} from '#/main/core/resources/directory/editor/store'

import resourcesList from '#/main/core/data/sources/resources'

import {Directory as DirectoryTypes} from '#/main/core/resources/directory/prop-types'

const DirectoryForm = (props) =>
  <div>
    <div className="alert alert-info">
      <span className="fa fa-fw fa-exclamation-circle" />
      {trans('directory_not_implemented_display')}
    </div>

    <FormData
      level={3}
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
            {
              name: 'uploadDestination',
              type: 'boolean',
              label: trans('rich_text_upload_directory'),
              help: trans('rich_text_upload_directory_help')
            }
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
      <ListForm
        level={3}
        name={selectors.FORM_NAME}
        dataPart="list"
        list={resourcesList.parameters}
        parameters={props.directory.list}
      />
    </FormData>
  </div>

DirectoryForm.propTypes = {
  directory: T.shape(
    DirectoryTypes.propTypes
  )
}

DirectoryForm.defaultProps = {
  directory: DirectoryTypes.defaultProps
}

const DirectoryEditor = connect(
  (state) => ({
    directory: selectors.directory(state)
  })
)(DirectoryForm)

export {
  DirectoryEditor
}
