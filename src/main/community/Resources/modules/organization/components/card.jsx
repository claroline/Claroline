import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'
import {Badge} from '#/main/app/components/badge'
import {DataCard} from '#/main/app/data/components/card'

import {Organization as OrganizationTypes} from '#/main/community/organization/prop-types'

const OrganizationCard = (props) =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.thumbnail}
    icon="fa fa-building"
    name={props.data.name}
    title={
      <div className={classes('d-flex flex-row gap-2 align-items-baseline', {'justify-content-center': 'row' !== props.orientation})} role="presentation">
        {get(props.data, 'meta.public') &&
          <TooltipOverlay
            position="top"
            tip={trans('public')}
          >
            <span className="fa fa-fw fa-globe" aria-hidden={true} />
          </TooltipOverlay>
        }

        {props.data.name}

        {'row' === props.orientation && get(props.data, 'meta.default') &&
          <Badge variant="primary">{trans('default')}</Badge>
        }
      </div>
    }
    contentText={get(props.data, 'meta.description') || <em className="text-body-tertiary">{trans('no_description')}</em>}
    asIcon={true}
    meta={get(props.data, 'meta.default') &&
      <Badge variant="primary">{trans('default')}</Badge>
    }
  />

OrganizationCard.propTypes = {
  orientation: T.string,
  data: T.shape(
    OrganizationTypes.propTypes
  ).isRequired
}

export {
  OrganizationCard
}
