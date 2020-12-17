import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

class SearchMinimal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentSearch: ''
    }
  }

  render() {
    return (
      <form
        className={classes('input-group', this.props.className, {
          [`input-group-${this.props.size}`]: !!this.props.size
        })}
      >
        <input
          type="search"
          className="form-control" placeholder={this.props.placeholder}
          value={this.state.currentSearch}
          onChange={(e) => this.setState({currentSearch: e.target.value || ''})}
        />

        <span className="input-group-btn">
          <Button
            className="btn"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-search"
            label={trans('search', {}, 'actions')}
            tooltip="left"
            htmlType="submit"
            callback={() => {
              this.props.search(this.state.currentSearch)
              this.setState({currentSearch: ''})
            }}
            disabled={!this.state.currentSearch}
          />
        </span>
      </form>
    )
  }
}

SearchMinimal.propTypes = {
  className: T.string,
  size: T.string,
  placeholder: T.string,
  search: T.func.isRequired
}

SearchMinimal.defaultProps = {
  placeholder: trans('search', {}, 'actions')
}

export {
  SearchMinimal
}
