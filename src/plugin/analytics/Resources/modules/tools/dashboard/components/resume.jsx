import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {displayDate, trans} from '#/main/app/intl'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {UserLink} from '#/main/core/user/components/link'

const DashboardResume = (props) =>
  <div className="content-resume">
    <div className="content-resume-info content-resume-primary">
      <span className="text-muted">
        {trans('status')}
      </span>

      {get(props.workspace, 'restrictions.hidden') &&
        <h1 className="content-resume-title h2 text-muted">
          {trans('hidden')}
        </h1>
      }

      {!get(props.workspace, 'restrictions.hidden') &&
        <h1 className="content-resume-title h2 text-success">
          {trans('visible')}
        </h1>
      }
    </div>

    <div className="content-resume-info">
      <span className="text-muted">
        {trans('last_modification')}
      </span>

      {get(props.workspace, 'meta.updated') &&
        <h1 className="content-resume-title h2">
          {displayDate(get(props.workspace, 'meta.updated'), false, true)}
        </h1>
      }
    </div>

    <div className="content-resume-info">
      <span className="text-muted">
        {trans('creation_date')}
      </span>

      {get(props.workspace, 'meta.created') &&
        <h1 className="content-resume-title h2">
          {displayDate(get(props.workspace, 'meta.created'), false, true)}
          <small>
            {trans('by')} <UserLink {...get(props.workspace, 'meta.creator', {})} />
          </small>
        </h1>
      }
    </div>
  </div>

DashboardResume.propTypes = {
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired
}

export {
  DashboardResume
}
