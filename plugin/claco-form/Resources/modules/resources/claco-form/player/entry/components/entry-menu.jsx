import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {withRouter} from '#/main/app/router'
import {url} from '#/main/app/api'

import {trans} from '#/main/core/translation'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store'

class EntryMenuComponent extends Component {
  goToRandomEntry() {
    fetch(url(['claro_claco_form_entry_random', {clacoForm: this.props.clacoFormId}]), {
      method: 'GET' ,
      credentials: 'include'
    })
      .then(response => response.json())
      .then(entryId => {
        if (entryId) {
          this.props.history.push(`/entries/${entryId}`)
        }
      })
  }

  render() {
    return (
      <div className="entry-menu">
        {this.props.canAddEntry &&
          <TooltipButton
            id="tooltip-button-add"
            className="btn btn-primary entry-menu-button"
            title={trans('add_entry', {}, 'clacoform')}
            onClick={() => this.props.history.push('/entry/form')}
          >
            <span className="fa fa-fw fa-plus" />
          </TooltipButton>
        }

        {this.props.randomEnabled &&
          <TooltipButton
            id="tooltip-button-random"
            className="btn btn-default entry-menu-button"
            title={trans('random_entry', {}, 'clacoform')}
            onClick={() => this.goToRandomEntry()}
          >
            <span className="fa fa-fw fa-random" />
          </TooltipButton>
        }

        {this.props.canSearchEntry &&
          <TooltipButton
            id="tooltip-button-list"
            className="btn btn-default entry-menu-button"
            title={trans('entries_list', {}, 'clacoform')}
            onClick={() => this.props.history.push('/entries')}
          >
            <span className="fa fa-fw fa-list" />
          </TooltipButton>
        }
      </div>
    )
  }
}

EntryMenuComponent.propTypes = {
  clacoFormId: T.string.isRequired,
  canSearchEntry: T.bool.isRequired,
  canAddEntry: T.bool.isRequired,
  randomEnabled: T.bool.isRequired,
  history: T.object.isRequired
}

const EntryMenu = withRouter(connect(
  (state) => ({
    clacoFormId: selectors.clacoForm(state).id,
    canSearchEntry: selectors.canSearchEntry(state),
    randomEnabled: selectors.params(state).random_enabled,
    canAddEntry: selectors.canAddEntry(state)
  })
)(EntryMenuComponent))

export {
  EntryMenu
}