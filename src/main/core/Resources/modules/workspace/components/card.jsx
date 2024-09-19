import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'
import {DataCard} from '#/main/app/data/components/card'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

const WorkspaceCard = props =>
  <DataCard
    {...props}
    className={classes(props.className, {
      'data-card-muted': get(props.data, 'restrictions.hidden', false)
    })}
    id={props.data.id}
    poster={props.data.thumbnail}
    icon={!props.data.thumbnail ? props.data.name.charAt(0) : null}
    title={
      <>
        <TooltipOverlay
          id={'ws-type'+props.data.id}
          position="top"
          tip={classes({
            [trans('workspace_public_registration', {}, 'workspace')]: get(props.data, 'registration.selfRegistration'),
            [trans('workspace_model', {}, 'workspace')]: get(props.data, 'meta.model'),
            [trans('workspace_personal', {}, 'workspace')]: get(props.data, 'meta.personal')
          })}
        >
          <span className={classes({
            'fa fa-fw fa-globe me-2': get(props.data, 'registration.selfRegistration'),
            'fa fa-fw fa-stamp me-2': get(props.data, 'meta.model'),
            'fa fa-fw fa-user me-2': get(props.data, 'meta.personal')
          })} aria-hidden={true} />
        </TooltipOverlay>

        {props.data.name}
      </>
    }
    contentText={get(props.data, 'meta.description')}
    meta={
      <>
        <span className="badge bg-secondary-subtle text-secondary-emphasis">{transChoice('display_views', get(props.data, 'meta.views') || 0, {count: get(props.data, 'meta.views') || 0})}</span>
        {get(props.data, 'evaluation.estimatedDuration') &&
          <span className="badge bg-secondary-subtle text-secondary-emphasis">
            <span className="fa far fa-clock me-1" />
            {get(props.data, 'evaluation.estimatedDuration') + ' ' + trans('minutes_short')}
          </span>
        }
        {get(props.data, 'meta.archived') &&
          <span className="badge bg-secondary-subtle text-secondary-emphasis text-capitalize">{trans('archived')}</span>
        }

        {get(props.data, 'restrictions.hidden', false) &&
          <span className="badge bg-secondary-subtle text-secondary-emphasis text-capitalize">{trans('hidden')}</span>
        }
      </>
    }
  />

WorkspaceCard.propTypes = {
  className: T.string,
  data: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired
}

export {
  WorkspaceCard
}
