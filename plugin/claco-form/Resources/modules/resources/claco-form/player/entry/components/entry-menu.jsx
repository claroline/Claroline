import React, {Component} from 'react'
import {connect} from 'react-redux'
import {withRouter} from 'react-router-dom'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/core/translation'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {selectors} from '../../../selectors'
import {generateUrl} from '#/main/core/fos-js-router'

class EntryMenu extends Component {
  goToRandomEntry() {
    fetch(generateUrl('claro_claco_form_entry_random', {clacoForm: this.props.resourceId}), {
      method: 'GET' ,
      credentials: 'include'
    })
    .then(response => response.json())
    .then(entryId => {
      if (entryId > 0) {
        this.props.history.push(`/entry/${entryId}/view`)
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
            onClick={() => this.props.history.push('/entry/create')}
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

EntryMenu.propTypes = {
  resourceId: T.number.isRequired,
  canSearchEntry: T.bool.isRequired,
  canAddEntry: T.bool.isRequired,
  randomEnabled: T.bool.isRequired,
  history: T.object.isRequired
}

function mapStateToProps(state) {
  return {
    resourceId: selectors.resource(state).id,
    canSearchEntry: selectors.canSearchEntry(state),
    randomEnabled: selectors.getParam(state, 'random_enabled'),
    canAddEntry: selectors.canAddEntry(state)
  }
}

function mapDispatchToProps() {
  return {}
}

const ConnectedEntryMenu = withRouter(connect(mapStateToProps, mapDispatchToProps)(EntryMenu))

export {ConnectedEntryMenu as EntryMenu}