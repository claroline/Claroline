import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {ResourceList} from '#/main/core/resource/components/list'

const ResourcesRoot = props =>
  <ToolPage>
    <ResourceList
      className="my-3"
      path={props.path}
      name={props.listName}
      url={['apiv2_resource_list']}
      backAction={{
        name: 'back',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-arrow-left',
        label: trans('back'),
        disabled: true, // This is just for ui stability. It will never be enabled because there is no parent
        target: props.path,
        exact: true
      }}
    />
  </ToolPage>

ResourcesRoot.propTypes = {
  path: T.string.isRequired,
  listName: T.string.isRequired
}

export {
  ResourcesRoot
}
