import React from 'react'
import { PropTypes as T } from 'prop-types'

import { PageHeader } from '#/main/core/layout/page/components/page.jsx'
import { ResourceActions } from '#/main/core/layout/resource/components/resource-actions.jsx'

const ResourceHeader = props =>
  <PageHeader
    className="resource-header"
    title={props.resourceNode.name}
    subtitle={props.subtitle}
  >
    <ResourceActions editEnabled={props.editEnabled} />
  </PageHeader>

ResourceHeader.propTypes = {
  resourceNode: T.shape({
    name: T.string.isRequired
  }).isRequired,
  subtitle: T.string,
  editEnabled: T.bool,


  edit: T.func,
  publish: T.func,
  unpublish: T.func,
  manageRights: T.func,

  share: T.func,
  like: T.func,
  favorite: T.func,

  export: T.func,
  delete: T.func
}

ResourceHeader.defaultProps = {
  editEnabled: false,
  subtitle: null,
  actions: []
}

export {ResourceHeader}
