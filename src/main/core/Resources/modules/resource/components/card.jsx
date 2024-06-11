import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceIcon} from '#/main/core/resource/components/icon'

const ResourceCard = props =>
  <DataCard
    {...props}
    className={classes(props.className, {
      'data-card-muted': !get(props.data, 'meta.published', false) || get(props.data, 'restrictions.hidden', false)
    })}
    id={props.data.id}
    poster={props.data.thumbnail}
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
    contentText={get(props.data, 'meta.description')}
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
