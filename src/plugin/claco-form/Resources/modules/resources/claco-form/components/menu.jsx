import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ResourceMenu} from '#/main/core/resource/containers/menu'
import {LINK_BUTTON} from '#/main/app/buttons'

const ClacoFormMenu = props =>
  <ResourceMenu
    overview={true}
    actions={[
      {
        type: LINK_BUTTON,
        /*icon: 'fa fa-fw fa-search',*/
        label: trans('entries_list', {}, 'clacoform'),
        displayed: props.canSearchEntry,
        target: `${props.path}/entries`,
        exact: true
      }, {
        type: LINK_BUTTON,
        label: trans('random_entry', {}, 'clacoform'),
        target: `${props.path}/random`,
        displayed: props.randomEnabled
      }
    ]}
  />

ClacoFormMenu.propTypes = {
  path: T.string.isRequired,
  canSearchEntry: T.bool.isRequired,
  randomEnabled: T.bool.isRequired
}

export {
  ClacoFormMenu
}
