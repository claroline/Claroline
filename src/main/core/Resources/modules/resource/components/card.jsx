import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceIcon} from '#/main/core/resource/components/icon'
import {Badge} from '#/main/app/components/badge'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'

const ResourceCard = props =>
  <DataCard
    {...props}
    poster={props.data.poster}
    icon={
      <ResourceIcon
        mimeType={get(props.data, 'meta.mimeType')}
        size={props.size}
      />
    }
    name={props.data.name}
    title={
      <>
        {get(props.data, 'meta.public') &&
          <TooltipOverlay
            id={'ws-type'+props.data.id}
            position="top"
            tip={trans('public')}
          >
            <span className="fa fa-fw fa-globe me-2" aria-hidden={true} />
          </TooltipOverlay>
        }

        {props.data.name}
      </>
    }
    status={false === get(props.data, 'meta.published') ? {
      variant: 'secondary',
      text: trans('not_published')
    } : undefined}
    meta={
      <>
        <Badge variant="secondary" subtle={true}>{trans(get(props.data, 'meta.type'), {}, 'resource')}</Badge>
        <Badge variant="secondary" subtle={true}>{transChoice('display_views', get(props.data, 'meta.views') || 0, {count: get(props.data, 'meta.views') || 0})}</Badge>
        {get(props.data, 'estimatedDuration') &&
          <Badge variant="secondary" subtle={true}>
            <span className="fa far fa-clock me-1" aria-hidden={true} />
            {get(props.data, 'estimatedDuration') + ' ' + trans('minutes_short')}
          </Badge>
        }

        {get(props.data, 'restrictions.hidden', false) &&
          <Badge variant="secondary" subtle={true}>{trans('hidden')}</Badge>
        }
      </>
    }
    contentText={get(props.data, 'meta.description') || <em className="text-body-tertiary">{trans('no_description')}</em>}
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
