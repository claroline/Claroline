import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import uniq from 'lodash/uniq'

import {trans} from '#/main/app/intl/translation'
import {Select} from '#/main/app/input/components/select'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

class IconCollection extends Component {
  constructor(props) {
    super(props)

    this.state = {
      loaded: false,
      category: 'all'
    }

    this.changeCategory = this.changeCategory.bind(this)
    this.getIcons = this.getIcons.bind(this)
  }

  componentDidMount() {
    this.props.load().then(() => this.setState({loaded: true}))
  }

  changeCategory(newCategory) {
    this.setState({category: newCategory})
  }

  getIcons() {
    if ('all' === this.state.category) {
      return uniq(Object.keys(this.props.icons).reduce((choices, current) => choices.concat(this.props.icons[current].icons), []))
    }

    return this.props.icons[this.state.category].icons
  }

  render() {
    const icons = this.getIcons()

    return (
      <div className="icon-collection-container">
        {this.props.showCurrent &&
          <div className="current-container">
            {this.props.selected &&
              <span className={classes('current-icon', `fa fa-fw fa-${this.props.selected}`)}/>
            }

            {!this.props.selected &&
              <span className="current-icon">N/A</span>
            }
          </div>
        }

        <div className="icons-container">
          <Select
            id={this.props.id+'-select'}
            size="sm"
            value={this.state.category}
            onChange={this.changeCategory}
            choices={Object.keys(this.props.icons).reduce((choices, current) => Object.assign(choices, {
              [current]: this.props.icons[current].label
            }), {
              'all': trans('all')
            })}
          />

          <div className="icons-library">
            {icons.map(icon => (
              <Button
                key={icon}
                className={classes('icon btn-link', {
                  selected : this.props.selected === icon
                })}
                type={CALLBACK_BUTTON}
                icon={`fa fa-fw fa-${icon}`}
                label={icon}
                callback={() => this.props.onChange(icon)}
                tooltip="bottom"
              />
            ))}
          </div>
        </div>
      </div>
    )
  }
}


IconCollection.propTypes = {
  id: T.string.isRequired,
  selected: T.string,
  showCurrent: T.bool,
  onChange: T.func,

  // from store
  icons: T.object,
  load: T.func.isRequired
}

IconCollection.defaultProps = {
  selected: '',
  showCurrent: true,
  icons: {}
}

export {
  IconCollection
}
