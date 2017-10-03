import React, {Component} from 'react'
import {connect} from 'react-redux'
import {withRouter} from 'react-router-dom'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/core/translation'
import {selectors} from '../../selectors'
import {generateUrl} from '#/main/core/fos-js-router'

class ClacoFormMainMenu extends Component {
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
      <div className="claco-form-main-menu">
        {this.props.canAddEntry &&
          <a
            className="btn btn-default claco-form-menu-btn"
            href="#/entry/create"
          >
            <span className="fa fa-w fa-pencil-square-o fa-5x"></span>
            <h4>{trans('add_entry', {}, 'clacoform')}</h4>
          </a>
        }
        {this.props.canSearchEntry &&
          <a
            className="btn btn-default claco-form-menu-btn"
            href="#/entries"
          >
            <span className="fa fa-w fa-search fa-5x"></span>
            <h4>{trans('find_entry', {}, 'clacoform')}</h4>
          </a>
        }
        {this.props.randomEnabled &&
          <button
            className="btn btn-default claco-form-menu-btn"
            onClick={() => this.goToRandomEntry()}
          >
            <span className="fa fa-w fa-random fa-5x"></span>
            <h4>{trans('random_entry', {}, 'clacoform')}</h4>
          </button>
        }
      </div>
    )
  }
}

ClacoFormMainMenu.propTypes = {
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

const ConnectedClacoFormMainMenu = withRouter(connect(mapStateToProps, mapDispatchToProps)(ClacoFormMainMenu))

export {ConnectedClacoFormMainMenu as ClacoFormMainMenu}