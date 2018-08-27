import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import isEqual from 'lodash/isEqual'
import uniq from 'lodash/uniq'

import {trans} from '#/main/core/translation'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

// todo : enhance implementation (make it more generic)
// todo : find better naming and location

const GroupTabs = props =>
  <ul className="nav nav-tabs">
    {props.tabs.map(tab =>
      <li key={tab} className={classes({active: tab === props.current})}>
        <a
          role="button"
          href=""
          onClick={(e) => {
            e.preventDefault()
            props.activate(tab)
          }}
        >
          {tab}
        </a>
      </li>
    )}
  </ul>

GroupTabs.propTypes = {
  current: T.string.isRequired,
  tabs: T.arrayOf(T.string).isRequired,
  activate: T.func.isRequired
}

class GridSelection extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentGroup: props.tag || trans('all'),
      currentType: props.items[0]
    }

    this.changeGroup = this.changeGroup.bind(this)
  }

  componentWillReceiveProps(nextProps) {
    if (0 === this.props.items.length && 0 < nextProps.items.length) {
      this.setState({
        currentType: nextProps.items[0]
      })
    }
  }

  handleItemMouseOver(type) {
    this.setState({
      currentType: type
    })
  }

  changeGroup(group) {
    const filteredItems = this.props.items
      .filter(item => trans('all') === group || (item.tags && -1 !== item.tags.indexOf(group)))

    this.setState({
      currentGroup: group,
      currentType: filteredItems[0]
    })
  }

  render() {
    const tags = uniq(this.props.items.reduce((accumulator, current) => accumulator.concat(current.tags || []), []))

    const filteredItems = this.props.items
      .filter(item => trans('all') === this.state.currentGroup || (item.tags && -1 !== item.tags.indexOf(this.state.currentGroup)))

    return (
      <div className="generic-type-picker">
        {0 !== tags.length &&
          <GroupTabs
            current={this.state.currentGroup}
            tabs={[trans('all')].concat(tags)}
            activate={this.changeGroup}
          />
        }

        <div className="modal-body">
          <div className="types-list" role="listbox">
            {filteredItems.map((type, index) =>
              <CallbackButton
                key={`type-${index}`}
                className={classes('type-entry', {
                  selected: isEqual(this.state.currentType, type)
                })}
                role="option"
                onMouseOver={() => this.handleItemMouseOver(type)}
                callback={() => {
                  this.props.handleSelect(type)
                }}
              >
                {typeof type.icon === 'string' ?
                  <span className={classes('type-icon', type.icon)} /> :
                  React.cloneElement(type.icon, {
                    className: 'type-icon'
                  })
                }
              </CallbackButton>
            )}
          </div>

          {this.state.currentType &&
            <div className="type-desc">
              <span className="type-name">{this.state.currentType.label}</span>

              {this.state.currentType.description &&
                <p>{this.state.currentType.description}</p>
              }
            </div>
          }
        </div>
      </div>
    )
  }
}

GridSelection.propTypes = {
  tag: T.string,
  items: T.arrayOf(T.shape({
    label: T.string.isRequired,
    icon: T.node.isRequired, // either a FontAwesome class string or a custom icon component
    description: T.string,
    tags: T.arrayOf(T.string)
  })).isRequired,
  handleSelect: T.func.isRequired
}

export {
  GridSelection
}
