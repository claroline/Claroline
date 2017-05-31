import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {generateUrl} from '#/main/core/fos-js-router'

const UsersList = props =>
  <ul className="dropdown-menu">
    {props.users.map((user) =>
      <li key={user.id}>
        <a
          role="button"
          href=""
          onClick={(e) => {
            props.select(user)
            e.preventDefault()
          }}
        >
          {user.name}
        </a>
      </li>
    )}
  </ul>

UsersList.propTypes = {
  users: T.arrayOf(T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired
  })).isRequired,
  select: T.func.isRequired
}

export class UserTypeahead extends Component {
  constructor(props) {
    super(props)

    this.state = {
      isFetching: false,
      searchString: '',
      results: []
    }
  }

  search(search) {
    this.setState({searchString: search})

    if (search && search.length > 2) {
      this.setState({isFetching: true})

      fetch(generateUrl('questions_share_users', {search: search}), {
        method: 'GET' ,
        credentials: 'include'
      })
      .then(response => response.json())
      .then(users => this.setState({results: users, isFetching: false}))
    }
  }

  selectUser(user) {
    this.setState({
      searchString: '',
      results: []
    })

    this.props.handleSelect(user)
  }

  render() {
    return (
      <div className={classes('dropdown', 0 < this.state.results.length ? 'open' : null)}>
        <div className="input-group">
        <span className="input-group-addon">
          <span
            className={classes(
              'fa fa-fw',
              this.state.isFetching ? 'fa-circle-o-notch fa-spin' : 'fa-user'
            )}
          ></span>
        </span>

          <input
            id="search-users"
            type="text"
            className="form-control"
            value={this.state.searchString}
            onChange={e => this.search(e.target.value)}
          />
        </div>

        <UsersList
          users={this.state.results}
          select={this.selectUser.bind(this)}
        />
      </div>
    )
  }
}

UserTypeahead.propTypes = {
  handleSelect: T.func.isRequired
}
