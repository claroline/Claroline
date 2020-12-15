import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListSource} from '#/main/app/content/list/containers/source'
import {ToolPage} from '#/main/core/tool/containers/page'
import {getActions, getDefaultAction} from '#/main/core/resource/utils'

import resourcesSource from '#/main/core/data/sources/resources'
import {Directory as DirectoryTypes} from '#/main/core/resources/directory/prop-types'

const ResourcesRoot = props =>
  <ToolPage>
    <ListSource
      name={props.listName}
      fetch={{
        url: ['apiv2_resource_list'],
        autoload: true
      }}
      customActions={[
        {
          name: 'back',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-arrow-left',
          label: trans('back'),
          disabled: true, // This is just for ui stability. It will never be enabled because there is no parent
          target: props.path,
          exact: true
        }
      ]}
      source={merge({}, resourcesSource, {
        // adds actions to source
        parameters: {
          primaryAction: (resourceNode) => getDefaultAction(resourceNode, {
            add: props.invalidate,
            update: props.invalidate,
            delete: props.invalidate
          }, props.path, props.currentUser),
          actions: (resourceNodes) => getActions(resourceNodes, {
            add: props.invalidate,
            update: props.invalidate,
            delete: props.invalidate
          }, props.path, props.currentUser)
        }
      })}
      parameters={DirectoryTypes.defaultProps.list}
    />
  </ToolPage>

ResourcesRoot.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  listName: T.string.isRequired,
  invalidate: T.func.isRequired
}

export {
  ResourcesRoot
}
