import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {withRouter} from '#/main/app/router'
import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store'

const EntryMenuComponent = props =>
  <nav className="entry-menu">
    {props.canSearchEntry &&
      <Button
        className="btn-link"
        type={LINK_BUTTON}
        icon="fa fa-fw fa-list"
        label={trans('entries_list', {}, 'clacoform')}
        target={`${props.path}/entries`}
        exact={true}
        primary={true}
      />
    }

    {props.randomEnabled &&
      <Button
        className="btn-link"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-random"
        label={trans('random_entry', {}, 'clacoform')}
        callback={() => fetch(url(['claro_claco_form_entry_random', {clacoForm: props.clacoFormId}]), {
          method: 'GET' ,
          credentials: 'include'
        })
          .then(response => response.json())
          .then(entryId => {
            if (entryId) {
              props.history.push(`${props.path}/entries/${entryId}`)
            }
          })
        }
        primary={true}
      />
    }
  </nav>

EntryMenuComponent.propTypes = {
  path: T.string.isRequired,
  clacoFormId: T.string.isRequired,
  canSearchEntry: T.bool.isRequired,
  randomEnabled: T.bool.isRequired,
  history: T.object.isRequired
}

const EntryMenu = withRouter(connect(
  (state) => ({
    path: resourceSelectors.path(state),
    clacoFormId: selectors.clacoForm(state).id,
    canSearchEntry: selectors.canSearchEntry(state),
    randomEnabled: selectors.clacoForm(state).random.enabled
  })
)(EntryMenuComponent))

export {
  EntryMenu
}