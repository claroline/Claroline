import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'
import uniq from 'lodash/uniq'

import {trans} from '#/main/core/translation'
import {CallbackButton} from '#/main/app/button/components/callback'
import {Modal} from '#/main/app/overlay/modal/components/modal'

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

class SelectionModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentGroup: trans('all'),
      currentType: props.items[0]
    }

    this.changeGroup = this.changeGroup.bind(this)
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
      <Modal
        {...omit(this.props, 'items', 'handleSelect')}
        className="generic-type-picker"
      >
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
                  selected: this.state.currentType === type
                })}
                role="option"
                onMouseOver={() => this.handleItemMouseOver(type)}
                callback={() => {
                  this.props.fadeModal()
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
      </Modal>
    )
  }
}

SelectionModal.propTypes = {
  items: T.arrayOf(T.shape({
    label: T.string.isRequired,
    icon: T.node.isRequired, // either a FontAwesome class string or a custom icon component
    description: T.string,
    tags: T.arrayOf(T.string)
  })).isRequired,
  fadeModal: T.func.isRequired,
  handleSelect: T.func.isRequired
}

export {
  SelectionModal
}
