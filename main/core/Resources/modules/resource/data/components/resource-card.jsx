import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {asset} from '#/main/core/scaffolding/asset'
import {displayDate} from '#/main/core/scaffolding/date'

import {DataCard} from '#/main/core/data/components/data-card'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

const ResourceCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail) : null}
    icon={<img className="icon" src={asset(props.data.meta.icon)} />}
    title={props.data.name}
    subtitle={trans(props.data.meta.type, {}, 'resource')}
    flags={[
      ['fa fa-fw fa-eye', trans('resource_views', {}, 'resource'), props.data.meta.views],
      props.data.social && ['fa fa-fw fa-thumbs-up', trans('resource_likes', {}, 'resource'), props.data.social.likes]
    ]}
    contentText={props.data.meta.description}
    footer={trans('published_at', {date: displayDate(props.data.meta.created, false, true)})}
  />

ResourceCard.propTypes = {
  data: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired
}

// used in widgets
// todo : remove when card will become configurable in widget UI
const ResourceCardMinimal = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail) : null}
    title={props.data.name}
    contentText={props.data.meta.description}
  />

ResourceCardMinimal.propTypes = {
  data: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired
}

export {
  ResourceCard,
  ResourceCardMinimal
}
