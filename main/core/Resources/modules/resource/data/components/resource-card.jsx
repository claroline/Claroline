import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans, transChoice} from '#/main/core/translation'
import {asset} from '#/main/core/scaffolding/asset'
import {displayDate} from '#/main/core/scaffolding/date'

import {DataCard} from '#/main/core/data/components/data-card'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceIcon} from '#/main/core/resource/components/icon'
import {getSimpleAccessRule} from '#/main/core/resource/rights'
import {hasPermission} from '#/main/core/resource/permissions'

import {UserMicro} from '#/main/core/user/components/micro'

const ResourceCard = props => {
  // computes simplified version of node rights
  let rightsIcon, rightsTip
  if (props.data.rights && hasPermission('administrate', props.data)) {
    const rights = getSimpleAccessRule(props.data.rights, props.data.workspace)

    switch (rights) {
      case 'all':
        rightsIcon = 'fa-globe'
        rightsTip = 'resource_rights_all_tip'
        break
      case 'user':
        rightsIcon = 'fa-users'
        rightsTip = 'resource_rights_user_tip'
        break
      case 'workspace':
        rightsIcon = 'fa-book'
        rightsTip = 'resource_rights_workspace_tip'
        break
      case 'admin':
        rightsIcon = 'fa-lock'
        rightsTip = 'resource_rights_admin_tip'
        break
    }
  }

  return (
    <DataCard
      {...props}
      className={classes(props.className, {
        'data-card-muted': !props.data.meta.published
      })}
      id={props.data.id}
      poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
      icon={<ResourceIcon className="icon" mimeType={props.data.meta.mimeType} />}
      title={props.data.name}
      subtitle={trans(props.data.meta.type, {}, 'resource')}
      flags={[
        props.data.rights && hasPermission('administrate', props.data) && [classes('fa fa-fw', rightsIcon), trans(rightsTip, {}, 'resource')],
        ['fa fa-fw fa-eye', transChoice('resource_views', props.data.meta.views, {count: props.data.meta.views}, 'resource'), props.data.meta.views],
        props.data.social && ['fa fa-fw fa-thumbs-up', transChoice('resource_likes', props.data.social.likes, {count: props.data.social.likes}, 'resource'), props.data.social.likes]
      ].filter(flag => !!flag)}
      contentText={props.data.meta.description}
      footer={
        <span
          style={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between'
          }}
        >
          <UserMicro {...props.data.meta.creator} />

          {trans('published_at', {date: displayDate(props.data.meta.created, false, true)})}
        </span>
      }
    />
  )
}

ResourceCard.propTypes = {
  className: T.string,
  data: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired
}

export {
  ResourceCard
}
