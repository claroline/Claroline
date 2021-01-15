import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ResourcePage} from '#/main/core/resource/containers/page'
import {ResourceEmbedded} from '#/main/core/resource/containers/embedded'

const ShortcutResource = (props) =>
  <ResourcePage>
    <div className="row">
      <ResourceEmbedded
        resourceNode={props.resource}
        showHeader={false}
      />
    </div>
  </ResourcePage>

ShortcutResource.propTypes = {
  resource: T.object.isRequired
}

export {
  ShortcutResource
}