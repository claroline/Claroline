import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'

import {DataCard} from '#/main/app/data/components/card'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {UserMicro} from '#/main/core/user/components/micro'
import {displayDate} from '#/main/app/intl'

const WorkspaceCard = props =>
  <DataCard
    {...props}
    className={classes(props.className, {
      'data-card-muted': get(props.data, 'restrictions.hidden', false)
    })}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
    icon="fa fa-book"
    title={props.data.name}
    subtitle={props.data.code}
    flags={[
      get(props.data, 'meta.archived')                       && ['fa fa-box',       trans('is_archived', {}, 'workspace')],
      get(props.data, 'meta.personal')                       && ['fa fa-user',      trans('workspace_personal', {}, 'workspace')],
      get(props.data, 'restrictions.hidden')                 && ['fa fa-eye-slash', trans('workspace_hidden', {}, 'workspace')],
      get(props.data, 'registration.selfRegistration')       && ['fa fa-globe',     trans('workspace_public_registration', {}, 'workspace')],
      get(props.data, 'registration.waitingForRegistration') && ['fa fa-hourglass', trans('pending')]
    ].filter(flag => !!flag)}
    contentText={get(props.data, 'meta.description')}
    footer={
      <span
        style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between'
        }}
      >
        <UserMicro {...get(props.data, 'meta.creator', {})} />

        {get(props.data, 'meta.created') &&
          trans('created_at', {date: displayDate(props.data.meta.created, false, true)})
        }
      </span>
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
