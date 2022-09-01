import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {ResourceEmbedded} from '#/main/core/resource/containers/embedded'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

const ShortcutPlayer = (props) =>
  <Fragment>
    {!props.resource &&
      <ContentPlaceholder
        size="lg"
        title={trans('no_resource', {}, 'resource')}
      />
    }

    {props.resource &&
      <div className="row">
        <ResourceEmbedded
          resourceNode={props.resource}
          showHeader={false}
        />
      </div>
    }
  </Fragment>

ShortcutPlayer.propTypes = {
  resource: T.object.isRequired
}

export {
  ShortcutPlayer
}