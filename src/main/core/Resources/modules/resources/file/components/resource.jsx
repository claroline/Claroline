import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {File as FileType} from '#/main/core/files/prop-types'
import {Resource} from '#/main/core/resource'
import {PlayerMain} from '#/main/core/resources/file/player/containers/main'
import {EditorMain} from '#/main/core/resources/file/editor/containers/main'

const FileResource = props =>
  <Resource
    {...omit(props, 'file', 'url', 'resetForm')}
    /*actions={[
      {
        name: 'clipboard',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-clipboard',
        label: trans('copy_permalink_to_clipboard'),
        permission: 'open',
        callback: () => copy(props.url)
      }
    ]}*/
    pages={[
      {
        path: '/',
        exact: true,
        component: PlayerMain
      }, {
        path: '/edit',
        component: EditorMain,
        onEnter: () => props.resetForm(props.file)
      }
    ]}
  />

FileResource.propTypes = {
  file: T.shape(FileType.propTypes),
  url: T.string,
  resetForm: T.func.isRequired
}

export {
  FileResource
}
