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
import {UserAvatar} from '#/main/app/user/components/avatar'

const ResourceCard = props =>
  <DataCard
    {...props}
    className={classes(props.className, {
      'data-card-muted': !get(props.data, 'meta.published', false) || get(props.data, 'restrictions.hidden', false)
    })}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail) : null}
    icon={!props.data.thumbnail ?
      <ResourceIcon
        mimeType={props.data.meta.mimeType}
        size={props.size}
      /> :
      null
    }
    title={props.data.name}
    /*subtitle={trans(props.data.meta.type, {}, 'resource')}*/
    meta={
      <>
        <span className="badge bg-secondary-subtle text-secondary-emphasis">{trans(props.data.meta.type, {}, 'resource')}</span>
        {get(props.data, 'meta.published') &&
          <span className="badge bg-secondary-subtle text-secondary-emphasis">{transChoice('display_views', get(props.data, 'meta.views') || 0, {count: get(props.data, 'meta.views') || 0})}</span>
        }
        {get(props.data, 'evaluation.estimatedDuration') &&
          <span className="badge bg-secondary-subtle text-secondary-emphasis">
            <span className="fa far fa-clock me-1" />
            {get(props.data, 'evaluation.estimatedDuration') + ' ' + trans('minutes_short')}
          </span>
        }

        {!get(props.data, 'meta.published') &&
          <span className="badge bg-secondary-subtle text-secondary-emphasis text-capitalize">{trans('not_published')}</span>
        }

        {get(props.data, 'restrictions.hidden', false) &&
          <span className="badge bg-secondary-subtle text-secondary-emphasis text-capitalize">{trans('hidden')}</span>
        }
      </>
    }
    /*flags={[
      !get(props.data, 'meta.published') && ['fa fa-fw fa-eye-slash', trans('resource_not_published', {}, 'resource')],
      get(props.data, 'meta.published') && [
        'fa fa-fw fa-eye',
        undefined !== get(props.data, 'meta.views') ?
          transChoice('resource_views', props.data.meta.views, {count: props.data.meta.views}, 'resource')
          :
          trans('resource_published', {}, 'resource')
        , get(props.data, 'meta.views')]
    ].filter(flag => !!flag)}*/
    contentText={get(props.data, 'meta.description')}
    /*footer={get(props.data, 'meta.creator') || get(props.data, 'meta.created') ?
      <span
        style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between'
        }}
      >
        <UserMicro {...get(props.data, 'meta.creator', {})} />

        {get(props.data, 'meta.created') && trans('published_at', {date: displayDate(props.data.meta.created, false, true)})}
      </span>
      :
      null
    }*/
  />

ResourceCard.propTypes = {
  className: T.string,
  size: T.string,
  data: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired
}

export {
  ResourceCard
}
