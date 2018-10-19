import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {WorkspaceCard} from '#/main/core/workspace/data/components/workspace-card'

const WorkspacesDisplay = (props) => !isEmpty(props.data) ?
  <div>
    {props.data.map(workspace =>
      <WorkspaceCard
        key={`workspace-card-${workspace.id}`}
        data={workspace}
        size="sm"
        orientation="col"
      />
    )}
  </div> :
  <EmptyPlaceholder
    size="lg"
    icon="fa fa-books"
    title={trans('no_workspace')}
  />

WorkspacesDisplay.propTypes = {
  data: T.arrayOf(T.shape(WorkspaceType.propTypes))
}

export {
  WorkspacesDisplay
}
