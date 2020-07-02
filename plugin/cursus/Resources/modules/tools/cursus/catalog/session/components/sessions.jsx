import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as cursusSelectors} from '#/plugin/cursus/tools/cursus/store/selectors'
import {SessionList} from '#/plugin/cursus/administration/cursus/session/components/session-list'

const Sessions = (props) =>
  <ListData
    name={cursusSelectors.STORE_NAME + '.catalog.sessions'}
    fetch={{
      url: ['apiv2_cursus_session_public_list'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/catalog/sessions/${row.id}`,
      label: trans('open', {}, 'actions')
    })}
    definition={SessionList.definition}
    card={SessionList.card}
  />

Sessions.propTypes = {
  path: T.string.isRequired
}

export {
  Sessions
}
