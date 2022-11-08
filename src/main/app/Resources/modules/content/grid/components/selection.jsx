import React, {Component, cloneElement, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import uniq from 'lodash/uniq'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

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
      currentGroup: props.tag || trans('all')
    }

    this.changeGroup = this.changeGroup.bind(this)
  }

  changeGroup(group) {
    this.setState({
      currentGroup: group
    })
  }

  render() {
    const tags = uniq(this.props.items.reduce((accumulator, current) => accumulator.concat(current.tags || []), []))

    const filteredItems = this.props.items
      .filter(item => trans('all') === this.state.currentGroup || (item.tags && -1 !== item.tags.indexOf(this.state.currentGroup)))
      .sort((a, b) => a.label < b.label ? -1 : 1)

    return (
      <Fragment>
        {0 !== tags.length &&
          <GroupTabs
            current={this.state.currentGroup}
            tabs={[trans('all')].concat(tags)}
            activate={this.changeGroup}
          />
        }

        <ul className="list-group" role="listbox">
          {filteredItems.map((type) => {
            let selectAction
            if (this.props.selectAction) {
              selectAction = this.props.selectAction(type)
            } else {
              selectAction = {
                type: CALLBACK_BUTTON,
                callback: () => this.props.handleSelect(type)
              }
            }

            return (
              <Button
                id={type.id || type.name}
                key={type.id || type.name}
                className="list-group-item type-control lg"
                role="option"
                icon={typeof type.icon === 'string' ?
                  <span className={classes('type-icon', type.icon)} /> :
                  cloneElement(type.icon, {
                    className: 'type-icon'
                  })
                }
                label={
                  <div>
                    <h1>{type.label}</h1>

                    <p>{type.description}</p>
                  </div>
                }
                {...selectAction}
              />
            )
          })}
        </ul>
      </Fragment>
    )
  }
}

GridSelection.propTypes = {
  tag: T.string,
  items: T.arrayOf(T.shape({
    id: T.string, // to merge with name
    name: T.string,
    label: T.string.isRequired,
    icon: T.node.isRequired, // either a FontAwesome class string or a custom icon component
    description: T.string,
    tags: T.arrayOf(T.string)
  })).isRequired,
  selectAction: T.func,
  /**
   * @deprecated
   */
  handleSelect: T.func // for retro-compatibility only. Use selectAction
}

export {
  GridSelection
}
