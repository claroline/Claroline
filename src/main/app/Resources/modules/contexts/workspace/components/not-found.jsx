import React from 'react'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentNotFound} from '#/main/app/content/components/not-found'
import {route as toolRoute} from '#/main/core/tool/routing'

const WorkspaceNotFound = () =>
  <ContentNotFound
    size="lg"
    title={trans('not_found', {}, 'workspace')}
    description={trans('not_found_desc', {}, 'workspace')}
  >
    <Button
      variant="btn"
      size="lg"
      type={LINK_BUTTON}
      label={trans('browse-workspaces', {}, 'actions')}
      target={toolRoute('workspaces')}
      exact={true}
      primary={true}
    />
  </ContentNotFound>

export {
  WorkspaceNotFound
}
