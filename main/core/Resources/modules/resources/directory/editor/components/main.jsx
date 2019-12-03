import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Alert} from '#/main/app/alert/components/alert'

import {FormData} from '#/main/app/content/form/containers/data'
import {ListForm} from '#/main/app/content/list/parameters/containers/form'
import {selectors} from '#/main/core/resources/directory/editor/store'

import resourcesSource from '#/main/core/data/sources/resources'

import {Directory as DirectoryTypes} from '#/main/core/resources/directory/prop-types'

const EditorMain = (props) =>
  <Fragment>
    {props.storageLock &&
      <Alert type="warning">{trans('storage_limit_reached_resources')}</Alert>
    }

    <FormData
      level={2}
      title={trans('parameters')}
      name={selectors.FORM_NAME}
      target={['apiv2_resource_directory_update', {id: props.directory.id}]}
      buttons={true}
      cancel={{
        type: LINK_BUTTON,
        target: props.path,
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
        }
      ]}
    >
      <ListForm
        level={3}
        name={selectors.FORM_NAME}
        dataPart="list"
        list={resourcesSource.parameters}
        parameters={props.directory.list}
      />
    </FormData>
  </Fragment>

EditorMain.propTypes = {
  path: T.string,
  directory: T.shape(
    DirectoryTypes.propTypes
  ),
  storageLock: T.bool.isRequired
}

EditorMain.defaultProps = {
  directory: DirectoryTypes.defaultProps
}


export {
  EditorMain
}
