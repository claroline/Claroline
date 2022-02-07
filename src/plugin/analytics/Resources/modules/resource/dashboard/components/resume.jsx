import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {displayDate, trans} from '#/main/app/intl'
import {UserLink} from '#/main/core/user/components/link'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

const DashboardResume = (props) =>
  <div className="content-resume">
    <div className="content-resume-info content-resume-primary">
      <span className="text-muted">
        {trans('status')}
      </span>

      {!get(props.resourceNode, 'meta.published') &&
        <h1 className="content-resume-title h2 text-muted">
          {trans('not_published')}
        </h1>
      }

      {get(props.resourceNode, 'meta.published') && get(props.resourceNode, 'restrictions.hidden') &&
        <h1 className="content-resume-title h2 text-muted">
          {trans('hidden')}
        </h1>
      }

      {get(props.resourceNode, 'meta.published') && !get(props.resourceNode, 'restrictions.hidden') &&
        <h1 className="content-resume-title h2 text-success">
          {trans('published')}
        </h1>
      }
    </div>

    <div className="content-resume-info">
      <span className="text-muted">
        {trans('last_modification')}
      </span>

      {get(props.resourceNode, 'meta.updated') &&
        <h1 className="content-resume-title h2">
          {displayDate(get(props.resourceNode, 'meta.updated'), false, true)}
        </h1>
      }
    </div>

    <div className="content-resume-info">
      <span className="text-muted">
        {trans('creation_date')}
      </span>

      {get(props.resourceNode, 'meta.created') &&
        <h1 className="content-resume-title h2">
          {displayDate(get(props.resourceNode, 'meta.created'), false, true)}
          <small>
            {trans('by')} <UserLink {...get(props.resourceNode, 'meta.creator', {})} />
          </small>
        </h1>
      }
    </div>
  </div>

DashboardResume.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired
}

export {
  DashboardResume
}
