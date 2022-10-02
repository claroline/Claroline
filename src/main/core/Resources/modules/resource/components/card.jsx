import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'
import {displayDate} from '#/main/app/intl/date'

import {DataCard} from '#/main/app/data/components/card'
import {UserMicro} from '#/main/core/user/components/micro'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceIcon} from '#/main/core/resource/components/icon'

const ResourceCard = props =>
  <DataCard
    {...props}
    className={classes(props.className, {
      'data-card-muted': !get(props.data, 'meta.published', false) || get(props.data, 'restrictions.hidden', false)
    })}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail) : null}
    icon={
      <ResourceIcon className="icon" mimeType={props.data.meta.mimeType} />
    }
    title={props.data.name}
    subtitle={trans(props.data.meta.type, {}, 'resource')}
    flags={[
      !props.data.meta.published && ['fa fa-fw fa-eye-slash', trans('resource_not_published', {}, 'resource')],
      props.data.meta.published && ['fa fa-fw fa-eye', transChoice('resource_views', props.data.meta.views, {count: props.data.meta.views}, 'resource'), props.data.meta.views]
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

ResourceCard.propTypes = {
  className: T.string,
  data: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired
}

export {
  ResourceCard
}
