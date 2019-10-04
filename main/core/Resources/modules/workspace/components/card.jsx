import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'

import {DataCard} from '#/main/app/content/card/components/data'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

const WorkspaceCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
    icon="fa fa-book"
    title={props.data.name}
    subtitle={props.data.code}
    flags={[
      get(props.data, 'meta.archived')                       && ['fa fa-box',       trans('workspace_archived', {}, 'workspace')],
      get(props.data, 'meta.personal')                       && ['fa fa-user',      trans('workspace_personal', {}, 'workspace')],
      get(props.data, 'restrictions.hidden')                 && ['fa fa-eye-slash', trans('workspace_hidden', {}, 'workspace')],
      get(props.data, 'registration.selfRegistration')       && ['fa fa-globe',     trans('workspace_public_registration', {}, 'workspace')],
      get(props.data, 'registration.waitingForRegistration') && ['fa fa-hourglass', trans('pending')]
    ].filter(flag => !!flag)}
    contentText={get(props.data, 'meta.description')}
    footer={
      <span>
        created by <b>{get(props.data, 'meta.creator') ? props.data.meta.creator.name : trans('unknown')}</b>
      </span>
    }
  />

WorkspaceCard.propTypes = {
  data: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired
}

export {
  WorkspaceCard
}
