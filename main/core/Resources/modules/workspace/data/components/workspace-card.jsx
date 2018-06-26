import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {asset} from '#/main/core/scaffolding/asset'

import {DataCard} from '#/main/core/data/components/data-card'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

const WorkspaceCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail) : null}
    icon="fa fa-book"
    title={props.data.name}
    subtitle={props.data.code}
    flags={[
      props.data.meta.personal                       && ['fa fa-user',         trans('personal_workspace')],
      props.data.meta.model                          && ['fa fa-object-group', trans('model')],
      props.data.display.displayable                 && ['fa fa-eye',          trans('displayable_in_workspace_list')],
      props.data.registration.selfRegistration       && ['fa fa-globe',        trans('public_registration')],
      props.data.registration.waitingForRegistration && ['fa fa-hourglass',    trans('pending')]
    ].filter(flag => !!flag)}
    contentText={props.data.meta.description}
    footer={
      <span>
        created by <b>{props.data.meta.creator ? props.data.meta.creator.name : trans('unknown')}</b>
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
