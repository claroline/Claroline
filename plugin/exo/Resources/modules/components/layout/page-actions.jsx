import React, { Component } from 'react'
import { DropdownButton, MenuItem } from 'react-bootstrap'

const T = React.PropTypes

const Divider = () =>
  <div className="divider" role="separator"></div>

const PagePrimaryAction = props => {
  if (typeof props.handleAction === 'function') {
    return (
      <button
        type="button"
        className="btn btn-link"
        disabled={props.disabled}
        onClick={() => !props.disabled && props.handleAction()}
      >
        {props.icon && <span className={props.icon}></span>}
        &nbsp;{props.label}
        &nbsp;{props.badge}
      </button>
    )
  } else {
    return (
      <a
        className="btn btn-link"
        disabled={props.disabled}
        href={!props.disabled ? props.handleAction : ''}
      >
        {props.icon && <span className={props.icon}></span>}
        &nbsp;{props.label}
        &nbsp;{props.badge}
      </a>
    )
  }
}

PagePrimaryAction.propTypes = {
  icon: T.string,
  badge: T.node,
  label: T.string.isRequired,
  disabled: T.bool,
  handleAction: T.oneOfType([T.func, T.string]).isRequired
}

const MoreActionsDropdown = props =>
  <DropdownButton
    id="dropdown-other-actions"
    title={<span className="fa fa-fw fa-ellipsis-v"></span>}
    bsStyle="link"
    noCaret={true}
    pullRight={true}
  >
    <MenuItem header>More actions</MenuItem>
    {props.actions.map((action, index) => {
      if (typeof action.handleAction === 'function') {
        return (
          <MenuItem
            key={index}
            eventKey={index}
            disabled={action.disabled}
            onClick={() => !action.disabled && action.handleAction()}
          >
            {action.icon && <span className={action.icon}></span>}
            &nbsp;{action.label}
          </MenuItem>
        )
      } else {
        return (
          <MenuItem
            key={index}
            eventKey={index}
            disabled={action.disabled}
            href={!action.disabled ? action.handleAction : ''}
          >
            {action.icon && <span className={action.icon}></span>}
            &nbsp;{action.label}
          </MenuItem>
        )
      }
    })}
  </DropdownButton>

MoreActionsDropdown.propTypes = {
  actions: T.arrayOf(
    T.shape({
      icon: T.string,
      badge: T.node,
      label: T.string.isRequired,
      primary: T.bool,
      disabled: T.bool,
      handleAction: T.oneOfType([T.func, T.string]).isRequired
    })
  ).isRequired
}

export default class PageActions extends Component {
  render() {
    const primaryActions = this.props.actions.filter(action => action.primary)
    const secondaryActions = this.props.actions.filter(action => !action.primary)

    return (
      <nav className="page-actions">
        {primaryActions.map((primaryAction, index) => primaryAction.divider ?
          (<Divider key={index} />) :
          (<PagePrimaryAction key={index} icon={primaryAction.icon} badge={primaryAction.badge} label={primaryAction.label} handleAction={primaryAction.handleAction} />)
        )}

        {0 !== secondaryActions.length && <MoreActionsDropdown actions={secondaryActions} />}
      </nav>
    )
  }
}

PageActions.propTypes = {
  actions: T.arrayOf(
    T.shape({
      icon: T.string,
      badge: T.node,
      label: T.string,
      primary: T.bool,
      divider: T.bool,
      disabled: T.bool,
      handleAction: T.oneOfType([T.func, T.string])
    })
  ).isRequired
}
