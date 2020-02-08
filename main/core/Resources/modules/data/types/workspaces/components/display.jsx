import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {route} from '#/main/core/workspace/routing'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {WorkspaceCard} from '#/main/core/workspace/components/card'

const WorkspacesDisplay = (props) => {
  if (!isEmpty(props.data)) {
    return (
      <Fragment>
        {props.data.map(workspace =>
          <WorkspaceCard
            key={`workspace-card-${workspace.id}`}
            data={workspace}
            size="xs"
            primaryAction={{
              type: LINK_BUTTON,
              label: trans('open', {}, 'actions'),
              target: route(workspace)
            }}
          />
        )}
      </Fragment>
    )
  }

  return (
    <EmptyPlaceholder
      icon="fa fa-book"
      title={trans('no_workspace')}
    />
  )
}

WorkspacesDisplay.propTypes = {
  data: T.arrayOf(T.shape(
    WorkspaceType.propTypes
  ))
}

export {
  WorkspacesDisplay
}
